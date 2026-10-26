import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import test from 'node:test';
import vm from 'node:vm';

const script = readFileSync(new URL('../../public/js/loan-schedule.js', import.meta.url), 'utf8');

function setup() {
    const handlers = new Map();
    function element(id) {
        const control = {
            value: '', min: '', max: '', disabled: false, textContent: '', error: '',
            setCustomValidity(error) { this.error = error; },
            addEventListener(name, handler) { handlers.set(`${id}:${name}`, handler); },
            removeAttribute(name) { this[name] = ''; },
            focus() { this.focused = true; },
        };
        Object.defineProperty(control, 'validity', {
            get: () => ({ valid: !control.error
                && (!control.min || control.value >= control.min)
                && (!control.max || control.value <= control.max) }),
        });
        return control;
    }
    const controls = Object.fromEntries([
        'loanReviewForm', 'starts_at', 'ends_at', 'availabilityMessage', 'scheduleSummary', 'consentCheck',
    ].map((id) => [id, element(id)]));
    const form = controls.loanReviewForm;
    form.dataset = {
        availabilityUrl: '/peminjaman/ketersediaan',
        serverNow: String(Date.parse('2026-10-26T08:00:00+07:00')),
    };
    form.elements = { monster_id: { value: '7' } };
    form.reportValidity = () => { form.reported = true; };
    let timer;
    let fetchResult = { available_quantity: 1, message: '1 unit tersedia' };
    let requestedUrl;
    vm.runInNewContext(script, {
        document: { getElementById: (id) => controls[id] },
        performance: { now: () => 0 },
        window: { location: { origin: 'https://example.test' } },
        Intl, Date, URL, URLSearchParams, AbortController,
        setTimeout: (callback) => { timer = callback; return 1; },
        clearTimeout: () => { timer = null; },
        fetch: async (url) => {
            requestedUrl = url;
            return { ok: true, json: async () => fetchResult };
        },
    });
    return {
        controls,
        change(start, end) {
            controls.starts_at.value = start;
            controls.ends_at.value = end;
            handlers.get('starts_at:input')();
        },
        submit() {
            let prevented = false;
            handlers.get('loanReviewForm:submit')({ preventDefault() { prevented = true; } });
            return prevented;
        },
        async check(result = fetchResult) {
            fetchResult = result;
            await timer?.();
            return requestedUrl;
        },
    };
}

test('both date/time fields are required with Indonesian messages', () => {
    const ui = setup();
    assert.match(ui.controls.starts_at.error, /mulai.*wajib diisi/);
    assert.match(ui.controls.ends_at.error, /selesai.*wajib diisi/);
    assert.equal(ui.submit(), true);
});

for (const [name, start, end, field, expected] of [
    ['past start', '2026-10-26T07:59', '2026-10-26T09:00', 'starts_at', /masa lalu/],
    ['equal end', '2026-10-27T09:00', '2026-10-27T09:00', 'ends_at', /lebih besar/],
    ['reversed end', '2026-10-27T09:00', '2026-10-27T08:59', 'ends_at', /lebih besar/],
    ['over 120 hours', '2026-10-27T09:00', '2026-11-01T09:01', 'ends_at', /120 jam/],
    ['date without time', '2026-10-27', '2026-10-28T09:00', 'starts_at', /tidak valid/],
]) {
    test(`frontend rejects ${name}`, () => {
        const ui = setup();
        ui.change(start, end);
        assert.match(ui.controls[field].error, expected);
        assert.equal(ui.submit(), true);
    });
}

test('exactly 120 hours is valid and summary uses WIB', async () => {
    const ui = setup();
    ui.change('2026-10-27T09:00', '2026-11-01T09:00');
    assert.equal(ui.controls.starts_at.error, '');
    assert.equal(ui.controls.ends_at.error, '');
    assert.match(ui.controls.scheduleSummary.textContent, /27 Oktober 2026.*09.00 WIB sampai 01 November 2026.*09.00 WIB/);
    assert.equal(ui.controls.ends_at.max, '2026-11-01T09:00');
    const url = await ui.check();
    assert.equal(url.searchParams.get('starts_at'), '2026-10-27T09:00');
    assert.equal(url.searchParams.get('monster_id'), '7');
    assert.equal(ui.submit(), false);
});

test('unavailable preview prevents confirmation; changing schedule clears stale result', async () => {
    const ui = setup();
    ui.change('2026-10-27T09:00', '2026-10-27T12:00');
    await ui.check({ available_quantity: 0, message: 'Produk tidak tersedia' });
    assert.equal(ui.submit(), true);
    assert.match(ui.controls.availabilityMessage.textContent, /Produk tidak tersedia/);
    ui.change('2026-10-27T12:00', '2026-10-27T13:00');
    await ui.check({ available_quantity: 1, message: '1 unit tersedia' });
    assert.equal(ui.submit(), false);
});

test('input timestamps remain WIB when device timezone is outside Indonesia', () => {
    const original = process.env.TZ;
    try {
        process.env.TZ = 'America/New_York';
        const ui = setup();
        ui.change('2026-10-26T08:01', '2026-10-26T09:00');
        assert.equal(ui.controls.starts_at.min, '2026-10-26T08:00');
        assert.equal(ui.controls.starts_at.error, '');
        assert.match(ui.controls.scheduleSummary.textContent, /08.01 WIB sampai.*09.00 WIB/);
        assert.equal(ui.submit(), false);
    } finally {
        if (original === undefined) delete process.env.TZ;
        else process.env.TZ = original;
    }
});
