document.addEventListener('DOMContentLoaded', () => {
    const sidebar = document.getElementById('sidebar');

    document.querySelector('[data-menu-toggle]')?.addEventListener('click', () => {
        sidebar?.classList.toggle('open');
    });

    document.addEventListener('click', (event) => {
        if (
            window.innerWidth <= 760 &&
            sidebar?.classList.contains('open') &&
            !sidebar.contains(event.target) &&
            !event.target.closest('[data-menu-toggle]')
        ) {
            sidebar.classList.remove('open');
        }
    });

    document.querySelectorAll('.dismissible').forEach((node) => {
        window.setTimeout(() => node.remove(), 6500);
    });

    // Show / hide password fields.
    document.querySelectorAll('[data-password-toggle]').forEach((button) => {
        button.addEventListener('click', () => {
            const input = button.parentElement?.querySelector('input');
            if (!input) return;

            const shouldShow = input.type === 'password';
            input.type = shouldShow ? 'text' : 'password';
            button.textContent = shouldShow ? '◎' : '◉';
            button.setAttribute('aria-label', shouldShow ? 'Sembunyikan password' : 'Lihat password');
            button.setAttribute('aria-pressed', shouldShow ? 'true' : 'false');
        });
    });

    // Show the edit controls directly in the selected vehicle row.
    document.querySelectorAll('[data-edit-row]').forEach((button) => {
        button.addEventListener('click', () => {
            const row = button.closest('[data-vehicle-row]');
            if (!row) return;
            row.classList.add('is-editing');
        });
    });

    document.querySelectorAll('[data-cancel-row]').forEach((button) => {
        button.addEventListener('click', () => {
            const row = button.closest('[data-vehicle-row]');
            if (!row) return;
            const form = row.querySelector('form[id^="vehicle-form-"]');
            form?.reset();
            row.classList.remove('is-editing');
        });
    });

    const formatRupiah = (value) => {
        const number = Number(value || 0);
        return `Rp ${new Intl.NumberFormat('id-ID', { maximumFractionDigits: 0 }).format(number)}`;
    };

    document.querySelectorAll('[data-vehicle-row]').forEach((row) => {
        const gross = row.querySelector('[data-gross-input]');
        const allowance = row.querySelector('[data-allowance-input]');
        const preview = row.querySelector('[data-profit-preview]');
        const updatePreview = () => {
            if (!gross || !allowance || !preview) return;
            preview.textContent = formatRupiah(Number(gross.value || 0) - Number(allowance.value || 0));
        };
        gross?.addEventListener('input', updatePreview);
        allowance?.addEventListener('input', updatePreview);
    });

    const fileInput = document.querySelector('input[type="file"][name="file"]');
    fileInput?.addEventListener('change', () => {
        const label = document.querySelector('[data-file-label]');
        if (label) label.textContent = fileInput.files?.[0]?.name || 'Belum ada file dipilih';
    });

    document.querySelectorAll('[data-table-search]').forEach((input) => {
        input.addEventListener('input', () => {
            const table = document.getElementById(input.dataset.tableSearch);
            table?.querySelectorAll('tbody tr').forEach((row) => {
                row.hidden = !row.textContent.toLowerCase().includes(input.value.toLowerCase());
            });
        });
    });

    // Download CSV with a small delay before revoking the temporary URL.
    document.querySelectorAll('[data-export-table]').forEach((button) => {
        button.addEventListener('click', () => {
            const table = document.getElementById(button.dataset.exportTable);
            if (!table) return;

            const rows = [...table.querySelectorAll('tr')].map((row) => {
                const cells = [...row.querySelectorAll('th,td')];
                return cells.map((cell) => {
                    const visible = cell.querySelector('.view-value');
                    const value = visible ? visible.innerText : cell.innerText;
                    return `"${String(value).trim().replaceAll('"', '""')}"`;
                }).join(';');
            });

            const blob = new Blob(['\ufeff' + rows.join('\r\n')], { type: 'text/csv;charset=utf-8;' });
            const url = URL.createObjectURL(blob);
            const link = document.createElement('a');
            link.href = url;
            link.download = button.dataset.filename || 'laporan.csv';
            document.body.appendChild(link);
            link.click();
            link.remove();
            window.setTimeout(() => URL.revokeObjectURL(url), 1000);
        });
    });
});
