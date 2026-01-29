<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import { Textarea } from '@/components/ui/textarea';
import { useTenantUrl } from '@/composables/useTenantUrl';
import AppLayout from '@/layouts/AppLayout.vue';
import { edit, index, update } from '@/routes/admin/eventos';
import { type BreadcrumbItem } from '@/types';

interface EventPayload {
    id: number;
    title: string;
    description: string | null;
    location: string | null;
    starts_at: string | null;
    ends_at: string | null;
    status: string;
    is_public: boolean;
    capacity: number | null;
    main_photo_url: string | null;
    can_add_photos: boolean;
}

interface PhotoPayload {
    id: number;
    thumb_url: string;
}

const props = defineProps<{
    event: EventPayload;
    photos: PhotoPayload[];
}>();

const { withTenantUrl } = useTenantUrl();

const breadcrumbs = computed<BreadcrumbItem[]>(() => [
    {
        title: 'Eventos',
        href: withTenantUrl(index()),
    },
    {
        title: 'Editar evento',
        href: withTenantUrl(edit({ event: props.event.id })),
    },
]);
</script>

<template>
    <Head title="Editar evento" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <Card>
            <CardHeader>
                <CardTitle>Editar evento</CardTitle>
            </CardHeader>
            <CardContent>
                <Form
                    :action="withTenantUrl(update({ event: event.id }).url)"
                    method="put"
                    v-slot="{ errors, processing }"
                    class="space-y-6"
                >
                    <div class="grid gap-2">
                        <Label for="title">Titulo</Label>
                        <Input
                            id="title"
                            name="title"
                            :default-value="event.title"
                        />
                        <InputError :message="errors.title" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="description">Descricao</Label>
                        <Textarea
                            id="description"
                            name="description"
                            rows="4"
                            :default-value="event.description || ''"
                        />
                        <InputError :message="errors.description" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="location">Local</Label>
                        <Input
                            id="location"
                            name="location"
                            :default-value="event.location || ''"
                        />
                        <InputError :message="errors.location" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="starts_at">Inicio</Label>
                        <Input
                            id="starts_at"
                            name="starts_at"
                            type="datetime-local"
                            :default-value="event.starts_at || ''"
                        />
                        <InputError :message="errors.starts_at" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="ends_at">Fim</Label>
                        <Input
                            id="ends_at"
                            name="ends_at"
                            type="datetime-local"
                            :default-value="event.ends_at || ''"
                        />
                        <InputError :message="errors.ends_at" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="status">Status</Label>
                        <select
                            id="status"
                            name="status"
                            class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm"
                            :value="event.status"
                        >
                            <option value="draft">Rascunho</option>
                            <option value="published">Publicado</option>
                            <option value="canceled">Cancelado</option>
                        </select>
                        <InputError :message="errors.status" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="capacity">Capacidade</Label>
                        <Input
                            id="capacity"
                            name="capacity"
                            type="number"
                            min="1"
                            :default-value="event.capacity || ''"
                        />
                        <InputError :message="errors.capacity" />
                    </div>

                    <div class="flex items-center gap-4">
                        <Switch
                            id="is_public"
                            name="is_public"
                            :checked="event.is_public"
                            value="1"
                        />
                        <Label for="is_public">Evento publico</Label>
                        <InputError :message="errors.is_public" />
                    </div>

                    <div class="flex items-center gap-4">
                        <Button :disabled="processing">Salvar</Button>
                        <Link class="text-sm text-muted-foreground" :href="withTenantUrl(index())">
                            Voltar
                        </Link>
                    </div>
                </Form>
            </CardContent>
        </Card>

        <Card class="mt-6">
            <CardHeader>
                <CardTitle>Fotos</CardTitle>
            </CardHeader>
            <CardContent>
                <div v-if="!photos.length" class="text-sm text-muted-foreground">
                    Nenhuma foto cadastrada ainda.
                </div>

                <div v-else class="grid gap-4 sm:grid-cols-3">
                    <div
                        v-for="photo in photos"
                        :key="photo.id"
                        class="overflow-hidden rounded-lg border"
                    >
                        <img :src="photo.thumb_url" alt="Foto do evento" />
                    </div>
                </div>
            </CardContent>
        </Card>
    </AppLayout>
</template>
