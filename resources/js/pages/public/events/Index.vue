<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';

import { Card, CardContent, CardHeader, CardAction } from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';
import { index, show } from '@/routes/eventos';
import { type BreadcrumbItem } from '@/types';

interface EventSummary {
    id: number;
    title: string;
    description:string;
    slug: string;
    location: string | null;
    starts_at: string | null;
    ends_at: string | null;
    main_photo_medium_path: string;
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

            <div class="grid gap-4 md:grid-cols-4">
                <Card v-for="event in events.data" :key="event.id" class="pt-0">
                    <div
                        class="relative  z-20 aspect-video w-full object-cover rounded-t-md"
                        :style="'background:url('+event.main_photo_medium_path+');background-size: cover;background-repeat: no-repeat;background-position: center;'"
                    >
                        <p class="font-bold subpixel-antialiased text-white absolute bottom-0 left-0 text-2xl ml-3"> {{ event.title }}</p>
                    </div>
                    <!-- <img
                        :src="event.main_photo_medium_path"
                        alt="Event cover"
                        className="relative z-20 aspect-video w-full object-cover brightness-60 grayscale dark:brightness-40 rounded-t-md"
                    /> -->
                    <CardHeader>
                        {{ event.description }}
                    </CardHeader>
                    <CardContent class="space-y-1 text-sm text-muted-foreground">
                        <p v-if="event.location">Local: {{ event.location }}</p>
                        <p>Inicio: {{ event.starts_at }}</p>
                        <p>Fim: {{ event.ends_at }}</p>
                    </CardContent>
                    <CardAction>
                        <Link
                            :href="show({ event: event.slug }).url"
                            class=""
                        >
                            ver mais
                        </Link>
                    </CardAction>
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
                >
                    <span v-html="link.label"></span>
                </Link>
            </div>
        </div>
    </AppLayout>
</template>
