import { Editor, Extension, Mark } from '@tiptap/core';
import StarterKit from '@tiptap/starter-kit';
import Subscript from '@tiptap/extension-subscript';
import Superscript from '@tiptap/extension-superscript';
import TextAlign from '@tiptap/extension-text-align';
import { Table } from '@tiptap/extension-table';
import { TableRow } from '@tiptap/extension-table-row';
import { TableHeader } from '@tiptap/extension-table-header';
import { TableCell } from '@tiptap/extension-table-cell';
import Image from '@tiptap/extension-image';
import Underline from '@tiptap/extension-underline';

/**
 * Mark kustom untuk mendukung Jenis Font (font-family) dan Ukuran Font (font-size).
 */
const FontStyle = Mark.create({
    name: 'fontStyle',

    addOptions() {
        return {
            HTMLAttributes: {},
        };
    },

    addAttributes() {
        return {
            fontFamily: {
                default: null,
                parseHTML: (element) => {
                    const raw = element.style.fontFamily;
                    if (!raw) return null;
                    return raw.replace(/^['"]+|['"]+$/g, '').trim() || null;
                },
            },
            fontSize: {
                default: null,
                parseHTML: (element) => element.style.fontSize || null,
            },
        };
    },

    parseHTML() {
        return [
            {
                tag: 'span[style]',
                getAttrs: (element) => {
                    if (typeof element === 'string') return false;
                    const hasFontFamily = Boolean(element.style.fontFamily);
                    const hasFontSize = Boolean(element.style.fontSize);
                    return hasFontFamily || hasFontSize ? {} : false;
                },
            },
        ];
    },

    renderHTML({ HTMLAttributes }) {
        const styles = [];
        if (HTMLAttributes.fontFamily) {
            styles.push(`font-family: ${HTMLAttributes.fontFamily}`);
        }
        if (HTMLAttributes.fontSize) {
            styles.push(`font-size: ${HTMLAttributes.fontSize}`);
        }

        const attrs = {};
        if (styles.length > 0) {
            attrs.style = styles.join('; ');
        }

        return ['span', attrs, 0];
    },

    addCommands() {
        return {
            setFontFamily:
                (fontFamily) =>
                ({ chain }) => {
                    if (!fontFamily) {
                        return chain().setMark(this.name, { fontFamily: null }).run();
                    }
                    return chain().setMark(this.name, { fontFamily }).run();
                },
            unsetFontFamily:
                () =>
                ({ chain }) => {
                    return chain().setMark(this.name, { fontFamily: null }).run();
                },
            setFontSize:
                (fontSize) =>
                ({ chain }) => {
                    if (!fontSize) {
                        return chain().setMark(this.name, { fontSize: null }).run();
                    }
                    return chain().setMark(this.name, { fontSize }).run();
                },
            unsetFontSize:
                () =>
                ({ chain }) => {
                    return chain().setMark(this.name, { fontSize: null }).run();
                },
        };
    },
});

/**
 * Extension kustom untuk Line Spacing (line-height) pada paragraph dan heading.
 */
const LineSpacing = Extension.create({
    name: 'lineSpacing',

    addGlobalAttributes() {
        return [
            {
                types: ['paragraph', 'heading'],
                attributes: {
                    lineHeight: {
                        default: null,
                        parseHTML: (element) => element.style.lineHeight || null,
                        renderHTML: (attributes) => {
                            if (!attributes.lineHeight) {
                                return {};
                            }
                            return {
                                style: `line-height: ${attributes.lineHeight}`,
                            };
                        },
                    },
                },
            },
        ];
    },

    addCommands() {
        return {
            setLineHeight:
                (lineHeight) =>
                ({ commands }) => {
                    return ['paragraph', 'heading'].every((type) =>
                        commands.updateAttributes(type, { lineHeight })
                    );
                },
            unsetLineHeight:
                () =>
                ({ commands }) => {
                    return ['paragraph', 'heading'].every((type) =>
                        commands.resetAttributes(type, 'lineHeight')
                    );
                },
        };
    },
});

/**
 * Extension kustom untuk Opsi Multilevel List & Tipe Poin (angka, alfabet, romawi, simbol bullet).
 */
const ListStyle = Extension.create({
    name: 'listStyle',

    addGlobalAttributes() {
        return [
            {
                types: ['orderedList', 'bulletList'],
                attributes: {
                    listStyleType: {
                        default: null,
                        parseHTML: (element) =>
                            element.getAttribute('data-list-style') ||
                            element.style.listStyleType ||
                            null,
                        renderHTML: (attributes) => {
                            if (!attributes.listStyleType) {
                                return {};
                            }

                            const cssMap = {
                                decimal: 'decimal',
                                'lower-alpha': 'lower-alpha',
                                'upper-alpha': 'upper-alpha',
                                'lower-roman': 'lower-roman',
                                'upper-roman': 'upper-roman',
                                disc: 'disc',
                                circle: 'circle',
                                square: 'square',
                            };

                            const cssType = cssMap[attributes.listStyleType] || null;

                            return {
                                'data-list-style': attributes.listStyleType,
                                ...(cssType ? { style: `list-style-type: ${cssType}` } : {}),
                            };
                        },
                    },
                },
            },
        ];
    },

    addKeyboardShortcuts() {
        return {
            Tab: () => {
                if (this.editor.isActive('listItem')) {
                    return this.editor.commands.sinkListItem('listItem');
                }
                return false;
            },
            'Shift-Tab': () => {
                if (this.editor.isActive('listItem')) {
                    return this.editor.commands.liftListItem('listItem');
                }
                return false;
            },
        };
    },
});

let notifyTimeout = null;
function notifyToolbarUpdate() {
    if (notifyTimeout) return;
    notifyTimeout = window.requestAnimationFrame(() => {
        notifyTimeout = null;
        window.dispatchEvent(new CustomEvent('tiptap-update'));
    });
}

export function createTipTapEditor({ element, content = '', onUpdate = null }) {
    return new Editor({
        element,
        extensions: [
            StarterKit.configure({
                heading: {
                    levels: [1, 2, 3, 4],
                },
                link: {
                    openOnClick: false,
                    autolink: true,
                },
            }),
            Underline,
            Subscript,
            Superscript,
            FontStyle,
            LineSpacing,
            ListStyle,
            TextAlign.configure({
                types: ['heading', 'paragraph'],
            }),
            Table.configure({
                resizable: true,
            }),
            TableRow,
            TableHeader,
            TableCell,
            Image,
        ],
        editorProps: {
            attributes: {
                class: 'prose prose-sm dark:prose-invert max-w-none focus:outline-none min-h-[260px] p-4 cursor-text',
                style: 'font-size: 11pt; line-height: 1.5;',
            },
        },
        content: content || '',
        onUpdate: ({ editor }) => {
            if (typeof onUpdate === 'function') {
                onUpdate(editor.getHTML());
            }
            notifyToolbarUpdate();
        },
        onSelectionUpdate: () => {
            notifyToolbarUpdate();
        },
    });
}

export function registerTipTap(Alpine) {
    window.createTipTapEditor = createTipTapEditor;
}
