<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import {
    CalendarDays,
    Globe,
    LayoutGrid,
    Users,
} from 'lucide-vue-next';
import { computed } from 'vue';

import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
    SidebarSeparator,
} from '@/components/ui/sidebar';
import { useTenantUrl } from '@/composables/useTenantUrl';
import { index as adminEventsIndex } from '@/routes/admin/eventos';
import { index as adminUsersIndex } from '@/routes/admin/usuarios';
import { index as publicEventsIndex } from '@/routes/eventos';
import { type NavItem } from '@/types';

import AppLogo from './AppLogo.vue';

const page = usePage();
const tenantRole = computed<string | null>(
    () => (page.props.auth?.tenant_role as string | undefined) ?? null,
);
const isAdmin = computed(() => tenantRole.value === 'admin');
const isModerator = computed(() => tenantRole.value === 'moderator');
const canManage = computed(() => isAdmin.value || isModerator.value);

const { withTenantUrl, dashboardUrl } = useTenantUrl();

const primaryNavItems = computed<NavItem[]>(() => [
    {
        title: 'Dashboard',
        href: dashboardUrl.value,
        icon: LayoutGrid,
    },
]);
const publicNavItems: NavItem[] = [
    {
        title: 'Eventos publicos',
        href: withTenantUrl(publicEventsIndex()),
        icon: Globe,
    },
];

const managementNavItems = computed<NavItem[]>(() => {
    if (!canManage.value) return [];

    return [
        {
            title: 'Eventos',
            href: withTenantUrl(adminEventsIndex()),
            icon: CalendarDays,
        },
        {
            title: 'Usuarios',
            href: withTenantUrl(adminUsersIndex()),
            icon: Users,
        },
    ];
});

const footerNavItems: NavItem[] = [
    {
        title: 'Repositorio',
        href: 'https://github.com/WillTbn/EventFlow',
        icon: Globe,
    },
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="dashboardUrl">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain label="Principal" :items="primaryNavItems" />
            <SidebarSeparator class="my-2" />
            <NavMain label="Publico" :items="publicNavItems" />

            <template v-if="managementNavItems.length">
                <SidebarSeparator class="my-2" />
                <NavMain label="Administracao" :items="managementNavItems" />
            </template>
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>


