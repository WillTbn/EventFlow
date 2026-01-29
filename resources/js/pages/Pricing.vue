<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Pricing" />

    <section class="mx-auto flex w-full max-w-5xl flex-col gap-8 px-6 py-10">
        <header class="space-y-2">
            <h1 class="text-3xl font-semibold text-slate-900">Planos</h1>
            <p class="text-sm text-slate-600">
                Escolha o plano ideal para o seu workspace.
            </p>
        </header>

        <div class="grid gap-6 md:grid-cols-3">
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900">Free</h2>
                <p class="mt-2 text-sm text-slate-600">Para comecar rapido.</p>
                <ul class="mt-4 space-y-2 text-sm text-slate-600">
                    <li>Eventos ilimitados</li>
                    <li>Convites basicos</li>
                    <li>Suporte por email</li>
                </ul>
                <Link
                    href="/register"
                    class="mt-6 inline-flex rounded-full bg-slate-900 px-4 py-2 text-xs font-medium text-white"
                >
                    Comecar
                </Link>
            </div>
            <div class="rounded-2xl border border-slate-900 bg-slate-900 p-6 text-white shadow-lg">
                <h2 class="text-lg font-semibold">Plus</h2>
                <p class="mt-2 text-sm text-slate-200">Mais automacoes e limites.</p>
                <ul class="mt-4 space-y-2 text-sm text-slate-200">
                    <li>Workflows avancados</li>
                    <li>Branding do workspace</li>
                    <li>Suporte prioritario</li>
                </ul>
                <Link
                    href="/register"
                    class="mt-6 inline-flex rounded-full bg-white px-4 py-2 text-xs font-medium text-slate-900"
                >
                    Testar
                </Link>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900">Enterprise</h2>
                <p class="mt-2 text-sm text-slate-600">Para operacoes em escala.</p>
                <ul class="mt-4 space-y-2 text-sm text-slate-600">
                    <li>Multi-time completo</li>
                    <li>SLAs dedicados</li>
                    <li>Integracoes customizadas</li>
                </ul>
                <Link
                    href="/register"
                    class="mt-6 inline-flex rounded-full border border-slate-200 px-4 py-2 text-xs font-medium text-slate-700"
                >
                    Falar com vendas
                </Link>
            </div>
        </div>
    </section>
</template>
