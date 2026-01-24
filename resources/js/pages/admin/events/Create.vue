<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';

import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import { create, index, store } from '@/routes/admin/eventos';
import { type BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Eventos',
        href: index().url,
    },
    {
        title: 'Novo evento',
        href: create().url,
    },
];

const statusOptions = [
    { value: 'draft', label: 'Rascunho' },
    { value: 'published', label: 'Publicado' },
    { value: 'canceled', label: 'Cancelado' },
];
</script>

<template>
    <Head title="Novo evento" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="max-w-2xl">
            <h1 class="text-xl font-semibold">Novo evento</h1>
            <p class="text-sm text-muted-foreground">
                Cadastre os detalhes do seu evento.
            </p>

            <Form
                :action="store().url"
                method="post"
                class="mt-6 space-y-6"
                v-slot="{ errors, processing }"
            >
                <div class="grid gap-2">
                    <Label for="title">Titulo</Label>
                    <Input id="title" name="title" required />
                    <InputError :message="errors.title" />
                </div>

                <div class="grid gap-2">
                    <Label for="description">Descricao</Label>
                    <textarea
                        id="description"
                        name="description"
                        rows="4"
                        class="rounded-md border border-input bg-background px-3 py-2 text-sm shadow-sm"
                    ></textarea>
                    <InputError :message="errors.description" />
                </div>

                <div class="grid gap-2">
                    <Label for="location">Local</Label>
                    <Input id="location" name="location" />
                    <InputError :message="errors.location" />
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="grid gap-2">
                        <Label for="starts_at">Inicio</Label>
                        <Input id="starts_at" name="starts_at" type="datetime-local" required />
                        <InputError :message="errors.starts_at" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="ends_at">Fim</Label>
                        <Input id="ends_at" name="ends_at" type="datetime-local" required />
                        <InputError :message="errors.ends_at" />
                    </div>
                </div>

                <div class="grid gap-2">
                    <Label for="status">Status</Label>
                    <select
                        id="status"
                        name="status"
                        class="rounded-md border border-input bg-background px-3 py-2 text-sm shadow-sm"
                        :value="'draft'"
                    >
                        <option v-for="option in statusOptions" :key="option.value" :value="option.value">
                            {{ option.label }}
                        </option>
                    </select>
                    <InputError :message="errors.status" />
                </div>

                <div class="grid gap-2">
                    <Label for="capacity">Capacidade</Label>
                    <Input id="capacity" name="capacity" type="number" min="1" />
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
                    />
                    <Label for="is_public">Visivel ao publico</Label>
                </div>
                <InputError :message="errors.is_public" />

                <div class="flex items-center gap-3">
                    <Button type="submit" :disabled="processing">Salvar</Button>
                    <Button variant="outline" as-child>
                        <Link :href="index().url">Cancelar</Link>
                    </Button>
                </div>
            </Form>
        </div>
    </AppLayout>
</template>
