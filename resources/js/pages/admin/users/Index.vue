<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';

import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { useTenantUrl } from '@/composables/useTenantUrl';
import AppLayout from '@/layouts/AppLayout.vue';
import { create, edit, index } from '@/routes/admin/usuarios';
import { type BreadcrumbItem } from '@/types';

interface UserListItem {
    id: number;
    hash_id: string;
    name: string;
    email: string;
    role: string | null;
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface Pagination<T> {
    data: T[];
    links: PaginationLink[];
}

defineProps<{
    users: Pagination<UserListItem>;
}>();

const { withTenantUrl } = useTenantUrl();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Usuarios',
        href: withTenantUrl(index()),
    },
];
</script>

<template>
    <Head title="Usuarios" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-xl font-semibold">Usuarios</h1>
                <p class="text-sm text-muted-foreground">
                    Gerencie membros do seu workspace.
                </p>
            </div>

            <Button as-child>
                <Link :href="withTenantUrl(create())">Novo usuario</Link>
            </Button>
        </div>

        <Card class="mt-6">
            <CardHeader>
                <CardTitle>Lista de usuarios</CardTitle>
            </CardHeader>
            <CardContent>
                <div v-if="users.data.length === 0" class="text-sm text-muted-foreground">
                    Nenhum usuario cadastrado ainda.
                </div>

                <div v-else class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="border-b text-left">
                            <tr>
                                <th class="px-3 py-2">Nome</th>
                                <th class="px-3 py-2">Email</th>
                                <th class="px-3 py-2">Role</th>
                                <th class="px-3 py-2 text-right">Acoes</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="user in users.data"
                                :key="user.id"
                                class="border-b last:border-0"
                            >
                                <td class="px-3 py-2 font-medium">
                                    {{ user.name }}
                                </td>
                                <td class="px-3 py-2">
                                    {{ user.email }}
                                </td>
                                <td class="px-3 py-2 capitalize">
                                    {{ user.role }}
                                </td>
                                <td class="px-3 py-2 text-right">
                                        <Button size="sm" variant="outline" as-child>
                                        <Link :href="withTenantUrl(edit({ user: user.hash_id }))">
                                            Editar
                                        </Link>
                                    </Button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div
                    v-if="users.links.length > 1"
                    class="mt-4 flex flex-wrap gap-2"
                >
                    <Link
                        v-for="link in users.links"
                        :key="link.label"
                        :href="link.url || ''"
                        class="rounded border px-3 py-1 text-sm"
                        :class="[
                            link.active
                                ? 'border-primary text-primary'
                                : 'border-border text-muted-foreground',
                            link.url ? 'hover:border-primary/60' : 'pointer-events-none opacity-60',
                        ]"
                    >
                        <span v-html="link.label"></span>
                    </Link>
                </div>
            </CardContent>
        </Card>
    </AppLayout>
</template>
