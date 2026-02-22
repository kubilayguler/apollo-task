@props(['name', 'value' => '', 'placeholder' => 'Select date'])

<div class="relative flex items-center" x-data="{ date: '{{ $value }}' }" x-init="flatpickr($refs.input, {
    dateFormat: 'Y-m-d',
    allowInput: false,
    disableMobile: false,
    theme: 'dark',
    defaultDate: date,
    onChange: function(selectedDates, dateStr) {
        date = dateStr;
    }
})">
    <span
        class="material-symbols-outlined absolute left-2.5 top-1/2 -translate-y-1/2 text-[16px] text-app-muted pointer-events-none">calendar_today</span>

    <input type="text" x-ref="input" name="{{ $name }}" x-model="date" placeholder="{{ $placeholder }}" readonly
        class="h-8 w-32 sm:w-36 rounded-md border border-border-dark bg-background-dark pl-8 pr-7 text-xs text-app-muted hover:text-app-text hover:border-app-muted/50 focus:border-primary focus:outline-none transition-colors cursor-pointer placeholder:text-app-muted">

    <button type="button" x-show="date" @click="date = ''; $refs.input._flatpickr.clear()"
        class="absolute right-1.5 top-1/2 -translate-y-1/2 text-app-muted hover:text-app-text p-0.5 rounded-md hover:bg-surface-dark transition-colors"
        style="display: none;">
        <span class="material-symbols-outlined text-[14px] block">close</span>
    </button>
</div>
