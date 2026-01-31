<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';

import { useTenantUrl } from '@/composables/useTenantUrl';
import { show } from '@/routes/eventos';
import { show as workspaceShow } from '@/routes/workspace';

interface WorkspacePayload {
    name: string | null;
    slug: string | null;
    logo_url: string | null;
    logo_thumb_url: string | null;
}

interface EventListItem {
    id: number;
    title: string;
    description: string | null;
    hash_id: string;
    location: string | null;
    starts_at: string | null;
    ends_at: string | null;
    main_photo_medium_path: string | null;
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
    workspace: WorkspacePayload;
    events: Pagination<EventListItem>;
}>();

const { withTenantUrl } = useTenantUrl();
</script>

<template>
    <Head title="Eventos" />

    <div class="landing-page space-y-20  bg-white text-slate-900 dark:bg-white dark:text-slate-900">
        <section
            class="relative overflow-hidden rounded-3xl border bg-gradient-to-b from-slate-50 via-white to-slate-100 px-6 py-10 shadow-sm sm:px-10 lg:px-16"
        >
            <div class="absolute -left-16 -top-16 h-44 w-44 rounded-full bg-amber-200/40 blur-3xl"></div>
            <div class="absolute -right-16 top-8 h-52 w-52 rounded-full bg-emerald-200/40 blur-3xl"></div>

            <div class="relative space-y-6 ">
                <div class="flex flex-wrap items-center justify-between gap-6">
                    <div class="flex items-center gap-4">
                        <div
                            class="flex h-14 w-14 items-center justify-center overflow-hidden rounded-2xl border border-slate-200 bg-white"
                        >
                            <img
                                v-if="workspace.logo_thumb_url || workspace.logo_url"
                                :src="workspace.logo_thumb_url || workspace.logo_url || ''"
                                :alt="workspace.name || 'Workspace'"
                                class="h-full w-full object-contain"
                            />
                            <span v-else class="text-sm font-semibold text-slate-500">
                                {{ workspace.name?.slice(0, 2) || 'WS' }}
                            </span>
                        </div>
                        <div>
                            <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Workspace</p>
                            <h1 class="text-2xl font-semibold text-slate-900 sm:text-3xl">
                                {{ workspace.name || 'Eventos publicos' }}
                            </h1>
                        </div>
                    </div>
                    <Link
                        class="rounded-full border border-slate-300 px-5 py-2 text-sm font-medium text-slate-700 transition hover:-translate-y-0.5 hover:border-slate-400"
                        :href="withTenantUrl(workspaceShow())"
                    >
                        Voltar ao workspace
                    </Link>
                </div>

                <p class="max-w-2xl text-sm text-slate-600 sm:text-base">
                    Explore eventos publicados e compartilhe as experiencias com o seu publico.
                    Apenas eventos confirmados aparecem nesta lista.
                </p>
            </div>
        </section>

        <section class="space-y-20 container">
            <div v-if="events.data.length" class="grid gap-6 md:grid-cols-2 lg:grid-cols-3 px-12">
                <article
                    v-for="event in events.data"
                    :key="event.id"
                    class="group overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition hover:-translate-y-1 hover:border-slate-300"
                >
                    <div class="relative h-40 overflow-hidden bg-slate-100">
                        <img
                            v-if="event.main_photo_medium_path"
                            :src="event.main_photo_medium_path"
                            :alt="event.title"
                            class="h-full w-full object-cover transition duration-300 group-hover:scale-105"
                        />
                        <div
                            v-else
                            class="flex h-full items-center justify-center bg-gradient-to-br from-slate-100 via-white to-slate-200 text-xs text-slate-400"
                        >
                            Sem imagem
                        </div>
                    </div>
                    <div class="space-y-3 p-5">
                        <div class="space-y-1">
                            <h2 class="text-base font-semibold text-slate-900">
                                {{ event.title }}
                            </h2>
                            <p class="line-clamp-3 text-sm text-slate-600">
                                {{ event.description || 'Sem descricao cadastrada.' }}
                            </p>
                        </div>
                        <div class="flex flex-wrap items-center gap-3 text-xs text-slate-500">
                            <span v-if="event.location" class="rounded-full bg-slate-100 px-3 py-1">
                                {{ event.location }}
                            </span>
                            <span v-if="event.starts_at" class="rounded-full bg-slate-100 px-3 py-1">
                                {{ event.starts_at }}
                            </span>
                        </div>
                        <Link
                            class="inline-flex items-center gap-2 text-sm font-medium text-slate-700 transition group-hover:text-slate-900"
                            :href="withTenantUrl(show({ event: event.hash_id }))"
                        >
                            Ver detalhes
                            <span aria-hidden="true">→</span>
                        </Link>
                    </div>
                </article>
            </div>

            <div v-else class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 p-8 text-sm text-slate-500">
                Nenhum evento publico publicado ainda.
            </div>

            <div v-if="events.links.length > 1" class="flex flex-wrap gap-2">
                <Link
                    v-for="link in events.links"
                    :key="link.label"
                    :href="link.url || ''"
                    class="rounded border border-slate-200 px-3 py-1 text-sm text-slate-600"
                    :class="[
                        link.active
                            ? 'border-slate-900 text-slate-900'
                            : 'hover:border-slate-300',
                        link.url ? '' : 'pointer-events-none opacity-60',
                    ]"
                >
                    <span v-html="link.label"></span>
                </Link>
            </div>

        </section>
    </div>
</template>

<style scoped>
.landing-page {
    font-family: 'Sora', 'Avenir Next', 'Segoe UI', 'Helvetica Neue', sans-serif;
}
</style>
