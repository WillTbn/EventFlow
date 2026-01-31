<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import { computed, onBeforeUnmount, ref } from 'vue';

import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { useTenantUrl } from '@/composables/useTenantUrl';
import AppLayout from '@/layouts/AppLayout.vue';
import { edit, fotoPrincipal, fotos, index, update } from '@/routes/admin/eventos';
import { type BreadcrumbItem } from '@/types';

interface EventPayload {
    id: number;
    hash_id: string;
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

interface UploadPreview {
    id: string;
    url: string;
    file: File;
}

const props = defineProps<{
    event: EventPayload;
    photos: PhotoPayload[];
}>();

const { withTenantUrl } = useTenantUrl();


const mainPhotoInput = ref<HTMLInputElement | null>(null);
const mainPhotoPreviewUrl = ref<string | null>(null);
const mainPhotoFileName = ref<string | null>(null);

const galleryInput = ref<HTMLInputElement | null>(null);
const galleryPreviews = ref<UploadPreview[]>([]);

const breadcrumbs = computed<BreadcrumbItem[]>(() => [
    {
        title: 'Eventos',
        href: withTenantUrl(index()),
    },
    {
        title: 'Editar evento',
        href: withTenantUrl(edit({ event: props.event.hash_id })),
    },
]);

const handleMainPhotoChange = (event: Event) => {
    const input = event.target as HTMLInputElement;
    const file = input.files?.[0] ?? null;

    if (mainPhotoPreviewUrl.value) {
        URL.revokeObjectURL(mainPhotoPreviewUrl.value);
        mainPhotoPreviewUrl.value = null;
    }

    if (!file) {
        mainPhotoFileName.value = null;
        return;
    }

    mainPhotoPreviewUrl.value = URL.createObjectURL(file);
    mainPhotoFileName.value = file.name;
};

const clearMainPhoto = () => {
    if (mainPhotoPreviewUrl.value) {
        URL.revokeObjectURL(mainPhotoPreviewUrl.value);
    }

    if (mainPhotoInput.value) {
        mainPhotoInput.value.value = '';
    }

    mainPhotoPreviewUrl.value = null;
    mainPhotoFileName.value = null;
};

const syncGalleryInput = (files: File[]) => {
    if (!galleryInput.value) return;

    const dataTransfer = new DataTransfer();
    files.forEach((file) => dataTransfer.items.add(file));
    galleryInput.value.files = dataTransfer.files;
};

const handleGalleryChange = (event: Event) => {
    const input = event.target as HTMLInputElement;
    const files = Array.from(input.files ?? []);

    galleryPreviews.value.forEach((preview) => URL.revokeObjectURL(preview.url));
    galleryPreviews.value = files.map((file, index) => ({
        id: `${file.name}-${file.size}-${index}`,
        url: URL.createObjectURL(file),
        file,
    }));
};

const removeGalleryPreview = (previewId: string) => {
    const remaining = galleryPreviews.value.filter((preview) => preview.id !== previewId);
    const removed = galleryPreviews.value.find((preview) => preview.id === previewId);

    if (removed) {
        URL.revokeObjectURL(removed.url);
    }

    galleryPreviews.value = remaining;
    syncGalleryInput(remaining.map((preview) => preview.file));
};

const clearGalleryPreviews = () => {
    galleryPreviews.value.forEach((preview) => URL.revokeObjectURL(preview.url));
    galleryPreviews.value = [];
    syncGalleryInput([]);
};

onBeforeUnmount(() => {
    if (mainPhotoPreviewUrl.value) {
        URL.revokeObjectURL(mainPhotoPreviewUrl.value);
    }

    galleryPreviews.value.forEach((preview) => URL.revokeObjectURL(preview.url));
});
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
                    :action="withTenantUrl(update({ event: event.hash_id }).url)"
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

                    <div class="grid gap-2">
                        <Label>Visibilidade</Label>
                        <div class="flex flex-wrap items-center gap-4">
                            <label class="inline-flex items-center gap-2">
                                <input
                                    class="h-4 w-4 rounded-full border border-input text-primary"
                                    type="radio"
                                    name="is_public"
                                    value="0"
                                    :checked="!event.is_public"
                                />
                                <span class="text-sm">Nao publico</span>
                            </label>
                            <label class="inline-flex items-center gap-2">
                                <input
                                    class="h-4 w-4 rounded-full border border-input text-primary"
                                    type="radio"
                                    name="is_public"
                                    value="1"
                                    :checked="Boolean(Number(event.is_public))"
                                />
                                <span class="text-sm">Publico</span>
                            </label>
                        </div>
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
                <CardTitle>Foto principal</CardTitle>
            </CardHeader>
            <CardContent>
                <div class="flex flex-wrap items-center gap-4">
                    <div
                        class="flex h-24 w-24 items-center justify-center overflow-hidden rounded-xl border border-border bg-muted"
                    >
                        <img
                            v-if="mainPhotoPreviewUrl || event.main_photo_url"
                            :src="mainPhotoPreviewUrl || event.main_photo_url || ''"
                            alt="Foto principal"
                            class="h-full w-full object-cover"
                        />
                        <span v-else class="text-xs text-muted-foreground">Sem foto</span>
                    </div>
                    <Form
                        :action="withTenantUrl(fotoPrincipal({ event: event.hash_id }).url)"
                        method="post"
                        multipart
                        v-slot="{ errors, processing }"
                        class="flex flex-1 flex-col gap-3"
                    >
                        <div class="grid gap-2">
                            <Label for="main_photo_update">Atualizar foto principal</Label>
                            <Input
                                id="main_photo_update"
                                ref="mainPhotoInput"
                                name="main_photo"
                                type="file"
                                accept="image/png,image/jpeg,image/webp"
                                @change="handleMainPhotoChange"
                            />
                            <InputError :message="errors.main_photo" />
                        </div>
                        <div class="flex flex-wrap items-center gap-3 text-xs text-muted-foreground">
                            <Button size="sm" :disabled="processing">Salvar foto</Button>
                            <button
                                v-if="mainPhotoPreviewUrl"
                                type="button"
                                class="text-xs font-medium text-rose-600 transition hover:text-rose-700"
                                @click="clearMainPhoto"
                            >
                                Remover preview
                            </button>
                            <span>Formatos JPG, PNG ou WebP ate 5MB.</span>
                        </div>
                    </Form>
                </div>
            </CardContent>
        </Card>

        <Card class="mt-6">
            <CardHeader>
                <CardTitle>Fotos secundarias</CardTitle>
            </CardHeader>
            <CardContent class="space-y-6">
                <div v-if="!event.can_add_photos" class="text-sm text-muted-foreground">
                    Voce so pode adicionar fotos apos o evento terminar.
                </div>
                <Form
                    v-else
                    :action="withTenantUrl(fotos({ event: event.hash_id }).url)"
                    method="post"
                    multipart
                    v-slot="{ errors, processing }"
                    class="space-y-4"
                >
                    <div class="grid gap-2">
                        <Label for="photos_upload">Adicionar fotos</Label>
                        <Input
                            id="photos_upload"
                            ref="galleryInput"
                            name="photos[]"
                            type="file"
                            multiple
                            accept="image/png,image/jpeg,image/webp"
                            @change="handleGalleryChange"
                        />
                        <InputError :message="errors.photos" />
                        <InputError :message="errors['photos.0']" />
                    </div>

                    <div v-if="galleryPreviews.length" class="space-y-3">
                        <div class="flex items-center justify-between text-xs text-muted-foreground">
                            <span>Preview das fotos selecionadas</span>
                            <button
                                type="button"
                                class="text-xs font-medium text-rose-600 transition hover:text-rose-700"
                                @click="clearGalleryPreviews"
                            >
                                Limpar selecao
                            </button>
                        </div>
                        <div class="grid gap-4 sm:grid-cols-3">
                            <div
                                v-for="preview in galleryPreviews"
                                :key="preview.id"
                                class="group relative overflow-hidden rounded-lg border"
                            >
                                <img :src="preview.url" alt="Preview da foto" class="h-32 w-full object-cover" />
                                <button
                                    type="button"
                                    class="absolute right-2 top-2 rounded-full bg-white/90 px-2 py-1 text-[10px] font-medium text-rose-600 shadow-sm transition hover:bg-white"
                                    @click="removeGalleryPreview(preview.id)"
                                >
                                    Remover
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center gap-3">
                        <Button size="sm" :disabled="processing || !galleryPreviews.length">
                            Enviar fotos
                        </Button>
                        <span class="text-xs text-muted-foreground">JPG, PNG ou WebP ate 5MB.</span>
                    </div>
                </Form>

                <div>
                    <h3 class="text-sm font-semibold text-foreground">Fotos publicadas</h3>
                    <div v-if="!photos.length" class="mt-3 text-sm text-muted-foreground">
                        Nenhuma foto cadastrada ainda.
                    </div>

                    <div v-else class="mt-3 grid gap-4 sm:grid-cols-3">
                        <div
                            v-for="photo in photos"
                            :key="photo.id"
                            class="overflow-hidden rounded-lg border"
                        >
                            <img :src="photo.thumb_url" alt="Foto do evento" />
                        </div>
                    </div>
                </div>
            </CardContent>
        </Card>
    </AppLayout>
</template>
