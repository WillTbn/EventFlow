<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';

import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { useTenantUrl } from '@/composables/useTenantUrl';
import { index } from '@/routes/eventos';

interface EventPayload {
    id: number;
    title: string;
    description: string | null;
    location: string | null;
    starts_at: string | null;
    ends_at: string | null;
    status: string;
    is_public: boolean;
}

defineProps<{
    event: EventPayload;
}>();

const { withTenantUrl } = useTenantUrl();
</script>

<template>
    <Head :title="event.title" />

    <Card>
        <CardHeader>
            <CardTitle>{{ event.title }}</CardTitle>
        </CardHeader>
        <CardContent class="space-y-4">
            <p class="text-sm text-muted-foreground">{{ event.description }}</p>
            <div class="text-sm text-muted-foreground">
                <p>{{ event.location }}</p>
                <p>{{ event.starts_at }} - {{ event.ends_at }}</p>
            </div>
            <Link class="text-sm text-primary" :href="withTenantUrl(index())">
                Voltar
            </Link>
        </CardContent>
    </Card>
</template>
