<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';

import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';
import { index } from '@/routes/eventos';
import { type BreadcrumbItem } from '@/types';

interface EventDetails {
    id: number;
    title: string;
    description: string | null;
    location: string | null;
    starts_at: string | null;
    ends_at: string | null;
    status: string;
    is_public: boolean;
}

defineProps<{
    event: EventDetails;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Eventos',
        href: index().url,
    },
];
</script>

<template>
    <Head :title="event.title" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="max-w-3xl space-y-6">
            <div>
                <p class="text-sm text-muted-foreground">
                    <Link :href="index().url" class="hover:underline">Eventos</Link>
                </p>
                <h1 class="text-2xl font-semibold">{{ event.title }}</h1>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>Detalhes</CardTitle>
                </CardHeader>
                <CardContent class="space-y-2 text-sm">
                    <p v-if="event.location">Local: {{ event.location }}</p>
                    <p>Inicio: {{ event.starts_at }}</p>
                    <p>Fim: {{ event.ends_at }}</p>
                    <p>Status: {{ event.status }}</p>
                    <p>Visivel ao publico: {{ event.is_public ? 'Sim' : 'Nao' }}</p>
                </CardContent>
            </Card>

            <Card v-if="event.description">
                <CardHeader>
                    <CardTitle>Descricao</CardTitle>
                </CardHeader>
                <CardContent class="text-sm text-muted-foreground">
                    {{ event.description }}
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
