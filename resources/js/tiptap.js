import { Editor } from '@tiptap/core';
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
            },
        },
        content: content || '',
        onUpdate: ({ editor }) => {
            if (typeof onUpdate === 'function') {
                onUpdate(editor.getHTML());
            }
        },
    });
}

export function registerTipTap(Alpine) {
    // Registered for global access if needed
    window.createTipTapEditor = createTipTapEditor;
}
