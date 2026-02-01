<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

import AppLayout from '@/layouts/AppLayout.vue';
import { useTenantUrl } from '@/composables/useTenantUrl';
import { type BreadcrumbItem } from '@/types';

const { dashboardUrl } = useTenantUrl();

interface EventPayload {
    id: number;
    hash_id: string;
    title: string;
    description: string | null;
    starts_at: string | null;
    ends_at: string | null;
    status: string;
    is_public: boolean;
    capacity: number | null;
    rsvp_count: number;
}

const props = defineProps<{
    events: EventPayload[];
}>();

const progressEvents = computed(() =>
    props.events
        .filter((event) => event.capacity && event.capacity > 0)
        .map((event) => ({
            ...event,
            progress: Math.min(
                100,
                Math.round((event.rsvp_count / event.capacity!) * 100),
            ),
        })),
);

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboardUrl.value,
    },
];

const weekdayLabels = ['Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sab', 'Dom'];

const monthCursor = ref(new Date());
const selectedDateKey = ref(formatDateKey(new Date()));

const monthLabel = computed(() =>
    monthCursor.value.toLocaleString('pt-BR', {
        month: 'long',
        year: 'numeric',
    }),
);

const eventsByDate = computed(() => {
    const map = new Map<string, EventPayload[]>();

    for (const event of props.events) {
        if (!event.starts_at) {
            continue;
        }

        const dateKey = event.starts_at.split(' ')[0];
        const current = map.get(dateKey) ?? [];
        current.push(event);
        map.set(dateKey, current);
    }

    return map;
});

const calendarDays = computed(() => {
    const year = monthCursor.value.getFullYear();
    const month = monthCursor.value.getMonth();
    const firstDay = new Date(year, month, 1);
    const weekdayOffset = (firstDay.getDay() + 6) % 7;
    const startDate = new Date(year, month, 1 - weekdayOffset);

    return Array.from({ length: 42 }, (_, index) => {
        const date = new Date(
            startDate.getFullYear(),
            startDate.getMonth(),
            startDate.getDate() + index,
        );
        const dateKey = formatDateKey(date);

        return {
            date,
            dateKey,
            inMonth: date.getMonth() === month,
            events: eventsByDate.value.get(dateKey) ?? [],
        };
    });
});

const selectedEvents = computed(
    () => eventsByDate.value.get(selectedDateKey.value) ?? [],
);

const selectedDateLabel = computed(() => {
    const [year, month, day] = selectedDateKey.value.split('-').map(Number);
    const date = new Date(year, month - 1, day);
    return date.toLocaleDateString('pt-BR', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    });
});

function formatDateKey(date: Date): string {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
}

function goToPreviousMonth(): void {
    monthCursor.value = new Date(
        monthCursor.value.getFullYear(),
        monthCursor.value.getMonth() - 1,
        1,
    );
}

function goToNextMonth(): void {
    monthCursor.value = new Date(
        monthCursor.value.getFullYear(),
        monthCursor.value.getMonth() + 1,
        1,
    );
}

function selectDate(dateKey: string): void {
    selectedDateKey.value = dateKey;
}

watch(monthCursor, () => {
    const firstOfMonth = new Date(
        monthCursor.value.getFullYear(),
        monthCursor.value.getMonth(),
        1,
    );
    selectedDateKey.value = formatDateKey(firstOfMonth);
});
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-6 p-4">
            <section class="rounded-2xl border border-sidebar-border/70 bg-white p-6 shadow-sm">
                <div class="flex items-center justify-between">
                    <span class="text-sm font-medium text-slate-700">Capacidade x presenca</span>
                    <span class="text-xs text-emerald-600">Eventos</span>
                </div>
                <div class="mt-4 max-h-64 space-y-3 overflow-y-auto pr-2">
                    <div
                        v-for="event in progressEvents"
                        :key="event.id"
                        class="rounded-2xl border border-slate-100 bg-slate-50 p-4"
                    >
                        <p class="text-xs uppercase tracking-wide text-slate-400">
                            {{ event.rsvp_count }} confirmadas
                            <span v-if="event.capacity"> • {{ event.capacity }} vagas</span>
                        </p>
                        <div class="mt-2 flex items-center justify-between">
                            <p class="text-sm font-semibold text-slate-900">
                                {{ event.title }}
                            </p>
                            <span class="text-xs font-semibold text-emerald-700">
                                {{ event.progress }}%
                            </span>
                        </div>
                        <div class="mt-3 h-2 w-full rounded-full bg-slate-200">
                            <div
                                class="h-2 rounded-full bg-emerald-500"
                                :style="{ width: `${event.progress}%` }"
                            ></div>
                        </div>
                    </div>
                    <div
                        v-if="!progressEvents.length"
                        class="rounded-2xl border border-slate-100 bg-slate-50 p-4 text-sm text-slate-500"
                    >
                        Nenhum evento com capacidade definida.
                    </div>
                </div>
            </section>
        </div>
        <div class="flex flex-1 flex-col gap-6 overflow-x-auto p-4">
            <section class="rounded-2xl border border-sidebar-border/70 bg-white p-6 shadow-sm">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Agenda</p>
                        <h2 class="text-2xl font-semibold text-slate-900">Calendario de eventos</h2>
                    </div>
                    <div class="flex items-center gap-2">
                        <button
                            type="button"
                            class="rounded-full border border-slate-300 px-3 py-1.5 text-sm font-medium text-slate-600 transition hover:-translate-y-0.5 hover:border-slate-400"
                            @click="goToPreviousMonth"
                        >
                            Anterior
                        </button>
                        <p class="text-sm font-semibold text-slate-900">
                            {{ monthLabel }}
                        </p>
                        <button
                            type="button"
                            class="rounded-full border border-slate-300 px-3 py-1.5 text-sm font-medium text-slate-600 transition hover:-translate-y-0.5 hover:border-slate-400"
                            @click="goToNextMonth"
                        >
                            Proximo
                        </button>
                    </div>
                </div>
                <div class="mt-6 grid gap-6 lg:grid-cols-[1.4fr_0.6fr]">
                    <div>
                        <div class="grid grid-cols-7 gap-2 text-xs font-semibold text-slate-400">
                            <span v-for="day in weekdayLabels" :key="day" class="text-center">
                                {{ day }}
                            </span>
                        </div>
                        <div class="mt-3 grid grid-cols-7 gap-2">
                            <button
                                v-for="day in calendarDays"
                                :key="day.dateKey"
                                type="button"
                                class="flex min-h-[86px] flex-col items-start rounded-xl border border-transparent p-2 text-left transition"
                                :class="[
                                    day.inMonth
                                        ? 'bg-slate-50 text-slate-900 hover:border-slate-300'
                                        : 'bg-slate-100 text-slate-400',
                                    selectedDateKey === day.dateKey
                                        ? 'border-slate-900 bg-white shadow-sm'
                                        : '',
                                ]"
                                @click="selectDate(day.dateKey)"
                            >
                                <span class="text-xs font-semibold">
                                    {{ day.date.getDate() }}
                                </span>
                                <div class="mt-1 space-y-1">
                                    <span
                                        v-for="event in day.events.slice(0, 2)"
                                        :key="event.id"
                                        class="block truncate rounded-full bg-emerald-100 px-2 py-0.5 text-[11px] font-medium text-emerald-800"
                                    >
                                        {{ event.title }}
                                    </span>
                                    <span
                                        v-if="day.events.length > 2"
                                        class="block text-[11px] text-slate-500"
                                    >
                                        +{{ day.events.length - 2 }} eventos
                                    </span>
                                </div>
                            </button>
                        </div>
                    </div>

                    <aside class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
                        <p class="text-xs uppercase tracking-[0.3em] text-slate-400">
                            {{ selectedDateLabel }}
                        </p>
                        <h3 class="mt-2 text-lg font-semibold text-slate-900">
                            Eventos do dia
                        </h3>
                        <div v-if="selectedEvents.length" class="mt-4 space-y-3">
                            <div
                                v-for="event in selectedEvents"
                                :key="event.id"
                                class="rounded-xl border border-slate-200 bg-white p-3 shadow-sm"
                            >
                                <p class="text-sm font-semibold text-slate-900">
                                    {{ event.title }}
                                </p>
                                <p class="text-xs text-slate-500">
                                    {{ event.starts_at || 'Horario nao definido' }}
                                    <span v-if="event.ends_at"> - {{ event.ends_at }}</span>
                                </p>
                                <p v-if="event.description" class="mt-2 text-xs text-slate-600">
                                    {{ event.description }}
                                </p>
                            </div>
                        </div>
                        <p v-else class="mt-4 text-sm text-slate-500">
                            Nenhum evento encontrado para este dia.
                        </p>
                    </aside>
                </div>
            </section>
        </div>
    </AppLayout>
</template>
