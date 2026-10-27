document.querySelectorAll('[data-currency-converter]').forEach((widget) => {
    const button = widget.querySelector('[data-convert-currency]');
    const currencySelect = widget.querySelector('[data-currency-select]');
    const result = widget.querySelector('[data-conversion-result]');

    button.addEventListener('click', async () => {
        button.disabled = true;
        result.textContent = 'Mengambil kurs terbaru...';

        try {
            const params = new URLSearchParams({
                amount: widget.dataset.amount,
                currency: currencySelect.value,
            });
            const response = await fetch(`${widget.dataset.url}?${params}`, {
                headers: { Accept: 'application/json' },
            });
            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.message || 'Kurs tidak tersedia.');
            }

            const formatted = new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: data.to,
                maximumFractionDigits: 2,
            }).format(data.amount);
            result.textContent = `Perkiraan: ${formatted} (kurs ${data.rate_date})`;
        } catch (error) {
            result.textContent = error instanceof Error
                ? error.message
                : 'Kurs tidak tersedia. Silakan coba lagi.';
        } finally {
            button.disabled = false;
        }
    });
});
