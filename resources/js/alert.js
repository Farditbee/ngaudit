import Swal from 'sweetalert2';

const isDark = () => document.documentElement.classList.contains('dark');

const baseClasses = {
    popup: 'rounded-xl border border-neutral-200 bg-white text-neutral-900 shadow-xl dark:border-neutral-800 dark:bg-neutral-900 dark:text-neutral-100',
    title: 'text-base font-semibold',
    actions: '!mt-6 !gap-3',
    htmlContainer: 'text-sm text-neutral-600 dark:text-neutral-300',
    confirmButton:
        'inline-flex h-9 items-center justify-center rounded-md bg-neutral-900 px-5 text-xs font-medium text-white hover:bg-neutral-800 dark:bg-white dark:text-neutral-900 dark:hover:bg-neutral-200',
    cancelButton:
        'inline-flex h-9 items-center justify-center rounded-md border border-neutral-200 bg-white px-5 text-xs font-medium text-neutral-700 hover:bg-neutral-100 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-200 dark:hover:bg-neutral-800',
    input: 'h-9 rounded-md border border-neutral-200 bg-white px-3 text-xs text-neutral-900 dark:border-neutral-700 dark:bg-neutral-950 dark:text-neutral-100',
};

const dangerConfirmClass =
    'inline-flex h-9 items-center justify-center rounded-md bg-red-600 px-5 text-xs font-medium text-white hover:bg-red-700';

const Modal = Swal.mixin({
    buttonsStyling: false,
    reverseButtons: true,
    focusCancel: false,
    customClass: baseClasses,
    didOpen: (popup) => {
        popup.parentElement?.classList.toggle('swal2-dark', isDark());
    },
});

const Toast = Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 3500,
    timerProgressBar: true,
    customClass: {
        popup: baseClasses.popup + ' !p-3',
        title: 'text-xs font-medium',
    },
    didOpen: (toast) => {
        toast.addEventListener('mouseenter', Swal.stopTimer);
        toast.addEventListener('mouseleave', Swal.resumeTimer);
    },
});

export const notify = {
    /** Toast ringan di pojok kanan atas: success | error | warning | info */
    toast(icon, message) {
        return Toast.fire({ icon, title: message });
    },

    /** Popup tengah layar dengan tombol OK, otomatis tertutup setelah 3 detik. */
    success(message, title = 'Berhasil') {
        return Modal.fire({
            icon: 'success',
            title,
            text: message,
            confirmButtonText: 'OK',
            timer: 3000,
            timerProgressBar: true,
        });
    },

    error(message, title = 'Gagal') {
        return Modal.fire({ icon: 'error', title, text: message, confirmButtonText: 'Tutup' });
    },

    warning(message, title = 'Perhatian') {
        return Modal.fire({ icon: 'warning', title, text: message, confirmButtonText: 'Mengerti' });
    },

    info(message, title = 'Informasi') {
        return Modal.fire({
            icon: 'info',
            title,
            text: message,
            confirmButtonText: 'OK',
            timer: 3000,
            timerProgressBar: true,
        });
    },

    /** Popup tengah sesuai tipe: success/info otomatis tertutup 3 detik, error/warning menunggu tombol. */
    show(type, message) {
        if (type === 'error') return this.error(message);
        if (type === 'warning') return this.warning(message);
        if (type === 'info') return this.info(message);
        return this.success(message);
    },

    /** Dialog konfirmasi umum. Mengembalikan boolean. */
    async confirm({
        title = 'Apakah Anda yakin?',
        text = '',
        confirmText = 'Ya, lanjutkan',
        cancelText = 'Batal',
        icon = 'question',
        danger = false,
    } = {}) {
        const result = await Modal.fire({
            icon,
            title,
            text,
            showCancelButton: true,
            confirmButtonText: confirmText,
            cancelButtonText: cancelText,
            customClass: {
                ...baseClasses,
                confirmButton: danger ? dangerConfirmClass : baseClasses.confirmButton,
            },
        });
        return result.isConfirmed;
    },

    /** Konfirmasi hapus data. */
    confirmDelete({ label = '', text = '' } = {}) {
        return this.confirm({
            title: 'Hapus data ini?',
            text: text || (label ? `"${label}" akan dihapus dan tidak dapat dikembalikan.` : 'Data yang dihapus tidak dapat dikembalikan.'),
            confirmText: 'Ya, hapus',
            icon: 'warning',
            danger: true,
        });
    },

    /** Dialog input teks. Mengembalikan string atau null jika dibatalkan. */
    async prompt({ title = '', text = '', inputLabel = '', inputValue = '', confirmText = 'Simpan', required = true } = {}) {
        const result = await Modal.fire({
            title,
            text,
            input: 'text',
            inputLabel,
            inputValue,
            showCancelButton: true,
            confirmButtonText: confirmText,
            cancelButtonText: 'Batal',
            inputValidator: (value) => (required && !String(value || '').trim() ? 'Isian wajib diisi.' : undefined),
        });
        return result.isConfirmed ? String(result.value).trim() : null;
    },

    /** Label ringkas sebuah baris datatable untuk teks konfirmasi. */
    rowLabel(row) {
        if (!row) return '';
        return (
            row.nomor_pka ||
            row.no_sprin ||
            row.nama_template ||
            row.nama_tim ||
            row.name ||
            row.plot ||
            row.village_name ||
            row.district_name ||
            row.city_name ||
            row.province_name ||
            ''
        );
    },
};

export function registerNotify() {
    window.Swal = Swal;
    window.notify = notify;
}
