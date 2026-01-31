<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

import AppLogoIcon from '@/components/AppLogoIcon.vue';

interface TenantLogoPayload {
    name?: string | null;
    logo_url?: string | null;
    logo_thumb_url?: string | null;
}

const page = usePage();
const tenant = computed<TenantLogoPayload | null>(
    () => (page.props.tenant as TenantLogoPayload | undefined) ?? null,
);
const appName = computed(
    () => (page.props.name as string | undefined) ?? 'Fact Sphere',
);
const logoUrl = computed(
    () => tenant.value?.logo_thumb_url || tenant.value?.logo_url || null,
);
const displayName = computed(() => tenant.value?.name || appName.value);
</script>

<template>
    <div
        class="flex aspect-square size-8 items-center justify-center rounded-md bg-sidebar-primary text-sidebar-primary-foreground"
    >
        <img
            v-if="logoUrl"
            :src="logoUrl"
            :alt="displayName"
            class="h-5 w-5 rounded-sm object-contain"
        />
        <AppLogoIcon
            v-else
            class="size-5 fill-current text-white dark:text-black"
        />
    </div>
    <div class="ml-1 grid flex-1 text-left text-sm">
        <span class="mb-0.5 truncate leading-tight font-semibold">
            {{ displayName }}
        </span>
    </div>
</template>
