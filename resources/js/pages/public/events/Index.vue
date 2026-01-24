<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';

import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';
import { index, show } from '@/routes/eventos';
import { type BreadcrumbItem } from '@/types';

interface EventSummary {
    id: number;
    title: string;
    slug: string;
    location: string | null;
    starts_at: string | null;
    ends_at: string | null;
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface Pagination<T> {
    data: T[];
    links: PaginationLink[];
}

defineProps<{
    events: Pagination<EventSummary>;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Eventos',
        href: index().url,
    },
];
</script>

<template>
    <Head title="Eventos" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6">
            <div>
                <h1 class="text-xl font-semibold">Eventos</h1>
                <p class="text-sm text-muted-foreground">
                    Descubra os proximos eventos disponiveis.
                </p>
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                <Card v-for="event in events.data" :key="event.id">
                    <CardHeader>
                        <CardTitle>
                            <Link
                                :href="show({ event: event.slug }).url"
                                class="hover:underline"
                            >
                                {{ event.title }}
                            </Link>
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-1 text-sm text-muted-foreground">
                        <p v-if="event.location">Local: {{ event.location }}</p>
                        <p>Inicio: {{ event.starts_at }}</p>
                        <p>Fim: {{ event.ends_at }}</p>
                    </CardContent>
                </Card>
            </div>

            <div
                v-if="events.links.length > 1"
                class="flex flex-wrap gap-2"
            >
                <Link
                    v-for="link in events.links"
                    :key="link.label"
                    :href="link.url || ''"
                    class="rounded border px-3 py-1 text-sm"
                    :class="[
                        link.active
                            ? 'border-primary text-primary'
                            : 'border-border text-muted-foreground',
                        link.url ? 'hover:border-primary/60' : 'pointer-events-none opacity-60',
                    ]"
                    v-html="link.label"
                />
            </div>
        </div>
    </AppLayout>
</template>
