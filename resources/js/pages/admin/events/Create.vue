<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';

import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import { Textarea } from '@/components/ui/textarea';
import { useTenantUrl } from '@/composables/useTenantUrl';
import AppLayout from '@/layouts/AppLayout.vue';
import { create, index, store } from '@/routes/admin/eventos';
import { type BreadcrumbItem } from '@/types';

const { withTenantUrl } = useTenantUrl();

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
</script>

<template>
    <Head title="Novo evento" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <Card>
            <CardHeader>
                <CardTitle>Novo evento</CardTitle>
            </CardHeader>
            <CardContent>
                <Form
                    :action="withTenantUrl(store().url)"
                    method="post"
                    multipart
                    v-slot="{ errors, processing }"
                    class="space-y-6"
                >
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

                    <div class="flex items-center gap-4">
                        <Switch id="is_public" name="is_public" value="1" />
                        <Label for="is_public">Evento publico</Label>
                        <InputError :message="errors.is_public" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="main_photo">Foto principal</Label>
                        <Input id="main_photo" name="main_photo" type="file" />
                        <InputError :message="errors.main_photo" />
                    </div>

                    <div class="flex items-center gap-4">
                        <Button :disabled="processing">Salvar</Button>
                        <Link class="text-sm text-muted-foreground" :href="withTenantUrl(index())">
                            Cancelar
                        </Link>
                    </div>
                </Form>
            </CardContent>
        </Card>
    </AppLayout>
</template>
