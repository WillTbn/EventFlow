<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import FlexRowCenter from '@/layouts/FlexRowCenter.vue';
import { edit, index, update } from '@/routes/admin/eventos';
import { type BreadcrumbItem } from '@/types';

interface EventFormData {
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

interface EventPhotoItem {
    id: number;
    thumb_url: string;
}

const props = defineProps<{
    event: EventFormData;
    photos: EventPhotoItem[];
}>();

const mainPhotoAction = computed(() => `/admin/eventos/${props.event.id}/foto-principal`);
const storyPhotosAction = computed(() => `/admin/eventos/${props.event.id}/fotos`);

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Eventos',
        href: index().url,
    },
    {
        title: 'Editar evento',
        href: edit({ event: props.event.id }).url,
    },
];

const statusOptions = [
    { value: 'draft', label: 'Rascunho' },
    { value: 'published', label: 'Publicado' },
    { value: 'canceled', label: 'Cancelado' },
];
</script>

<template>
    <Head title="Editar evento" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="max-w-2xl">
            <h1 class="text-xl font-semibold">Editar evento</h1>
            <p class="text-sm text-muted-foreground">
                Atualize as informacoes do evento.
            </p>
            {{event.location}}
            <FlexRowCenter>
                <div class="max-w-2xl basis-2/3">
                    <Form
                        :action="update({ event: event.id }).url"
                        method="put"
                        class="mt-6 space-y-6"
                        v-slot="{ errors, processing }"
                    >
                        <div class="grid gap-2">
                            <Label for="title">Titulo</Label>
                            <Input
                                id="title"
                                name="title"
                                required
                                :default-value="event.title ?? ''"
                            />
                            <InputError :message="errors.title" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="description">Descricao</Label>
                            <textarea
                                id="description"
                                name="description"
                                rows="4"
                                class="rounded-md border border-input bg-background px-3 py-2 text-sm shadow-sm"
                                :value="event.description ?? ''"
                            ></textarea>
                            <InputError :message="errors.description" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="location">Local</Label>
                            <Input
                                id="location"
                                name="location"
                                :default-value="event.location ?? ''"
                            />
                            <InputError :message="errors.location" />
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div class="grid gap-2">
                                <Label for="starts_at">Inicio</Label>
                                <Input
                                    id="starts_at"
                                    name="starts_at"
                                    type="datetime-local"
                                    required
                                    :default-value="event.starts_at ?? ''"
                                />
                                <InputError :message="errors.starts_at" />
                            </div>
                            <div class="grid gap-2">
                                <Label for="ends_at">Fim</Label>
                                <Input
                                    id="ends_at"
                                    name="ends_at"
                                    type="datetime-local"
                                    required
                                    :default-value="event.ends_at ?? ''"
                                />
                                <InputError :message="errors.ends_at" />
                            </div>
                        </div>

                        <div class="grid gap-2">
                            <Label for="status">Status</Label>
                            <select
                                id="status"
                                name="status"
                                class="rounded-md border border-input bg-background px-3 py-2 text-sm shadow-sm"
                                :value="event.status"
                            >
                                <option v-for="option in statusOptions" :key="option.value" :value="option.value">
                                    {{ option.label }}
                                </option>
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
                                :default-value="event.capacity ?? ''"
                            />
                            <InputError :message="errors.capacity" />
                        </div>

                        <div class="flex items-center gap-2">
                            <input type="hidden" name="is_public" value="0" />
                            <input
                                id="is_public"
                                name="is_public"
                                type="checkbox"
                                value="1"
                                class="h-4 w-4 rounded border-input"
                                :checked="event.is_public"
                            />
                            <Label for="is_public">Visivel ao publico</Label>
                        </div>
                        <InputError :message="errors.is_public" />

                        <div class="flex items-center gap-3">
                            <Button type="submit" :disabled="processing">Salvar</Button>
                            <Button variant="outline" as-child>
                                <Link :href="index().url">Voltar</Link>
                            </Button>
                        </div>
                    </Form>

                    <div class="mt-10 space-y-6">
                        <div class="rounded-md border border-border p-4">
                            <h2 class="text-lg font-semibold">Foto principal</h2>
                            <p class="text-sm text-muted-foreground">
                                Envie a imagem principal de divulgacao do evento.
                            </p>

                            <div v-if="event.main_photo_url" class="mt-4">
                                <img
                                    :src="event.main_photo_url"
                                    alt="Foto principal do evento"
                                    class="h-32 w-32 rounded object-cover"
                                />
                            </div>

                            <Form
                                :action="mainPhotoAction"
                                method="post"
                                enctype="multipart/form-data"
                                class="mt-4 space-y-4"
                                v-slot="{ errors, processing }"
                            >
                                <div class="grid gap-2">
                                    <Label for="main_photo">Imagem principal</Label>
                                    <Input
                                        id="main_photo"
                                        name="main_photo"
                                        type="file"
                                        accept="image/jpeg,image/png,image/webp"
                                        required
                                    />
                                    <InputError :message="errors.main_photo" />
                                </div>

                                <Button type="submit" :disabled="processing">Atualizar foto</Button>
                            </Form>
                        </div>

                        <div class="rounded-md border border-border p-4">
                            <h2 class="text-lg font-semibold">Fotos do acontecido</h2>
                            <p class="text-sm text-muted-foreground">
                                Essas imagens registram o evento e so podem ser enviadas apos a realizacao.
                            </p>

                            <Form
                                :action="storyPhotosAction"
                                method="post"
                                enctype="multipart/form-data"
                                class="mt-4 space-y-4"
                                v-slot="{ errors, processing }"
                            >
                                <div class="grid gap-2">
                                    <Label for="story_photos">Enviar fotos</Label>
                                    <Input
                                        id="story_photos"
                                        name="photos[]"
                                        type="file"
                                        multiple
                                        accept="image/jpeg,image/png,image/webp"
                                        :disabled="!event.can_add_photos"
                                    />
                                    <InputError :message="errors.photos || errors['photos.0']" />
                                </div>

                                <Button type="submit" :disabled="processing || !event.can_add_photos">
                                    Enviar fotos
                                </Button>
                                <p v-if="!event.can_add_photos" class="text-xs text-muted-foreground">
                                    As fotos do acontecido so podem ser enviadas apos o evento terminar.
                                </p>
                            </Form>

                            <div v-if="photos.length" class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-3">
                                <img
                                    v-for="photo in photos"
                                    :key="photo.id"
                                    :src="photo.thumb_url"
                                    alt="Foto do evento"
                                    class="h-24 w-full rounded object-cover"
                                />
                            </div>
                            <p v-else class="mt-4 text-xs text-muted-foreground">
                                Nenhuma foto do acontecido cadastrada.
                            </p>
                        </div>
                    </div>
                </div>
            </FlexRowCenter>
        </div>
    </AppLayout>
</template>
