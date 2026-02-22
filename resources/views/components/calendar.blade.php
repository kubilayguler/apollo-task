@props(['tasks'])

<div class="rounded-xl border border-border-dark bg-surface-dark/40 p-4" x-data="calendarComponent()" x-init="initCalendar()">
    <h3 class="mb-4 text-sm font-bold text-app-text flex items-center gap-2">
        <span class="material-symbols-outlined text-[18px] text-primary">calendar_month</span>
        Calendar
        <button type="button"
            onclick="if(window.Alpine){ const d=Alpine.$data(document.querySelector('[x-data]')); if(d) d.activeModal='ai-home-modal'; }"
            class="ml-auto inline-flex items-center gap-1 rounded-lg bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500 px-2.5 py-1 text-[11px] font-semibold text-white shadow-md shadow-purple-500/20 hover:shadow-purple-500/40 hover:scale-105 transition-all">
            <span class="material-symbols-outlined text-[14px]">auto_awesome</span>
            AI Planner
        </button>
    </h3>
    <div x-ref="calendar" class="min-h-[400px] text-xs"></div>
</div>

@push('scripts')
    @php
        $calendarTasks = $tasks->map(function ($task) {
            $priority = strtolower($task->priority ?? 'none');
            $bgColor = match ($priority) {
                'high' => '#ef4444', // red-500
                'medium' => '#eab308', // yellow-500
                'low' => '#3b82f6', // blue-500
                default => '#64748b', // slate-500
            };

            return [
                'id' => $task->id,
                'title' => $task->title,
                'start' => $task->due_date->format('Y-m-d'),
                'allDay' => true,
                'url' => route('dashboard', ['project' => $task->project_id]),
                'backgroundColor' => $bgColor,
                'borderColor' => $bgColor,
                'className' => $task->status === 'completed' ? 'opacity-50 line-through' : '',
            ];
        });
    @endphp
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('calendarComponent', () => ({
                calendar: null,
                initCalendar() {
                    const tasks = @json($calendarTasks);

                    this.calendar = new Calendar(this.$refs.calendar, {
                        plugins: [dayGridPlugin, timeGridPlugin, interactionPlugin],
                        initialView: 'dayGridMonth',
                        headerToolbar: {
                            left: 'prev,next today',
                            center: 'title',
                            right: 'dayGridMonth,timeGridWeek,timeGridDay'
                        },
                        events: tasks,
                        height: 'auto',
                        themeSystem: 'standard',
                        eventClick: function(info) {
                            if (info.event.url) {
                                info.jsEvent.preventDefault();
                                // Trigger global loading state before navigation
                                if (window.Alpine) {
                                    Alpine.store('global').setLoading(true);
                                }
                                window.location.href = info.event.url;
                            }
                        }
                    });

                    this.calendar.render();

                    // Watch for global loading state changes
                    this.$watch('$store.global.loading', (isLoading) => {
                        if (!isLoading) {
                            // Wait for Alpine to remove display:none
                            setTimeout(() => {
                                this.calendar.updateSize();
                            }, 50);
                        }
                    });

                    // If already loaded, update size
                    if (!this.$store.global.loading) {
                        setTimeout(() => {
                            this.calendar.updateSize();
                        }, 50);
                    }
                }
            }));
        });
    </script>
    <style>
        /* FullCalendar Dark Theme Overrides */
        .fc {
            background-color: transparent !important;
        }

        .fc-theme-standard td,
        .fc-theme-standard th {
            border-color: rgba(255, 255, 255, 0.05) !important;
        }

        .fc-theme-standard .fc-scrollgrid {
            border-color: rgba(255, 255, 255, 0.05) !important;
            border-radius: 0.75rem;
            overflow: hidden;
            background-color: transparent !important;
        }

        .fc-daygrid-day,
        .fc-timegrid-col {
            background-color: transparent !important;
        }

        .fc-daygrid-day-frame,
        .fc-timegrid-col-frame {
            background-color: transparent !important;
        }

        .fc-daygrid-day-frame {
            padding: 2px !important;
        }

        .fc-daygrid-event {
            margin-top: 2px !important;
        }

        .fc-daygrid-block-event {
            margin-bottom: 2px !important;
        }

        .fc-scrollgrid-section-header th,
        .fc-scrollgrid-section-body td {
            background-color: transparent !important;
        }

        .fc-timegrid-axis-frame,
        .fc-timegrid-slot-label-frame {
            background-color: transparent !important;
        }

        .fc-timegrid-slot-lane {
            background-color: transparent !important;
        }

        .fc-timegrid-col-bg {
            background-color: transparent !important;
        }

        .fc-daygrid-day-bg {
            background-color: transparent !important;
        }

        .fc-timegrid-now-indicator-container {
            background-color: transparent !important;
        }

        .fc-scrollgrid-sync-table {
            background-color: transparent !important;
        }

        .fc-timegrid-body {
            background-color: transparent !important;
        }

        .fc-timegrid-slots table {
            background-color: transparent !important;
        }

        .fc-timegrid-cols table {
            background-color: transparent !important;
        }

        .fc-col-header {
            background-color: transparent !important;
        }

        .fc-scrollgrid-section-sticky>* {
            background-color: transparent !important;
        }

        .fc-scroller-harness {
            background-color: transparent !important;
        }

        .fc-scroller {
            background-color: transparent !important;
        }

        .fc-daygrid-body {
            background-color: transparent !important;
        }

        .fc-timegrid-axis-cushion {
            background-color: transparent !important;
        }

        .fc-timegrid-slot-label-cushion {
            background-color: transparent !important;
        }

        .fc-timegrid-axis {
            background-color: transparent !important;
        }

        .fc-timegrid-slot-label {
            background-color: transparent !important;
        }

        .fc-button-primary {
            background-color: rgba(255, 255, 255, 0.05) !important;
            border-color: rgba(255, 255, 255, 0.1) !important;
            color: #94a3b8 !important;
            text-transform: capitalize !important;
            border-radius: 0.5rem !important;
            padding: 0.4rem 0.75rem !important;
            font-size: 0.75rem !important;
            font-weight: 500 !important;
            transition: all 0.2s !important;
        }

        .fc-button-primary:hover {
            background-color: rgba(255, 255, 255, 0.1) !important;
            color: #f8fafc !important;
        }

        .fc-button-active {
            background-color: #085CEB !important;
            border-color: #085CEB !important;
            color: #ffffff !important;
        }

        .fc-toolbar-title {
            font-size: 1.125rem !important;
            font-weight: 600 !important;
            color: #f8fafc;
        }

        .fc-col-header-cell {
            background-color: #085CEB !important;
            border-color: #085CEB !important;
        }

        .fc-col-header-cell-cushion {
            color: #ffffff !important;
            font-weight: 600 !important;
            padding: 10px 0 !important;
            text-decoration: none !important;
        }

        .fc-daygrid-day-number {
            color: #cbd5e1;
            font-size: 0.75rem;
            padding: 8px !important;
            text-decoration: none !important;
        }

        .fc-daygrid-day-number:hover {
            color: #f8fafc;
            text-decoration: underline !important;
        }

        .fc-day-today {
            background-color: rgba(8, 92, 235, 0.05) !important;
        }

        .fc-timegrid-col.fc-day-today,
        .fc-daygrid-day.fc-day-today {
            background-color: rgba(8, 92, 235, 0.05) !important;
        }

        .fc-event {
            border-radius: 4px;
            padding: 3px 6px;
            font-size: 0.7rem;
            font-weight: 600;
            cursor: pointer;
            transition: opacity 0.2s, transform 0.2s;
            border: none !important;
            margin: 2px 4px !important;
        }

        .fc-event:hover {
            opacity: 0.9;
            transform: scale(1.02);
        }

        .fc-event-title {
            font-weight: 600 !important;
        }

        /* Additional Dark Theme Fixes */
        .fc-view-harness {
            background-color: transparent !important;
        }

        .fc-timegrid-slot-label-cushion,
        .fc-timegrid-axis-cushion {
            color: #94a3b8 !important;
            font-size: 0.75rem;
            padding: 0 4px !important;
        }

        .fc-timegrid-axis-frame {
            justify-content: center;
        }

        .fc-timegrid-slot-label-frame {
            text-align: center;
        }

        .fc-timegrid-divider {
            background-color: rgba(255, 255, 255, 0.02) !important;
            border-color: rgba(255, 255, 255, 0.05) !important;
            padding: 2px !important;
        }

        .fc-timegrid-slot {
            background-color: transparent !important;
            border-bottom-color: rgba(255, 255, 255, 0.03) !important;
        }

        .fc-timegrid-col-events {
            background-color: transparent !important;
        }

        .fc-daygrid-day-events {
            background-color: transparent !important;
            margin-bottom: 0 !important;
        }

        .fc-daygrid-event-harness {
            background-color: transparent !important;
            margin-bottom: 2px !important;
        }

        .fc-daygrid-day-bottom {
            background-color: transparent !important;
            padding: 2px !important;
        }

        .fc-daygrid-more-link {
            color: #085CEB !important;
            font-weight: 600 !important;
            font-size: 0.7rem !important;
        }

        .fc-scrollgrid-sync-inner {
            background-color: transparent !important;
        }

        .fc-timegrid-now-indicator-line {
            border-color: #ef4444 !important;
            border-width: 2px 0 0 !important;
        }

        .fc-timegrid-now-indicator-arrow {
            border-color: #ef4444 !important;
            border-width: 5px 0 5px 6px !important;
            border-top-color: transparent !important;
            border-bottom-color: transparent !important;
        }

        .fc-day-other {
            background-color: rgba(255, 255, 255, 0.02) !important;
        }

        .fc-highlight {
            background-color: rgba(8, 92, 235, 0.2) !important;
        }

        .fc-timegrid-col-events {
            margin: 0 2px !important;
        }

        .fc-timegrid-event-harness {
            background-color: transparent !important;
        }

        .fc-timegrid-event {
            border-radius: 4px;
            padding: 2px 4px;
            font-size: 0.7rem;
            font-weight: 600;
            cursor: pointer;
            transition: opacity 0.2s, transform 0.2s;
            border: none !important;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
        }

        .fc-timegrid-event:hover {
            opacity: 0.9;
            transform: scale(1.02);
            z-index: 5 !important;
        }

        .fc-v-event {
            border: none !important;
        }

        .fc-timegrid-event .fc-event-main {
            padding: 2px;
        }

        .fc-timegrid-event .fc-event-time {
            font-size: 0.65rem;
            margin-bottom: 2px;
            opacity: 0.9;
        }

        .fc-timegrid-event .fc-event-title {
            font-size: 0.7rem;
            font-weight: 600;
            line-height: 1.2;
        }

        .fc-timegrid-event-harness-inset .fc-timegrid-event {
            box-shadow: 0 0 0 1px #1e293b;
        }

        .fc-timegrid-slot-minor {
            border-bottom-style: dashed !important;
            border-bottom-color: rgba(255, 255, 255, 0.01) !important;
        }

        .fc-list-day-cushion {
            background-color: rgba(255, 255, 255, 0.02) !important;
            color: #f8fafc !important;
            font-weight: 600 !important;
        }

        .fc-list-event:hover td {
            background-color: rgba(255, 255, 255, 0.05) !important;
        }

        .fc-list-event-title {
            color: #cbd5e1 !important;
        }

        .fc-list-event-time {
            color: #94a3b8 !important;
        }

        .fc-list-empty-message {
            color: #94a3b8 !important;
            background-color: transparent !important;
        }

        .fc-list-event-dot {
            border-color: currentColor !important;
        }

        .fc-popover {
            background-color: #1e293b !important;
            border-color: rgba(255, 255, 255, 0.1) !important;
            border-radius: 0.5rem !important;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.5), 0 4px 6px -2px rgba(0, 0, 0, 0.25) !important;
        }

        .fc-popover-header {
            background-color: rgba(255, 255, 255, 0.05) !important;
            color: #f8fafc !important;
            border-top-left-radius: 0.5rem !important;
            border-top-right-radius: 0.5rem !important;
            padding: 0.5rem 0.75rem !important;
        }

        .fc-popover-body {
            color: #cbd5e1 !important;
            padding: 0.5rem !important;
        }

        .fc-popover-close {
            color: #94a3b8 !important;
            opacity: 1 !important;
            transition: color 0.2s !important;
        }

        .fc-popover-close:hover {
            color: #f8fafc !important;
        }

        .fc-more-link {
            color: #085CEB !important;
            font-weight: 600 !important;
            background-color: transparent !important;
            padding: 2px 4px !important;
            border-radius: 4px !important;
            transition: background-color 0.2s !important;
        }

        .fc-more-link:hover {
            background-color: rgba(8, 92, 235, 0.1) !important;
            text-decoration: none !important;
        }

        .fc-more-popover {
            background-color: #1e293b !important;
            border-color: rgba(255, 255, 255, 0.1) !important;
            border-radius: 0.5rem !important;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.5), 0 4px 6px -2px rgba(0, 0, 0, 0.25) !important;
        }

        .fc-more-popover .fc-popover-header {
            background-color: rgba(255, 255, 255, 0.05) !important;
            color: #f8fafc !important;
            border-top-left-radius: 0.5rem !important;
            border-top-right-radius: 0.5rem !important;
            padding: 0.5rem 0.75rem !important;
        }

        .fc-more-popover .fc-popover-body {
            color: #cbd5e1 !important;
            padding: 0.5rem !important;
        }

        .fc-more-popover .fc-popover-close {
            color: #94a3b8 !important;
            opacity: 1 !important;
            transition: color 0.2s !important;
        }

        .fc-more-popover .fc-popover-close:hover {
            color: #f8fafc !important;
        }

        .fc-popover-title {
            font-weight: 600 !important;
            font-size: 0.875rem !important;
        }

        .fc-theme-standard .fc-popover {
            border: 1px solid rgba(255, 255, 255, 0.1) !important;
        }

        .fc-theme-standard .fc-popover-header {
            background: rgba(255, 255, 255, 0.05) !important;
        }

        .fc-theme-standard .fc-popover-body {
            background: #1e293b !important;
        }

        .fc-theme-standard .fc-list-day-cushion {
            background-color: rgba(255, 255, 255, 0.02) !important;
        }

        .fc-theme-standard .fc-list-event:hover td {
            background-color: rgba(255, 255, 255, 0.05) !important;
        }

        .fc-theme-standard .fc-list-empty-message {
            background-color: transparent !important;
        }

        .fc-theme-standard .fc-list-event-dot {
            border-color: currentColor !important;
        }

        .fc-theme-standard .fc-more-popover {
            border: 1px solid rgba(255, 255, 255, 0.1) !important;
        }

        .fc-theme-standard .fc-more-popover .fc-popover-header {
            background: rgba(255, 255, 255, 0.05) !important;
        }

        .fc-theme-standard .fc-more-popover .fc-popover-body {
            background: #1e293b !important;
        }

        .fc-theme-standard .fc-popover-title {
            color: #f8fafc !important;
        }
    </style>
@endpush
