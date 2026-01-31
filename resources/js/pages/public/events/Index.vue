<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';

import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { useTenantUrl } from '@/composables/useTenantUrl';
import { index, show } from '@/routes/eventos';

interface EventListItem {
    id: number;
    title: string;
    description: string | null;
    hash_id: string;
    location: string | null;
    starts_at: string | null;
    ends_at: string | null;
    main_photo_medium_path: string | null;
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
    events: Pagination<EventListItem>;
}>();

const { withTenantUrl } = useTenantUrl();
</script>

<template>
    <Head title="Eventos" />

    <section class="space-y-6">
        <header class="space-y-1">
            <h1 class="text-2xl font-semibold">Eventos publicos</h1>
            <p class="text-sm text-muted-foreground">
                Explore eventos publicados do seu workspace.
            </p>
        </header>

        <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
            <Card v-for="event in events.data" :key="event.id">
                <CardHeader>
                    <CardTitle class="text-lg">{{ event.title }}</CardTitle>
                </CardHeader>
                <CardContent class="space-y-2">
                    <p class="text-sm text-muted-foreground">
                        {{ event.description }}
                    </p>
                    <p class="text-xs text-muted-foreground">
                        {{ event.location }}
                    </p>
                    <div class="flex items-center justify-between text-xs text-muted-foreground">
                        <span>{{ event.starts_at }}</span>
                        <span>{{ event.ends_at }}</span>
                    </div>
                    <Link
                        class="text-sm text-primary"
                        :href="withTenantUrl(show({ event: event.hash_id }))"
                    >
                        Ver detalhes
                    </Link>
                </CardContent>
            </Card>
        </div>

        <div v-if="events.links.length > 1" class="flex flex-wrap gap-2">
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

        <Link class="text-sm text-muted-foreground" :href="withTenantUrl(index())">
            Voltar
        </Link>
    </section>
</template>
