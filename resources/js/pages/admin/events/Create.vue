<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import { onBeforeUnmount, ref } from 'vue';

import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { useTenantUrl } from '@/composables/useTenantUrl';
import AppLayout from '@/layouts/AppLayout.vue';
import { create, index, store } from '@/routes/admin/eventos';
import { type BreadcrumbItem } from '@/types';

defineProps<{
    eventQuota: {
        plan: string | null;
        events_this_month: number;
        events_limit: number | null;
        can_create_event: boolean;
    };
}>();

const { withTenantUrl } = useTenantUrl();

const mainPhotoInput = ref<HTMLInputElement | null>(null);
const mainPhotoPreviewUrl = ref<string | null>(null);
const mainPhotoFileName = ref<string | null>(null);

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Eventos',
        href: withTenantUrl(index()),
    },
    {
        title: 'Novo evento',
        href: withTenantUrl(create()),
    },
];

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

onBeforeUnmount(() => {
    if (mainPhotoPreviewUrl.value) {
        URL.revokeObjectURL(mainPhotoPreviewUrl.value);
    }
});
</script>

<template>
    <Head title="Novo evento" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <Card>
            <CardHeader>
                <CardTitle>Novo evento</CardTitle>
            </CardHeader>
            <CardContent>
                <div
                    v-if="!eventQuota.can_create_event && eventQuota.events_limit !== null"
                    class="mb-4 rounded border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900"
                >
                    <div class="flex flex-wrap items-center gap-2">
                        <span>
                            Limite do plano {{ eventQuota.plan ?? 'Free' }} atingido
                            ({{ eventQuota.events_limit }} evento(s)/mes).
                        </span>
                        <Link class="underline" href="/pricing">Fazer upgrade</Link>
                    </div>
                </div>

                <Form
                    :action="withTenantUrl(store().url)"
                    method="post"
                    multipart
                    v-slot="{ errors, processing }"
                    class="space-y-6"
                >
                    <InputError v-if="errors.event" :message="errors.event" />

                    <div class="grid gap-2">
                        <Label for="title">Titulo</Label>
                        <Input id="title" name="title" placeholder="Nome do evento" />
                        <InputError :message="errors.title" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="description">Descricao</Label>
                        <Textarea
                            id="description"
                            name="description"
                            rows="4"
                            placeholder="Detalhes do evento"
                        />
                        <InputError :message="errors.description" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="location">Local</Label>
                        <Input id="location" name="location" placeholder="Cidade" />
                        <InputError :message="errors.location" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="starts_at">Inicio</Label>
                        <Input id="starts_at" name="starts_at" type="datetime-local" />
                        <InputError :message="errors.starts_at" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="ends_at">Fim</Label>
                        <Input id="ends_at" name="ends_at" type="datetime-local" />
                        <InputError :message="errors.ends_at" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="status">Status</Label>
                        <select
                            id="status"
                            name="status"
                            class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm"
                        >
                            <option value="draft">Rascunho</option>
                            <option value="published">Publicado</option>
                            <option value="canceled">Cancelado</option>
                        </select>
                        <InputError :message="errors.status" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="capacity">Capacidade</Label>
                        <Input id="capacity" name="capacity" type="number" min="1" />
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
                                    checked
                                />
                                <span class="text-sm">Nao publico</span>
                            </label>
                            <label class="inline-flex items-center gap-2">
                                <input
                                    class="h-4 w-4 rounded-full border border-input text-primary"
                                    type="radio"
                                    name="is_public"
                                    value="1"
                                />
                                <span class="text-sm">Publico</span>
                            </label>
                        </div>
                        <InputError :message="errors.is_public" />
                    </div>

                    <div class="grid gap-3">
                        <Label for="main_photo">Foto principal</Label>
                        <Input
                            id="main_photo"
                            ref="mainPhotoInput"
                            name="main_photo"
                            type="file"
                            accept="image/png,image/jpeg,image/webp"
                            @change="handleMainPhotoChange"
                        />
                        <InputError :message="errors.main_photo" />
                        <div v-if="mainPhotoPreviewUrl" class="flex flex-wrap items-center gap-4">
                            <div
                                class="flex h-24 w-24 items-center justify-center overflow-hidden rounded-xl border border-border bg-muted"
                            >
                                <img
                                    :src="mainPhotoPreviewUrl"
                                    :alt="mainPhotoFileName || 'Preview da foto principal'"
                                    class="h-full w-full object-cover"
                                />
                            </div>
                            <div class="space-y-2 text-sm text-muted-foreground">
                                <p class="font-medium text-foreground">Preview selecionado</p>
                                <p v-if="mainPhotoFileName">{{ mainPhotoFileName }}</p>
                                <button
                                    type="button"
                                    class="text-xs font-medium text-rose-600 transition hover:text-rose-700"
                                    @click="clearMainPhoto"
                                >
                                    Remover imagem
                                </button>
                            </div>
                        </div>
                        <p v-else class="text-xs text-muted-foreground">
                            Selecione uma imagem JPG, PNG ou WebP ate 5MB.
                        </p>
                    </div>

                    <div class="flex items-center gap-4">
                        <Button :disabled="processing || !eventQuota.can_create_event">
                            Salvar
                        </Button>
                        <Link class="text-sm text-muted-foreground" :href="withTenantUrl(index())">
                            Cancelar
                        </Link>
                    </div>
                </Form>
            </CardContent>
        </Card>
    </AppLayout>
</template>
