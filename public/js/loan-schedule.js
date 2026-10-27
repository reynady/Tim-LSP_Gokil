(() => {
    const form = document.getElementById('loanReviewForm');
    const start = document.getElementById('starts_at');
    const end = document.getElementById('ends_at');
    if (!form || !start || !end || start.disabled) return;

    const message = document.getElementById('availabilityMessage');
    const summary = document.getElementById('scheduleSummary');
    const formatter = new Intl.DateTimeFormat('id-ID', {
        timeZone: 'Asia/Jakarta', day: '2-digit', month: 'long', year: 'numeric',
        hour: '2-digit', minute: '2-digit', hourCycle: 'h23',
    });
    const serverNow = Number(form.dataset.serverNow);
    const maxDurationDays = Number(form.dataset.maxDurationDays || 5);
    const maxDurationMs = maxDurationDays * 86400000;
    const maxDurationHours = maxDurationDays * 24;
    const loadedAt = performance.now();
    const now = () => serverNow + performance.now() - loadedAt;
    const wibInput = (timestamp) => new Date(timestamp + 7 * 3600000).toISOString().slice(0, 16);
    const timestamp = (value) => /^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/.test(value)
        ? Date.parse(value + ':00+07:00') : NaN;
    let timer;
    let request;
    let available = null;
    let revision = 0;

    function validate() {
        const begins = timestamp(start.value);
        const finishes = timestamp(end.value);
        start.min = wibInput(Math.ceil(now() / 60000) * 60000);
        start.setCustomValidity(!start.value ? 'Tanggal dan jam mulai peminjaman wajib diisi.'
            : !Number.isFinite(begins) ? 'Tanggal dan jam mulai peminjaman tidak valid.'
            : begins < now() ? 'Waktu mulai peminjaman tidak boleh di masa lalu.' : '');
        end.setCustomValidity(!end.value ? 'Tanggal dan jam selesai peminjaman wajib diisi.'
            : !Number.isFinite(finishes) ? 'Tanggal dan jam selesai peminjaman tidak valid.'
            : finishes <= begins ? 'Waktu selesai harus lebih besar daripada waktu mulai peminjaman.'
            : finishes > begins + maxDurationMs ? `Durasi peminjaman maksimal ${maxDurationDays} hari (${maxDurationHours} jam).` : '');
        if (Number.isFinite(begins)) {
            end.min = wibInput(begins + 60000);
            end.max = wibInput(begins + maxDurationMs);
        } else {
            end.removeAttribute('min');
            end.removeAttribute('max');
        }
        const valid = start.validity.valid && end.validity.valid;
        summary.textContent = valid
            ? formatter.format(begins) + ' WIB sampai ' + formatter.format(finishes) + ' WIB'
            : 'Tanggal dan jam belum lengkap atau tidak valid.';
        return valid;
    }

    async function checkAvailability(currentRevision) {
        if (!validate()) return;
        message.textContent = 'Memeriksa ketersediaan jadwal...';
        request = new AbortController();
        const url = new URL(form.dataset.availabilityUrl, window.location.origin);
        url.search = new URLSearchParams({
            monster_id: form.elements.monster_id.value,
            starts_at: start.value, ends_at: end.value,
        }).toString();
        try {
            const response = await fetch(url, {
                headers: {Accept: 'application/json'}, signal: request.signal,
            });
            const data = await response.json();
            if (currentRevision !== revision) return;
            if (!response.ok) {
                message.textContent = Object.values(data.errors || {}).flat()[0]
                    || 'Jadwal tidak dapat diperiksa. Silakan coba lagi.';
                return;
            }
            available = data.available_quantity > 0;
            message.textContent = data.message;
        } catch (error) {
            if (error.name !== 'AbortError' && currentRevision === revision) {
                message.textContent = 'Ketersediaan belum dapat diperiksa. Sistem akan memeriksa ulang saat Anda mengonfirmasi peminjaman.';
            }
        }
    }

    function changed() {
        clearTimeout(timer);
        request?.abort();
        available = null;
        revision++;
        message.textContent = 'Pilih jadwal yang valid untuk memeriksa ketersediaan unit.';
        if (validate()) {
            const currentRevision = revision;
            timer = setTimeout(() => checkAvailability(currentRevision), 300);
        }
    }

    start.addEventListener('input', changed);
    end.addEventListener('input', changed);
    form.addEventListener('submit', (event) => {
        if (!validate()) {
            event.preventDefault();
            form.reportValidity();
        } else if (available === false) {
            event.preventDefault();
            message.textContent = 'Produk tidak tersedia pada jadwal yang dipilih. Pilih jadwal lain atau produk lain.';
            start.focus();
        }
    });
    document.getElementById('consentCheck')?.addEventListener('invalid', (event) => {
        event.target.setCustomValidity('Anda wajib menyetujui ketentuan pengembalian melalui Admin.');
    });
    document.getElementById('consentCheck')?.addEventListener('change', (event) => event.target.setCustomValidity(''));
    changed();
})();
