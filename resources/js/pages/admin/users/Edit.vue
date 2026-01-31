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
import { edit, index, resendInvite, update } from '@/routes/admin/usuarios';
import { type BreadcrumbItem } from '@/types';

interface UserPayload {
    hash_id: string;
    name: string;
    email: string;
    role: string | null;
    access_started_at: string | null;
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
        href: withTenantUrl(edit({ user: props.user.hash_id })),
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
            <CardContent class="space-y-6">
                <div class="rounded-md border border-dashed border-muted-foreground/30 bg-muted/40 p-4 text-sm">
                    <div v-if="user.access_started_at" class="text-muted-foreground">
                        Começou a acessar o espaço em
                        <span class="font-medium text-foreground">{{ user.access_started_at }}</span>
                    </div>
                    <div
                        v-else
                        class="flex flex-col gap-3 text-muted-foreground sm:flex-row sm:items-center sm:justify-between"
                    >
                        <span>Este usuário ainda não confirmou o acesso ao workspace.</span>
                        <Form
                            :action="withTenantUrl(resendInvite({ user: user.hash_id }).url)"
                            method="post"
                            v-slot="{ errors, processing }"
                        >
                            <div class="flex flex-col items-start gap-2">
                                <Button size="sm" variant="outline" :disabled="processing">
                                    Reenviar convite
                                </Button>
                                <InputError :message="errors.invite" />
                            </div>
                        </Form>
                    </div>
                </div>

                <Form
                    :action="withTenantUrl(update({ user: user.hash_id }).url)"
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
