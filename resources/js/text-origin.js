// "ما نوع النص؟": show only the fields that fit the chosen kind, and mark the required ones.
// The server enforces the same rules (QuoteRequest); this only keeps the form short and clear.

export function initTextOrigin(root = document) {
    root.querySelectorAll('[data-text-origin]').forEach((fieldset) => {
        const form = fieldset.closest('form');
        const radios = [...fieldset.querySelectorAll('input[name="kind"]')];

        const apply = () => {
            const kind = radios.find((radio) => radio.checked)?.value ?? 'quoted';

            fieldset.querySelectorAll('[data-show-for]').forEach((block) => {
                block.hidden = !block.dataset.showFor.split(' ').includes(kind);
            });

            fieldset.querySelectorAll('[data-required-for]').forEach((input) => {
                input.required = input.dataset.requiredFor === kind;
            });

            form?.dispatchEvent(new Event('change'));
        };

        radios.forEach((radio) => radio.addEventListener('change', apply));
        apply();
    });
}
