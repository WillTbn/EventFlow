<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import {
    CalendarDays,
    Globe,
    KeyRound,
    LayoutGrid,
    Paintbrush,
    ShieldCheck,
    UserRound,
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
import { dashboard } from '@/routes';
import { index as adminEventsIndex } from '@/routes/admin/eventos';
import { index as adminUsersIndex } from '@/routes/admin/usuarios';
import { index as publicEventsIndex } from '@/routes/eventos';
import { type NavItem } from '@/types';
import AppLogo from './AppLogo.vue';

const page = usePage();
const roles = computed<string[]>(() => (page.props.auth?.roles as string[] | undefined) ?? []);
const isAdmin = computed(() => roles.value.includes('admin'));
const isModerator = computed(() => roles.value.includes('moderator'));
const canManage = computed(() => isAdmin.value || isModerator.value);

const primaryNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: dashboard(),
        icon: LayoutGrid,
    },
];

const publicNavItems: NavItem[] = [
    {
        title: 'Eventos publicos',
        href: publicEventsIndex(),
        icon: Globe,
    },
];

const managementNavItems = computed<NavItem[]>(() => {
    if (!canManage.value) return [];

    return [
        {
            title: 'Eventos',
            href: adminEventsIndex(),
            icon: CalendarDays,
        },
        {
            title: 'Usuarios',
            href: adminUsersIndex(),
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
                        <Link :href="dashboard()">
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
