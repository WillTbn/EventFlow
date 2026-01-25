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
import FlexRowCenter from '@/layouts/FlexRowCenter.vue';
import { index, store } from '@/routes/admin/usuarios';
import type { BreadcrumbItem } from '@/types';

const props = defineProps<{
    roles: string[];
}>();

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    role: props.roles[0] ?? 'member',
});

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Usuarios', href: index().url },
    { title: 'Novo usuario', href: store().url },
];

const submit = () => {
    form.post(store().url);
};
</script>

<template>
    <Head title="Novo usuario" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-2">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-semibold">Novo usuario</h1>
                    <p class="text-sm text-muted-foreground">
                        Cadastre um novo usuario e defina o papel.
                    </p>
                </div>
                <Button as-child variant="outline">
                    <Link :href="index().url">Voltar</Link>
                </Button>
            </div>
            <FlexRowCenter>
                <Card class="max-w-2xl basis-128">
                    <CardHeader>
                        <CardTitle>Dados do usuario</CardTitle>
                        <CardDescription>
                            Informacoes basicas e permissao inicial.
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
                                <Label for="password">Senha</Label>
                                <Input id="password" v-model="form.password" type="password" required />
                                <InputError :message="form.errors.password" />
                            </div>

                            <div class="grid gap-2">
                                <Label for="password_confirmation">Confirmar senha</Label>
                                <Input
                                    id="password_confirmation"
                                    v-model="form.password_confirmation"
                                    type="password"
                                    required
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

                            <div class="flex justify-end">
                                <Button type="submit" :disabled="form.processing">
                                    Criar usuario
                                </Button>
                            </div>
                        </form>
                    </CardContent>
                </Card>
            </FlexRowCenter>
        </div>
    </AppLayout>
</template>
