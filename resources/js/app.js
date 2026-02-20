import './bootstrap';

// Alpine.js
import Alpine from 'alpinejs';
window.Alpine = Alpine;
Alpine.start();

// Flatpickr
import flatpickr from 'flatpickr';
import 'flatpickr/dist/themes/dark.css';

window.flatpickr = flatpickr;

// Boot date pickers after DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-datepicker]').forEach(el => {
        flatpickr(el, {
            dateFormat: 'Y-m-d',
            allowInput: true,
            disableMobile: false,
            theme: 'dark',
        });
    });
});
