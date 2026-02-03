<script setup lang="ts">
import { ref, watch } from 'vue';
import type { CalendarOptions, EventApi, DateSelectArg, EventClickArg, DateClickArg } from '@fullcalendar/core';
import { isToday, isFuture, parseISO, isPast } from 'date-fns'; // helpful date utils
import FullCalendar from '@fullcalendar/vue3';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import interactionPlugin from '@fullcalendar/interaction';

const props = defineProps<{
	events?: Array<{
		id: string | number;
		title: string;
		start: string | Date;
		end?: string | Date;
		allDay?: boolean;
		user?: string;
	}>;
}>();

const emit = defineEmits<{
	(e: 'dateClick', arg: DateClickArg): void;
	(e: 'eventAdd', event: EventApi): void;
	(e: 'eventUpdate', event: EventApi): void;
	(e: 'eventRemove', event: EventApi): void;
	(e: 'eventClick', event: EventApi): void;
}>();

const calendarOptions = ref<CalendarOptions>({
	plugins: [dayGridPlugin, timeGridPlugin, interactionPlugin],
	headerToolbar: {
		left: 'prev,next today',
		center: 'title',
		right: 'dayGridMonth,timeGridWeek,timeGridDay',
	},
	initialView: 'dayGridMonth',
	editable: true,
	selectable: true,
	selectMirror: true,
	dayMaxEvents: true,
	height: 650,
	weekends: true,
	events: props.events, // Initialize with props
	eventClick: handleEventClick,
	eventsSet: handleEvents,
	dateClick: handleDateClick,
	eventAdd: handleEventAdd,
	eventChange: handleEventChange,
	eventRemove: handleEventRemove,
	eventClassNames: (arg) => {
		const eventStart = typeof arg.event.start === 'string' ? parseISO(arg.event.start) : arg.event.start;

		if (isToday(eventStart)) {
			return ['bg-primary', 'text-white'];
		} else if (isFuture(eventStart)) {
			return ['bg-green-300', 'text-black'];
		} else if (isPast(eventStart)) {
			return ['bg-red-200', 'text-muted-foreground'];
		}
		return [];
	},
});

const calendarApi = ref<any>(null);
const currentEvents = ref<EventApi[]>([]);

// Watch for changes in events prop and update calendar
watch(
	() => props.events,
	(newEvents) => {
		if (calendarApi.value) {
			calendarApi.value.removeAllEvents();
			if (newEvents && newEvents.length) {
				calendarApi.value.addEventSource(newEvents);
			}
		}
	},
	{ deep: true }
);

function handleDateClick(clickInfo: DateClickArg) {
	emit('dateClick', clickInfo);
}

function handleEventAdd(addInfo: { event: EventApi }) {
	emit('eventAdd', addInfo.event);
}

function handleEventChange(changeInfo: { event: EventApi }) {
	emit('eventUpdate', changeInfo.event);
}

function handleEventRemove(removeInfo: { event: EventApi }) {
	emit('eventRemove', removeInfo.event);
}

function handleEventClick(clickInfo: EventClickArg) {
	emit('eventClick', clickInfo.event);
	// if (confirm(`Are you sure you want to delete the event '${clickInfo.event.title}'`)) {
	//   clickInfo.event.remove();
	// }
}

function handleEvents(events: EventApi[]) {
	currentEvents.value = events;
	// Get the calendar API instance
	calendarApi.value = events[0]?.view?.calendar;
}
</script>

<template>
	<div class="demo-app">
		<div class="demo-app-main">
			<FullCalendar class="demo-app-calendar" :options="calendarOptions">
				<template v-slot:eventContent="arg">
					<div class="rounded-lg px-1">
						<b>{{ arg.timeText }}:</b>
						<span class="ml-1 truncate">{{ arg.event.title }}</span>
						<span class="ml-1 truncate">{{ arg.event.user }}</span>
					</div>
				</template>
			</FullCalendar>
		</div>
	</div>
</template>

<style scoped>
/* Override FullCalendar colors */
:root {
	--fc-border-color: #e2e8f0;
	--fc-daygrid-event-dot-width: 5px;
}

.demo-app-calendar {
	/* Button styles */
	--fc-button-bg-color: #fdd847;
	--fc-button-hover-bg-color: #eabd08;
	--fc-button-active-bg-color: #facc15;
	--fc-button-active-border-color: #facc15;
	--fc-button-border-color: transparent;

	/* Calendar styles */
	--fc-page-bg-color: #ffffff;
	--fc-today-bg-color: ##fefae8;
	--fc-event-bg-color: #fee78a;
	--fc-event-border-color: #facc15;
	--fc-highlight-color: #eabd08;

	/* Text colors */
	--fc-button-text-color: #000000;
	--fc-event-text-color: #000000;
}

/* Custom button styles */
.fc .fc-button {
	@apply rounded-md px-4 py-2 text-sm font-medium shadow-sm transition-colors;
}

/* Custom event styles */
.fc-event {
	@apply rounded-md border-none px-2 py-1 text-sm;
}

/* Custom today indicator */
.fc .fc-daygrid-day.fc-day-today {
	@apply !bg-secondary-foreground;
}

.fc-event-today {
	@apply bg-blue-500 text-white border-none;
}

.fc-event-future {
	@apply bg-green-500 text-white border-none;
}
</style>
