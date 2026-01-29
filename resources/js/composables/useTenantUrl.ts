import type { InertiaLinkProps } from '@inertiajs/vue3';
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

import { toUrl } from '@/lib/utils';

export function useTenantUrl() {
    const page = usePage();
    const tenantSlug = computed(
        () => (page.props.tenant?.slug as string | undefined) ?? null,
    );

    const dashboardUrl = computed(() =>
        tenantSlug.value ? `/t/${tenantSlug.value}/admin` : '/workspaces',
    );

    const withTenantUrl = (href: NonNullable<InertiaLinkProps['href']>) => {
        const url = toUrl(href);

        if (!url || !tenantSlug.value) {
            return url;
        }

        if (url.startsWith('http')) {
            return url;
        }

        const normalized = url.startsWith('/') ? url : `/${url}`;
        const tenantPrefix = `/t/${tenantSlug.value}`;

        if (normalized.startsWith(tenantPrefix)) {
            return normalized;
        }

        return `${tenantPrefix}${normalized}`;
    };

    return {
        tenantSlug,
        dashboardUrl,
        withTenantUrl,
    };
}
