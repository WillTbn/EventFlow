<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';

interface TenantItem {
    id: number;
    name: string;
    slug: string;
    plan: string;
    status: string;
    role: string;
}

defineProps<{
    tenants: TenantItem[];
}>();
</script>

<template>
    <Head title="Workspaces" />

    <section class="mx-auto flex w-full max-w-5xl flex-col gap-6 px-6 py-10">
        <header class="space-y-2">
            <h1 class="text-3xl font-semibold text-slate-900">Workspaces</h1>
            <p class="text-sm text-slate-600">
                Escolha o workspace para continuar no painel administrativo.
            </p>
        </header>

        <div v-if="!tenants.length" class="rounded-2xl border border-slate-200 bg-white p-6">
            <p class="text-sm text-slate-600">
                Voce ainda nao participa de nenhum workspace.
            </p>
        </div>

        <div v-else class="grid gap-4 md:grid-cols-2">
            <article
                v-for="tenant in tenants"
                :key="tenant.id"
                class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"
            >
                <div class="flex items-start justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-slate-900">
                            {{ tenant.name }}
                        </h2>
                        <p class="text-xs uppercase tracking-wide text-slate-400">
                            {{ tenant.plan }} • {{ tenant.role }}
                        </p>
                    </div>
                    <span
                        class="rounded-full border border-slate-200 px-3 py-1 text-xs text-slate-500"
                    >
                        {{ tenant.status }}
                    </span>
                </div>

                <div class="mt-4 flex items-center justify-between">
                    <p class="text-xs text-slate-500">/{{ tenant.slug }}</p>
                    <Link
                        :href="`/t/${tenant.slug}/admin`"
                        class="rounded-full bg-slate-900 px-4 py-2 text-xs font-medium text-white transition hover:-translate-y-0.5 hover:bg-slate-800"
                    >
                        Entrar
                    </Link>
                </div>
            </article>
        </div>
    </section>
</template>
