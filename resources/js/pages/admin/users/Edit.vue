<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useTenantUrl } from '@/composables/useTenantUrl';
import AppLayout from '@/layouts/AppLayout.vue';
import { edit, index, update } from '@/routes/admin/usuarios';
import { type BreadcrumbItem } from '@/types';

interface UserPayload {
    id: number;
    name: string;
    email: string;
    role: string | null;
}

const props = defineProps<{
    user: UserPayload;
    roles: string[];
}>();

const { withTenantUrl } = useTenantUrl();

const breadcrumbs = computed<BreadcrumbItem[]>(() => [
    {
        title: 'Usuarios',
        href: withTenantUrl(index()),
    },
    {
        title: 'Editar usuario',
        href: withTenantUrl(edit({ user: props.user.id })),
    },
]);
</script>

<template>
    <Head title="Editar usuario" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <Card>
            <CardHeader>
                <CardTitle>Editar usuario</CardTitle>
            </CardHeader>
            <CardContent>
                <Form
                    :action="withTenantUrl(update({ user: user.id }).url)"
                    method="put"
                    v-slot="{ errors, processing }"
                    class="space-y-6"
                >
                    <div class="grid gap-2">
                        <Label for="name">Nome</Label>
                        <Input id="name" name="name" :default-value="user.name" />
                        <InputError :message="errors.name" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="email">Email</Label>
                        <Input
                            id="email"
                            name="email"
                            type="email"
                            :default-value="user.email"
                        />
                        <InputError :message="errors.email" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="password">Senha</Label>
                        <Input id="password" name="password" type="password" />
                        <InputError :message="errors.password" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="password_confirmation">Confirmar senha</Label>
                        <Input
                            id="password_confirmation"
                            name="password_confirmation"
                            type="password"
                        />
                        <InputError :message="errors.password_confirmation" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="role">Role</Label>
                        <select
                            id="role"
                            name="role"
                            class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm"
                            :value="user.role || ''"
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
                            Voltar
                        </Link>
                    </div>
                </Form>
            </CardContent>
        </Card>
    </AppLayout>
</template>
