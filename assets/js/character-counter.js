class CharacterCounter {
    constructor(options = {}) {
        this.options = {
            warningThreshold: 20,
            showIcons: true,
            rtl: true,
            persianNumbers: true,
            containerClass: 'character-counter',
            normalClass: 'text-slate-500 dark:text-slate-400',
            warningClass: 'text-orange-600 dark:text-orange-400',
            errorClass: 'text-red-600 dark:text-red-400',
            fieldNormalClass: '',
            fieldWarningClass: 'near-limit',
            fieldErrorClass: 'over-limit',
            ...options
        };

        this.counters = [];
        this.init();
    }

    init() {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => this.ready());
        } else {
            this.ready();
        }
    }

    ready() {
        if (this.options.fields && Array.isArray(this.options.fields)) {
            this.options.fields.forEach(field => {
                this.addField(field.selector, field.maxLength, field.label);
            });
        }
    }

    // Add a single field
    addField(selector, maxLength, label = '') {
        const field = document.querySelector(selector);
        if (!field) {
            console.warn(`Character Counter: Field with selector "${selector}" not found`);
            return null;
        }

        const counter = this.createCounter(field, maxLength, label);
        this.counters.push({
            field,
            counter,
            maxLength,
            label,
            selector
        });

        return counter;
    }

    addFields(fieldsConfig) {
        if (!Array.isArray(fieldsConfig)) {
            console.error('Character Counter: addFields expects an array');
            return;
        }

        fieldsConfig.forEach(config => {
            if (config.selector && config.maxLength) {
                this.addField(config.selector, config.maxLength, config.label || '');
            }
        });
    }

    createCounter(field, maxLength, label) {
        const counter = document.createElement('div');
        counter.className = this.buildCounterClass();

        // Insert counter after the field or in specified container
        const insertTarget = field.parentNode;
        insertTarget.insertBefore(counter, field.nextSibling);

        const updateCounter = () => {
            const currentLength = field.value.length;
            const remaining = maxLength - currentLength;

            counter.innerHTML = this.getCounterHTML(currentLength, maxLength, remaining, label);
            counter.className = this.getCounterClass(remaining);

            if (this.options.fieldNormalClass || this.options.fieldWarningClass || this.options.fieldErrorClass) {
                this.updateFieldStyling(field, remaining);
            }
        };

        updateCounter();

        field.addEventListener('input', updateCounter);
        field.addEventListener('paste', () => setTimeout(updateCounter, 10));
        field.addEventListener('keydown', (e) => {
            // Optional: Prevent typing when over limit
            if (this.options.preventOverLimit && field.value.length >= maxLength &&
                !['Backspace', 'Delete', 'ArrowLeft', 'ArrowRight', 'Tab'].includes(e.key)) {
                e.preventDefault();
            }
        });

        return counter;
    }

    buildCounterClass() {
        const classes = [this.options.containerClass, 'text-sm', 'mt-1'];
        if (this.options.rtl) {
            classes.push('text-right');
        } else {
            classes.push('text-left');
        }
        return classes.join(' ');
    }

    updateFieldStyling(field, remaining) {
        if (this.options.fieldNormalClass) field.classList.remove(this.options.fieldNormalClass);
        if (this.options.fieldWarningClass) field.classList.remove(this.options.fieldWarningClass);
        if (this.options.fieldErrorClass) field.classList.remove(this.options.fieldErrorClass);

        if (remaining < 0 && this.options.fieldErrorClass) {
            field.classList.add(this.options.fieldErrorClass);
        } else if (remaining <= this.options.warningThreshold && this.options.fieldWarningClass) {
            field.classList.add(this.options.fieldWarningClass);
        } else if (this.options.fieldNormalClass) {
            field.classList.add(this.options.fieldNormalClass);
        }
    }

    getCounterHTML(currentLength, maxLength, remaining, label) {
        const currentDisplay = this.options.persianNumbers ? this.toPersianNumbers(currentLength) : currentLength;
        const maxDisplay = this.options.persianNumbers ? this.toPersianNumbers(maxLength) : maxLength;

        let icon = '';
        if (this.options.showIcons) {
            if (remaining < 0) {
                icon = `<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 inline ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>`;
            } else if (remaining <= this.options.warningThreshold) {
                icon = `<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 inline ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.232 16.5c-.77.833.192 2.5 1.732 2.5z" />
                </svg>`;
            }
        }

        if (remaining < 0) {
            const overLimit = this.options.persianNumbers ? this.toPersianNumbers(Math.abs(remaining)) : Math.abs(remaining);
            const labelText = label ? ` برای ${label}` : '';
            return `${icon}<span>${overLimit} کاراکتر بیش از حد مجاز${labelText}</span>`;
        } else if (remaining <= this.options.warningThreshold) {
            const remainingDisplay = this.options.persianNumbers ? this.toPersianNumbers(remaining) : remaining;
            return `${icon}<span>${remainingDisplay} کاراکتر باقی‌مانده از ${maxDisplay}</span>`;
        } else {
            return `<span>${currentDisplay} / ${maxDisplay} کاراکتر</span>`;
        }
    }

    getCounterClass(remaining) {
        const baseClass = this.buildCounterClass();

        if (remaining < 0) {
            return `${baseClass} ${this.options.errorClass}`;
        } else if (remaining <= this.options.warningThreshold) {
            return `${baseClass} ${this.options.warningClass}`;
        } else {
            return `${baseClass} ${this.options.normalClass}`;
        }
    }

    toPersianNumbers(num) {
        const persianDigits = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        return num.toString().replace(/\d/g, digit => persianDigits[parseInt(digit)]);
    }

    removeCounter(selector) {
        const index = this.counters.findIndex(c => c.selector === selector);
        if (index !== -1) {
            const counter = this.counters[index];
            if (counter.counter && counter.counter.parentNode) {
                counter.counter.parentNode.removeChild(counter.counter);
            }
            this.counters.splice(index, 1);
            return true;
        }
        return false;
    }

    removeAllCounters() {
        this.counters.forEach(counter => {
            if (counter.counter && counter.counter.parentNode) {
                counter.counter.parentNode.removeChild(counter.counter);
            }
        });
        this.counters = [];
    }

    updateOptions(newOptions) {
        this.options = { ...this.options, ...newOptions };
    }

    getCounterInfo(selector) {
        return this.counters.find(c => c.selector === selector);
    }

    getAllCounters() {
        return this.counters.map(counter => ({
            selector: counter.selector,
            currentLength: counter.field.value.length,
            maxLength: counter.maxLength,
            remaining: counter.maxLength - counter.field.value.length,
            label: counter.label
        }));
    }
}

window.CharacterCounter = CharacterCounter;