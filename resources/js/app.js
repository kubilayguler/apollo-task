import './bootstrap';

// Alpine.js
import Alpine from 'alpinejs';
window.Alpine = Alpine;

// Flatpickr
import flatpickr from 'flatpickr';
import 'flatpickr/dist/themes/dark.css';

window.flatpickr = flatpickr;

// FullCalendar
import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import interactionPlugin from '@fullcalendar/interaction';

window.Calendar = Calendar;
window.dayGridPlugin = dayGridPlugin;
window.timeGridPlugin = timeGridPlugin;
window.interactionPlugin = interactionPlugin;

// Start Alpine after all globals are set
Alpine.start();

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
