<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';

import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';
import { create, destroy, edit, index } from '@/routes/admin/eventos';
import { type BreadcrumbItem } from '@/types';

interface EventListItem {
    id: number;
    title: string;
    status: string;
    is_public: boolean;
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
    events: Pagination<EventListItem>;
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
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-xl font-semibold">Eventos</h1>
                <p class="text-sm text-muted-foreground">
                    Gerencie seus eventos publicados e rascunhos.
                </p>
            </div>

            <Button as-child>
                <Link :href="create().url">Novo evento</Link>
            </Button>
        </div>

        <Card class="mt-6">
            <CardHeader>
                <CardTitle>Lista de eventos</CardTitle>
            </CardHeader>
            <CardContent>
                <div v-if="events.data.length === 0" class="text-sm text-muted-foreground">
                    Nenhum evento cadastrado ainda.
                </div>

                <div v-else class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="border-b text-left">
                            <tr>
                                <th class="px-3 py-2">Titulo</th>
                                <th class="px-3 py-2">Status</th>
                                <th class="px-3 py-2">Visivel</th>
                                <th class="px-3 py-2">Inicio</th>
                                <th class="px-3 py-2">Fim</th>
                                <th class="px-3 py-2 text-right">Acoes</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="event in events.data"
                                :key="event.id"
                                class="border-b last:border-0"
                            >
                                <td class="px-3 py-2 font-medium">
                                    {{ event.title }}
                                </td>
                                <td class="px-3 py-2 capitalize">
                                    {{ event.status }}
                                </td>
                                <td class="px-3 py-2">
                                    {{ event.is_public ? 'Sim' : 'Nao' }}
                                </td>
                                <td class="px-3 py-2">
                                    {{ event.starts_at }}
                                </td>
                                <td class="px-3 py-2">
                                    {{ event.ends_at }}
                                </td>
                                <td class="px-3 py-2 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <Button size="sm" variant="outline" as-child>
                                            <Link :href="edit({ event: event.id }).url">
                                                Editar
                                            </Link>
                                        </Button>
                                        <Form
                                            :action="destroy({ event: event.id }).url"
                                            method="delete"
                                            v-slot="{ processing, errors }"
                                        >
                                            <Button
                                                size="sm"
                                                variant="destructive"
                                                :disabled="processing"
                                            >
                                                Excluir
                                            </Button>
                                            <InputError
                                                v-if="errors.event"
                                                class="mt-2"
                                                :message="errors.event"
                                            />
                                        </Form>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div
                    v-if="events.links.length > 1"
                    class="mt-4 flex flex-wrap gap-2"
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
            </CardContent>
        </Card>
    </AppLayout>
</template>
