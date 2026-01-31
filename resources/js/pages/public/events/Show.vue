<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';

import { useTenantUrl } from '@/composables/useTenantUrl';
import { index } from '@/routes/eventos';

interface WorkspacePayload {
    name: string | null;
    slug: string | null;
    logo_url: string | null;
    logo_thumb_url: string | null;
}

interface EventPayload {
    id: number;
    title: string;
    description: string | null;
    location: string | null;
    starts_at: string | null;
    ends_at: string | null;
    status: string;
    is_public: boolean;
    cover_url: string | null;
}

interface EventPhotoPayload {
    id: number;
    medium_url: string;
    thumb_url: string;
}

defineProps<{
    workspace: WorkspacePayload;
    event: EventPayload;
    photos: EventPhotoPayload[];
}>();

const { withTenantUrl } = useTenantUrl();
</script>

<template>
    <Head :title="event.title" />

    <div class="landing-page space-y-10">
        <section
            class="relative overflow-hidden rounded-3xl border bg-gradient-to-b from-slate-50 via-white to-slate-100 px-6 py-10 shadow-sm sm:px-10 lg:px-16"
        >
            <div class="absolute -left-16 -top-16 h-44 w-44 rounded-full bg-amber-200/40 blur-3xl"></div>
            <div class="absolute -right-16 top-8 h-52 w-52 rounded-full bg-emerald-200/40 blur-3xl"></div>

            <div class="relative space-y-6">
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
                            <p class="text-base font-semibold text-slate-900">
                                {{ workspace.name || 'Workspace' }}
                            </p>
                        </div>
                    </div>
                    <Link
                        class="rounded-full border border-slate-300 px-5 py-2 text-sm font-medium text-slate-700 transition hover:-translate-y-0.5 hover:border-slate-400"
                        :href="withTenantUrl(index())"
                    >
                        Voltar para eventos
                    </Link>
                </div>

                <div>
                    <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Evento publico</p>
                    <h1 class="text-3xl font-semibold text-slate-900 sm:text-4xl">
                        {{ event.title }}
                    </h1>
                    <p class="mt-3 max-w-2xl text-sm text-slate-600 sm:text-base">
                        {{ event.description || 'Sem descricao cadastrada.' }}
                    </p>
                </div>
            </div>
        </section>

        <section class="grid gap-8 lg:grid-cols-[1.4fr_0.6fr] lg:items-start">
            <div class="space-y-6">
                <div class="overflow-hidden rounded-3xl border border-slate-200 bg-slate-100 shadow-sm">
                    <img
                        v-if="event.cover_url"
                        :src="event.cover_url"
                        :alt="event.title"
                        class="h-72 w-full object-cover sm:h-96"
                    />
                    <div
                        v-else
                        class="flex h-72 items-center justify-center bg-gradient-to-br from-slate-100 via-white to-slate-200 text-sm text-slate-400 sm:h-96"
                    >
                        Sem foto principal
                    </div>
                </div>

                <div v-if="photos.length" class="space-y-4">
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-semibold text-slate-900">Galeria</h2>
                        <span class="text-xs text-slate-500">{{ photos.length }} fotos</span>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        <div
                            v-for="photo in photos"
                            :key="photo.id"
                            class="overflow-hidden rounded-2xl border border-slate-200 bg-slate-100"
                        >
                            <img :src="photo.medium_url" alt="Foto do evento" class="h-40 w-full object-cover" />
                        </div>
                    </div>
                </div>
            </div>

            <aside class="space-y-4">
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h2 class="text-sm font-semibold text-slate-900">Detalhes do evento</h2>
                    <div class="mt-4 space-y-3 text-sm text-slate-600">
                        <div>
                            <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Data</p>
                            <p class="text-slate-900">
                                {{ event.starts_at || 'Data nao definida' }}
                                <span v-if="event.ends_at"> - {{ event.ends_at }}</span>
                            </p>
                        </div>
                        <div>
                            <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Local</p>
                            <p class="text-slate-900">{{ event.location || 'Local nao informado' }}</p>
                        </div>
                    </div>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-6 text-sm text-slate-600">
                    Precisa de mais informacoes? Entre em contato com o organizador do workspace.
                </div>
            </aside>
        </section>
    </div>
</template>

<style scoped>
.landing-page {
    font-family: 'Sora', 'Avenir Next', 'Segoe UI', 'Helvetica Neue', sans-serif;
}
</style>
