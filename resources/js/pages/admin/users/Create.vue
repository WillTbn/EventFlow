<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';

import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useTenantUrl } from '@/composables/useTenantUrl';
import AppLayout from '@/layouts/AppLayout.vue';
import { create, index, store } from '@/routes/admin/usuarios';
import { type BreadcrumbItem } from '@/types';

defineProps<{
    roles: string[];
}>();

const { withTenantUrl } = useTenantUrl();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Usuarios',
        href: withTenantUrl(index()),
    },
    {
        title: 'Novo usuario',
        href: withTenantUrl(create()),
    },
];
</script>

<template>
    <Head title="Novo usuario" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <Card>
            <CardHeader>
                <CardTitle>Novo usuario</CardTitle>
            </CardHeader>
            <CardContent>
                <Form
                    :action="withTenantUrl(store().url)"
                    method="post"
                    v-slot="{ errors, processing }"
                    class="space-y-6"
                >
                    <div class="grid gap-2">
                        <Label for="name">Nome</Label>
                        <Input id="name" name="name" placeholder="Nome completo" />
                        <InputError :message="errors.name" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="email">Email</Label>
                        <Input id="email" name="email" type="email" />
                        <InputError :message="errors.email" />
                    </div>

                    <p class="text-sm text-muted-foreground">
                        Enviaremos um link para o usuário definir a senha de acesso.
                    </p>

                    <div class="grid gap-2">
                        <Label for="role">Role</Label>
                        <select
                            id="role"
                            name="role"
                            class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm"
                        >
                            <option v-for="role in roles" :key="role" :value="role">
                                {{ role }}
                            </option>
                        </select>
                        <InputError :message="errors.role" />
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
