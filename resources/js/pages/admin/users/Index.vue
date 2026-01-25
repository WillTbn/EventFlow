<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';

import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';
import { create, edit, index } from '@/routes/admin/usuarios';
import type { BreadcrumbItem } from '@/types';

interface UserItem {
    id: number;
    name: string;
    email: string;
    role: string | null;
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface UsersPagination {
    data: UserItem[];
    links: PaginationLink[];
}

defineProps<{
    users: UsersPagination;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Usuarios',
        href: index().url,
    },
];
</script>

<template>
    <Head title="Usuarios" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-semibold">Usuarios</h1>
                    <p class="text-sm text-muted-foreground">
                        Gerencie acessos e perfis do sistema.
                    </p>
                </div>
                <Button as-child>
                    <Link :href="create().url">Novo usuario</Link>
                </Button>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>Lista de usuarios</CardTitle>
                    <CardDescription>
                        Usuarios cadastrados e seus papeis.
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="overflow-hidden rounded-lg border">
                        <table class="min-w-full divide-y divide-border">
                            <thead class="bg-muted/50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-sm font-medium">
                                        Nome
                                    </th>
                                    <th class="px-4 py-3 text-left text-sm font-medium">
                                        Email
                                    </th>
                                    <th class="px-4 py-3 text-left text-sm font-medium">
                                        Papel
                                    </th>
                                    <th class="px-4 py-3 text-right text-sm font-medium">
                                        Acoes
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border">
                                <tr v-for="user in users.data" :key="user.id">
                                    <td class="px-4 py-3 text-sm">
                                        {{ user.name }}
                                    </td>
                                    <td class="px-4 py-3 text-sm">
                                        {{ user.email }}
                                    </td>
                                    <td class="px-4 py-3 text-sm capitalize">
                                        {{ user.role ?? 'sem papel' }}
                                    </td>
                                    <td class="px-4 py-3 text-right text-sm">
                                        <Link
                                            class="text-primary hover:underline"
                                            :href="edit({ user: user.id }).url"
                                        >
                                            Editar
                                        </Link>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4 flex flex-wrap gap-2">
                        <Link
                            v-for="link in users.links"
                            :key="link.label"
                            class="rounded border px-3 py-1 text-sm"
                            :class="link.active ? 'bg-muted font-medium' : 'bg-background'"
                            :href="link.url ?? ''"
                            v-html="link.label"
                        />
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
