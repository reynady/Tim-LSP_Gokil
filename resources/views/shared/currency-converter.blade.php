<details class="currency-converter"
         data-currency-converter
         data-amount="{{ $amount }}"
         data-url="{{ route('currency.convert') }}">
    <summary>Konversi mata uang</summary>
    <div class="currency-converter-controls">
        <label>
            Ke
            <select data-currency-select aria-label="Pilih mata uang konversi">
                <option value="USD">USD</option>
                <option value="EUR">EUR</option>
                <option value="GBP">GBP</option>
                <option value="JPY">JPY</option>
                <option value="SGD">SGD</option>
                <option value="AUD">AUD</option>
                <option value="CAD">CAD</option>
                <option value="MYR">MYR</option>
            </select>
        </label>
        <button type="button" data-convert-currency>Lihat nilai</button>
        <span data-conversion-result role="status" aria-live="polite"></span>
    </div>
</details>
