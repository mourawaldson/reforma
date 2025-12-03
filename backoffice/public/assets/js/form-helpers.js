// /assets/js/form-helpers.js
window.FormHelpers = (function() {
    // ---- Moeda ----
    function formatCurrencyBRFromNumber(num) {
        if (num === null || num === undefined || num === '') return '';
        const n = Number(num);
        if (isNaN(n)) return '';
        const fixed = n.toFixed(2);
        let [intPart, decPart] = fixed.split('.');
        intPart = intPart.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        return 'R$ ' + intPart + ',' + decPart;
    }

    function parseCurrencyToFloat(str) {
        if (!str) return null;
        str = String(str)
            .replace(/\s/g, '')
            .replace('R$', '')
            .replace(/\./g, '')
            .replace(',', '.');
        const n = parseFloat(str);
        return isNaN(n) ? null : n;
    }

    function formatCurrencyInput(value) {
        const digits = value.replace(/\D/g, '');
        if (!digits) return '';
        const intVal = parseInt(digits, 10);
        const num = intVal / 100;
        return formatCurrencyBRFromNumber(num);
    }

    function attachCurrencyMask(selector) {
        $(document).on('input', selector, function() {
            const caretPos = this.selectionStart;
            const oldLength = this.value.length;

            this.value = formatCurrencyInput(this.value);

            const newLength = this.value.length;
            const diff = newLength - oldLength;
            this.selectionStart = this.selectionEnd = caretPos + diff;
        });
    }

    // ---- Limpar erro Bootstrap ao alterar campo ----
    function clearInvalidOnChange(formSelector) {
        $(document).on('input change', formSelector + ' input, ' + formSelector + ' select, ' + formSelector + ' textarea', function() {
            $(this).removeClass('is-invalid');
        });
    }

    // ---- Helpers CPF/CNPJ ----
    function onlyDigits(str) {
        return (str || '').replace(/\D/g, '');
    }

    function isValidCpf(cpf) {
        cpf = onlyDigits(cpf);
        if (cpf.length !== 11) return false;
        if (/^(\d)\1{10}$/.test(cpf)) return false;

        let sum = 0;
        for (let i = 0; i < 9; i++) {
            sum += parseInt(cpf.charAt(i), 10) * (10 - i);
        }
        let r = (sum * 10) % 11;
        if (r === 10) r = 0;
        if (r !== parseInt(cpf.charAt(9), 10)) return false;

        sum = 0;
        for (let i = 0; i < 10; i++) {
            sum += parseInt(cpf.charAt(i), 10) * (11 - i);
        }
        r = (sum * 10) % 11;
        if (r === 10) r = 0;
        if (r !== parseInt(cpf.charAt(10), 10)) return false;

        return true;
    }

    function isValidCnpj(cnpj) {
        cnpj = onlyDigits(cnpj);
        if (cnpj.length !== 14) return false;
        if (/^(\d)\1{13}$/.test(cnpj)) return false;

        function calcDigit(base, weights) {
            let sum = 0;
            for (let i = 0; i < weights.length; i++) {
                sum += parseInt(base.charAt(i), 10) * weights[i];
            }
            const r = sum % 11;
            return (r < 2) ? 0 : (11 - r);
        }

        const w1 = [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
        const w2 = [6].concat(w1);

        const d1 = calcDigit(cnpj.substr(0, 12), w1);
        const d2 = calcDigit(cnpj.substr(0, 12) + String(d1), w2);

        return cnpj.endsWith(String(d1) + String(d2));
    }

    function validateCpfCnpj(str) {
        const digits = onlyDigits(str);
        if (!digits) return true; // vazio é válido (campo opcional)
        if (digits.length === 11) return isValidCpf(digits);
        if (digits.length === 14) return isValidCnpj(digits);
        return false;
    }

    // ---- Máscara visual CPF/CNPJ ----
    function formatCpfCnpjMask(value) {
        let digits = onlyDigits(value).slice(0, 14);

        if (digits.length <= 11) {
            // CPF: 000.000.000-00
            if (digits.length <= 3) return digits;
            if (digits.length <= 6) {
                return digits.replace(/(\d{3})(\d+)/, '$1.$2');
            }
            if (digits.length <= 9) {
                return digits.replace(/(\d{3})(\d{3})(\d+)/, '$1.$2.$3');
            }
            return digits.replace(/(\d{3})(\d{3})(\d{3})(\d{0,2})/, '$1.$2.$3-$4').replace(/-$/, '');
        } else {
            // CNPJ: 00.000.000/0000-00
            if (digits.length <= 2) return digits;
            if (digits.length <= 5) {
                return digits.replace(/(\d{2})(\d+)/, '$1.$2');
            }
            if (digits.length <= 8) {
                return digits.replace(/(\d{2})(\d{3})(\d+)/, '$1.$2.$3');
            }
            if (digits.length <= 12) {
                return digits.replace(/(\d{2})(\d{3})(\d{3})(\d+)/, '$1.$2.$3/$4');
            }
            return digits.replace(/(\d{2})(\d{3})(\d{3})(\d{4})(\d{0,2})/, '$1.$2.$3/$4-$5').replace(/-$/, '');
        }
    }

    function attachCpfCnpjMask(selector) {
        $(document).on('input', selector, function() {
            this.value = formatCpfCnpjMask(this.value);
        });
    }

    // ---- Validação genérica de texto ----
    /**
     * raw: string digitada
     * opts: { required?: boolean, maxLength?: number }
     * retorna { valid: boolean, value: string }
     */
    function validateTextField(raw, opts) {
        const value = (raw || '').trim();
        const required = !!(opts && opts.required);
        const maxLength = opts && opts.maxLength ? opts.maxLength : null;

        if (required && !value) {
            return { valid: false, value };
        }
        if (maxLength && value.length > maxLength) {
            return { valid: false, value };
        }
        return { valid: true, value };
    }

    return {
        // moeda
        formatCurrencyBRFromNumber,
        parseCurrencyToFloat,
        formatCurrencyInput,
        attachCurrencyMask,
        // bootstrap helpers
        clearInvalidOnChange,
        // cpf/cnpj
        validateCpfCnpj,
        attachCpfCnpjMask,
        // texto
        validateTextField
    };
})();
