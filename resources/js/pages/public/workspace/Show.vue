<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';

import { useTenantUrl } from '@/composables/useTenantUrl';
import { index as eventsIndex, show as eventsShow } from '@/routes/eventos';

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
    <Head :title="workspace.name ? `Workspace ${workspace.name}` : 'Workspace'" />

    <div class="landing-page space-y-12">
        <section
            class="relative overflow-hidden rounded-3xl border bg-gradient-to-b from-slate-50 via-white to-slate-100 px-6 py-12 shadow-sm sm:px-10 lg:px-16"
        >
            <div class="absolute -left-20 -top-16 h-52 w-52 rounded-full bg-amber-200/40 blur-3xl"></div>
            <div class="absolute -right-16 top-8 h-60 w-60 rounded-full bg-emerald-200/40 blur-3xl"></div>

            <div class="relative flex flex-col gap-8 lg:flex-row lg:items-center lg:justify-between">
                <div class="space-y-4">
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
                            <p class="text-xs uppercase tracking-[0.3em] text-slate-400">
                                Workspace
                            </p>
                            <h1 class="text-2xl font-semibold text-slate-900 sm:text-3xl">
                                {{ workspace.name || 'Workspace' }}
                            </h1>
                        </div>
                    </div>
                    <p class="max-w-2xl text-sm text-slate-600 sm:text-base">
                        Conheca os eventos publicos e novidades desta organizacao. Aqui voce encontra
                        as experiencias abertas, detalhes e atualizacoes mais recentes.
                    </p>
                </div>

                <div class="flex flex-wrap gap-3">
                    <Link
                        :href="withTenantUrl(eventsIndex())"
                        class="rounded-full bg-slate-900 px-5 py-2 text-sm font-medium text-white transition hover:-translate-y-0.5 hover:bg-slate-800"
                    >
                        Ver todos os eventos
                    </Link>
                    <Link
                        :href="withTenantUrl(eventsIndex())"
                        class="rounded-full border border-slate-300 px-5 py-2 text-sm font-medium text-slate-700 transition hover:-translate-y-0.5 hover:border-slate-400"
                    >
                        Programacao completa
                    </Link>
                </div>
            </div>
        </section>

        <section class="space-y-6">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 class="text-xl font-semibold text-slate-900">Eventos em destaque</h2>
                    <p class="text-sm text-slate-600">
                        Apenas eventos publicados e abertos ao publico.
                    </p>
                </div>
                <Link
                    :href="withTenantUrl(eventsIndex())"
                    class="text-sm font-medium text-slate-700 transition hover:text-slate-900"
                >
                    Ver lista completa
                </Link>
            </div>

            <div v-if="events.data.length" class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
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
                            <h3 class="text-base font-semibold text-slate-900">
                                {{ event.title }}
                            </h3>
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
                            :href="withTenantUrl(eventsShow({ event: event.hash_id }))"
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
