<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';

import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useTenantUrl } from '@/composables/useTenantUrl';
import AppLayout from '@/layouts/AppLayout.vue';
import { edit, update } from '@/routes/admin/workspace';
import { type BreadcrumbItem } from '@/types';

interface WorkspacePayload {
    name: string | null;
    slug: string | null;
    logo_url: string | null;
}

defineProps<{
    workspace: WorkspacePayload;
}>();

const { withTenantUrl, dashboardUrl } = useTenantUrl();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Workspace',
        href: withTenantUrl(edit()),
    },
];
</script>

<template>
    <Head title="Workspace" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <Card class="max-w-3xl">
            <CardHeader>
                <CardTitle>Configuracao do workspace</CardTitle>
            </CardHeader>
            <CardContent>
                <Form
                    :action="withTenantUrl(update().url)"
                    method="put"
                    v-slot="{ errors, processing }"
                    class="space-y-6"
                >
                    <div class="grid gap-2">
                        <Label for="name">Nome do workspace</Label>
                        <Input id="name" name="name" :default-value="workspace.name || ''" />
                        <InputError :message="errors.name" />
                        <p class="text-xs text-muted-foreground">
                            O slug publico e atualizado automaticamente com base no nome.
                        </p>
                        <p v-if="workspace.slug" class="text-xs text-muted-foreground">
                            Slug atual: <span class="font-medium text-foreground">{{ workspace.slug }}</span>
                        </p>
                    </div>

                    <div class="grid gap-3">
                        <Label for="logo">Logo da organizacao</Label>
                        <div class="flex flex-wrap items-center gap-4">
                            <div
                                class="flex h-16 w-16 items-center justify-center overflow-hidden rounded-2xl border border-border bg-muted"
                            >
                                <img
                                    v-if="workspace.logo_url"
                                    :src="workspace.logo_url"
                                    alt="Logo do workspace"
                                    class="h-full w-full object-contain"
                                />
                                <span v-else class="text-xs text-muted-foreground">Sem logo</span>
                            </div>
                            <div class="flex-1">
                                <Input id="logo" name="logo" type="file" />
                                <InputError :message="errors.logo" />
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-4">
                        <Button :disabled="processing">Salvar</Button>
                        <Link class="text-sm text-muted-foreground" :href="dashboardUrl">
                            Cancelar
                        </Link>
                    </div>
                </Form>
            </CardContent>
        </Card>
    </AppLayout>
</template>
