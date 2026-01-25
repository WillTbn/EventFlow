<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';

import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import { destroy, edit, index, update } from '@/routes/admin/usuarios';
import type { BreadcrumbItem } from '@/types';

interface UserFormData {
    id: number;
    name: string;
    email: string;
    role: string | null;
}

const props = defineProps<{
    user: UserFormData;
    roles: string[];
}>();

const form = useForm({
    name: props.user.name,
    email: props.user.email,
    password: '',
    password_confirmation: '',
    role: props.user.role ?? props.roles[0] ?? 'member',
});

const deleteForm = useForm({});

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Usuarios', href: index().url },
    { title: props.user.name, href: edit({ user: props.user.id }).url },
];

const submit = () => {
    form.put(update({ user: props.user.id }).url);
};

const destroyUser = () => {
    deleteForm.delete(destroy({ user: props.user.id }).url);
};
</script>

<template>
    <Head :title="`Editar ${user.name}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-semibold">Editar usuario</h1>
                    <p class="text-sm text-muted-foreground">
                        Atualize dados e permissao do usuario.
                    </p>
                </div>
                <Button as-child variant="outline">
                    <Link :href="index().url">Voltar</Link>
                </Button>
            </div>

            <Card class="max-w-2xl">
                <CardHeader>
                    <CardTitle>Dados do usuario</CardTitle>
                    <CardDescription>
                        Altere informacoes e senha se necessario.
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <form class="grid gap-6" @submit.prevent="submit">
                        <div class="grid gap-2">
                            <Label for="name">Nome</Label>
                            <Input id="name" v-model="form.name" required />
                            <InputError :message="form.errors.name" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="email">Email</Label>
                            <Input id="email" v-model="form.email" type="email" required />
                            <InputError :message="form.errors.email" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="password">Nova senha</Label>
                            <Input id="password" v-model="form.password" type="password" />
                            <InputError :message="form.errors.password" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="password_confirmation">Confirmar senha</Label>
                            <Input
                                id="password_confirmation"
                                v-model="form.password_confirmation"
                                type="password"
                            />
                            <InputError :message="form.errors.password_confirmation" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="role">Papel</Label>
                            <select
                                id="role"
                                v-model="form.role"
                                class="h-10 rounded-md border bg-background px-3 text-sm"
                            >
                                <option v-for="role in roles" :key="role" :value="role">
                                    {{ role }}
                                </option>
                            </select>
                            <InputError :message="form.errors.role" />
                        </div>

                        <div class="flex items-center justify-between">
                            <Button type="submit" :disabled="form.processing">
                                Salvar
                            </Button>
                            <Button
                                type="button"
                                variant="destructive"
                                :disabled="deleteForm.processing"
                                @click="destroyUser"
                            >
                                Excluir
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
