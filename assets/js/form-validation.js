(function () {
    'use strict';

    const style = document.createElement('link');
    style.rel = 'stylesheet';
    style.href = '/darjibhai/assets/css/form-validation.css';
    document.head.appendChild(style);

    const fieldSelector = 'input:not([type="submit"]):not([type="button"]):not([type="reset"]), select, textarea';

    function fieldLabel(field) {
        const explicit = field.labels && field.labels.length ? field.labels[0].textContent : '';
        const wrapper = field.closest('.form-group, .mb-3, .mb-4, .modal-form-group, .col, [class*="col-"]');
        const nearby = wrapper && wrapper.querySelector('label');
        return (explicit || (nearby && nearby.textContent) || field.getAttribute('aria-label') || field.name || 'This field')
            .replace(/\s+/g, ' ').replace(/[*:]+$/g, '').trim();
    }

    function messageFor(field) {
        const label = fieldLabel(field);
        const validity = field.validity;
        if (field.type === 'hidden' && field.required && field.value.trim() === '') return 'Please choose ' + label.toLowerCase() + '.';
        if (field.dataset.match && field.value !== document.getElementById(field.dataset.match)?.value) return field.dataset.matchMessage || 'These values do not match.';
        if (validity.valueMissing) return label + ' is required.';
        if (validity.typeMismatch && field.type === 'email') return 'Enter a valid email address.';
        if (validity.typeMismatch && field.type === 'url') return 'Enter a valid website address.';
        if (validity.patternMismatch) return field.dataset.patternMessage || 'Use the requested format for ' + label.toLowerCase() + '.';
        if (validity.tooShort) return label + ' must be at least ' + field.minLength + ' characters.';
        if (validity.tooLong) return label + ' must be no more than ' + field.maxLength + ' characters.';
        if (validity.rangeUnderflow) return label + ' must be ' + field.min + ' or later.';
        if (validity.rangeOverflow) return label + ' must be ' + field.max + ' or earlier.';
        if (validity.stepMismatch || validity.badInput) return 'Enter a valid value for ' + label.toLowerCase() + '.';
        return field.dataset.errorMessage || 'Check the value entered for ' + label.toLowerCase() + '.';
    }

    function feedbackFor(field) {
        let feedback = field.validationMessageElement;
        if (!feedback) {
            feedback = document.createElement('div');
            feedback.className = 'custom-validation-message';
            feedback.id = 'validation-' + Math.random().toString(36).slice(2, 10);
            const inputWrap = field.closest('.password-input-wrap, .input-group-custom');
            (inputWrap || field).insertAdjacentElement('afterend', feedback);
            field.validationMessageElement = feedback;
            const describedBy = (field.getAttribute('aria-describedby') || '').split(/\s+/).filter(Boolean);
            describedBy.push(feedback.id);
            field.setAttribute('aria-describedby', describedBy.join(' '));
        }
        return feedback;
    }

    function paint(field, reveal) {
        if (field.disabled || (field.type === 'hidden' && !field.required)) return true;
        const matches = !field.dataset.match || field.value === document.getElementById(field.dataset.match)?.value;
        const requiredValuePresent = field.type !== 'hidden' || !field.required || field.value.trim() !== '';
        const valid = field.checkValidity() && matches && requiredValuePresent;
        if (valid) {
            field.classList.remove('is-invalid');
            field.classList.remove('is-valid');
            field.removeAttribute('aria-invalid');
            if (field.validationMessageElement) field.validationMessageElement.textContent = '';
        } else if (reveal) {
            field.classList.remove('is-valid');
            field.classList.add('is-invalid');
            field.setAttribute('aria-invalid', 'true');
            feedbackFor(field).textContent = messageFor(field);
        }
        return valid;
    }

    document.querySelectorAll('form').forEach(function (form) {
        if (form.method.toLowerCase() === 'get' && !form.querySelector('[required], [pattern], [min], [max], [minlength], [maxlength]')) return;
        form.noValidate = true;
        const fields = Array.from(form.querySelectorAll(fieldSelector));
        fields.forEach(function (field) {
            field.addEventListener('blur', function () { paint(field, true); });
            field.addEventListener('input', function () {
                if (field.classList.contains('is-invalid') || field.classList.contains('is-valid')) paint(field, true);
            });
            field.addEventListener('change', function () {
                if (field.classList.contains('is-invalid') || field.classList.contains('is-valid')) paint(field, true);
            });
        });
        form.addEventListener('submit', function (event) {
            let firstInvalid = null;
            fields.forEach(function (field) {
                if (!paint(field, true) && !firstInvalid) firstInvalid = field;
            });
            if (firstInvalid) {
                event.preventDefault();
                const visibleControl = firstInvalid.type === 'hidden'
                    ? firstInvalid.closest('[data-catalog-select]')?.querySelector('button')
                    : firstInvalid;
                if (visibleControl) {
                    visibleControl.focus({ preventScroll: true });
                    visibleControl.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            }
        });
        form.addEventListener('reset', function () {
            window.setTimeout(function () {
                fields.forEach(function (field) {
                    field.classList.remove('is-invalid', 'is-valid');
                    field.removeAttribute('aria-invalid');
                    if (field.validationMessageElement) field.validationMessageElement.textContent = '';
                });
            }, 0);
        });
    });
})();
