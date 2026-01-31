<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import { onBeforeUnmount, ref } from 'vue';

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

const logoInput = ref<HTMLInputElement | null>(null);
const logoPreviewUrl = ref<string | null>(null);
const logoFileName = ref<string | null>(null);

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Workspace',
        href: withTenantUrl(edit()),
    },
];

const handleLogoChange = (event: Event) => {
    const input = event.target as HTMLInputElement;
    const file = input.files?.[0] ?? null;

    if (logoPreviewUrl.value) {
        URL.revokeObjectURL(logoPreviewUrl.value);
        logoPreviewUrl.value = null;
    }

    if (!file) {
        logoFileName.value = null;
        return;
    }

    logoPreviewUrl.value = URL.createObjectURL(file);
    logoFileName.value = file.name;
};

const clearLogo = () => {
    if (logoPreviewUrl.value) {
        URL.revokeObjectURL(logoPreviewUrl.value);
    }

    if (logoInput.value) {
        logoInput.value.value = '';
    }

    logoPreviewUrl.value = null;
    logoFileName.value = null;
};

onBeforeUnmount(() => {
    if (logoPreviewUrl.value) {
        URL.revokeObjectURL(logoPreviewUrl.value);
    }
});
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
                    multipart
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
                                    v-if="logoPreviewUrl || workspace.logo_url"
                                    :src="logoPreviewUrl || workspace.logo_url || ''"
                                    alt="Logo do workspace"
                                    class="h-full w-full object-contain"
                                />
                                <span v-else class="text-xs text-muted-foreground">Sem logo</span>
                            </div>
                            <div class="flex-1">
                                <Input
                                    id="logo"
                                    ref="logoInput"
                                    name="logo"
                                    type="file"
                                    accept="image/png,image/jpeg,image/webp"
                                    @change="handleLogoChange"
                                />
                                <InputError :message="errors.logo" />
                                <div v-if="logoPreviewUrl" class="mt-2 flex items-center gap-3 text-xs text-muted-foreground">
                                    <span v-if="logoFileName">{{ logoFileName }}</span>
                                    <button
                                        type="button"
                                        class="text-xs font-medium text-rose-600 transition hover:text-rose-700"
                                        @click="clearLogo"
                                    >
                                        Remover preview
                                    </button>
                                </div>
                            </div>
                        </div>
                        <p class="text-xs text-muted-foreground">
                            Envie uma imagem JPG, PNG ou WebP ate 5MB.
                        </p>
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
