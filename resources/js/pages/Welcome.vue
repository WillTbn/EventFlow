<script setup lang="ts">


</script>

<template>
    <div class="landing-page space-y-20">
        <section class="relative overflow-hidden rounded-3xl border bg-gradient-to-b from-slate-50 via-white to-slate-100 px-6 py-14 shadow-sm sm:px-10 lg:px-16">
            <div class="absolute -left-24 -top-20 h-56 w-56 rounded-full bg-amber-200/40 blur-3xl"></div>
            <div class="absolute -right-24 top-8 h-64 w-64 rounded-full bg-emerald-200/40 blur-3xl"></div>

            <div class="relative grid items-center gap-12 lg:grid-cols-[1.1fr_0.9fr]">
                <div class="space-y-6">
                    <p class="text-xs uppercase tracking-[0.35em] text-slate-500">Fact Sphere</p>
                    <h1 class="text-3xl font-semibold leading-tight text-slate-900 sm:text-4xl lg:text-5xl">
                        Transforme cada evento em uma experiencia fluida e memoravel.
                    </h1>
                    <p class="max-w-xl text-base text-slate-600 sm:text-lg">
                        Planeje, organize e acompanhe tudo em um so lugar. Do primeiro
                        rascunho ate a validacao final, o Fact Sphere deixa o seu time no
                        controle de cada detalhe.
                    </p>
                    <div class="flex flex-wrap items-center gap-3">
                        <Link
                            href="/login"
                            class="rounded-full bg-slate-900 px-5 py-2.5 text-sm font-medium text-white transition hover:-translate-y-0.5 hover:bg-slate-800"
                        >
                            Comecar agora
                        </Link>
                        <Link
                            href="#tutorial"
                            class="rounded-full border border-slate-300 px-5 py-2.5 text-sm font-medium text-slate-700 transition hover:-translate-y-0.5 hover:border-slate-400"
                        >
                            Ver como funciona
                        </Link>
                    </div>
                    <div class="flex flex-wrap gap-4 text-xs text-slate-500">
                        <span class="rounded-full border border-slate-200 bg-white px-3 py-1">Agenda inteligente</span>
                        <span class="rounded-full border border-slate-200 bg-white px-3 py-1">Convites automaticos</span>
                        <span class="rounded-full border border-slate-200 bg-white px-3 py-1">Check-in rapido</span>
                    </div>
                </div>

                <div class="relative mx-auto w-full max-w-sm">
                    <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-lg">
                        <div class="mb-6 flex items-center justify-between">
                            <span class="text-sm font-medium text-slate-700">Painel geral</span>
                            <span class="text-xs text-emerald-600">Hoje</span>
                        </div>
                        <div class="space-y-4">
                            <div class="rounded-2xl border border-slate-100 bg-slate-50 p-4">
                                <p class="text-xs uppercase tracking-wide text-slate-400">Evento ativo</p>
                                <p class="mt-2 text-lg font-semibold text-slate-900">Festival Criativo 2026</p>
                                <div class="mt-4 h-2 w-full rounded-full bg-slate-200">
                                    <div class="h-2 w-2/3 rounded-full bg-emerald-500"></div>
                                </div>
                            </div>
                            <div class="grid gap-3 sm:grid-cols-2">
                                <div class="rounded-2xl border border-slate-100 bg-white p-3">
                                    <p class="text-xs text-slate-400">Convidados</p>
                                    <p class="text-lg font-semibold text-slate-900">482</p>
                                </div>
                                <div class="rounded-2xl border border-slate-100 bg-white p-3">
                                    <p class="text-xs text-slate-400">Aprovacoes</p>
                                    <p class="text-lg font-semibold text-slate-900">18</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="absolute -bottom-6 -right-4 hidden rounded-3xl border border-slate-100 bg-white p-4 text-xs text-slate-500 shadow-sm sm:block">
                        <p>Checklist automatico concluido</p>
                        <p class="mt-1 font-medium text-slate-700">97% pronto</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="grid gap-10 lg:grid-cols-[0.9fr_1.1fr] lg:items-center px-6  sm:px-10 lg:px-16">
            <div class="space-y-5">
                <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Objetivo</p>
                <h2 class="text-2xl font-semibold text-slate-900 sm:text-3xl">
                    Centralizar o fluxo de eventos, do briefing ao publico final.
                </h2>
                <p class="text-base text-slate-600">
                    O Fact Sphere organiza equipes, prazos e comunicacao em um unico
                    painel para que cada evento seja executado sem ruido e com clareza.
                </p>
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h3 class="text-sm font-semibold text-slate-900">Para produtores</h3>
                    <p class="mt-2 text-sm text-slate-600">
                        Controle total de cronogramas, fornecedores e entregas com visao
                        clara de prioridades.
                    </p>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h3 class="text-sm font-semibold text-slate-900">Para equipes</h3>
                    <p class="mt-2 text-sm text-slate-600">
                        Acompanhe tarefas, compartilhe atualizacoes e mantenha a equipe
                        alinhada em segundos.
                    </p>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h3 class="text-sm font-semibold text-slate-900">Para o publico</h3>
                    <p class="mt-2 text-sm text-slate-600">
                        Informacoes claras, check-in rapido e comunicacao consistente do
                        inicio ao fim.
                    </p>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h3 class="text-sm font-semibold text-slate-900">Para organizadores</h3>
                    <p class="mt-2 text-sm text-slate-600">
                        Indicadores visiveis e facil compartilhamento de resultados para
                        cada evento.
                    </p>
                </div>
            </div>
        </section>

        <section id="tutorial" class="rounded-3xl border border-slate-200 bg-white px-6 py-12 shadow-sm sm:px-10 lg:px-16">
            <div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                <div class="space-y-3">
                    <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Mini tutorial</p>
                    <h2 class="text-2xl font-semibold text-slate-900 sm:text-3xl">
                        Tudo o que voce precisa para rodar um evento sem friccao.
                    </h2>
                    <p class="max-w-2xl text-sm text-slate-600">
                        Crie eventos, organize times, convide participantes e acompanhe
                        resultados. O Fact Sphere guia voce em cada etapa com automacoes
                        simples.
                    </p>
                </div>
                <Link
                    href="/login"
                    class="rounded-full border border-slate-300 px-5 py-2 text-sm font-medium text-slate-700 transition hover:-translate-y-0.5 hover:border-slate-400"
                >
                    Ver painel completo
                </Link>
            </div>

            <div class="mt-10 grid gap-6 lg:grid-cols-3">
                <div class="rounded-2xl border border-slate-100 bg-slate-50 p-6">
                    <p class="text-xs uppercase tracking-wide text-slate-400">01. Criar</p>
                    <h3 class="mt-2 text-lg font-semibold text-slate-900">Abra um evento em minutos</h3>
                    <p class="mt-3 text-sm text-slate-600">
                        Defina nome, data, local e publico. O Fact Sphere gera paginas e
                        comunicados automaticamente.
                    </p>
                </div>
                <div class="rounded-2xl border border-slate-100 bg-slate-50 p-6">
                    <p class="text-xs uppercase tracking-wide text-slate-400">02. Organizar</p>
                    <h3 class="mt-2 text-lg font-semibold text-slate-900">Monte o fluxo de tarefas</h3>
                    <p class="mt-3 text-sm text-slate-600">
                        Distribua responsaveis, acompanhe pendencias e veja o progresso
                        em tempo real.
                    </p>
                </div>
                <div class="rounded-2xl border border-slate-100 bg-slate-50 p-6">
                    <p class="text-xs uppercase tracking-wide text-slate-400">03. Engajar</p>
                    <h3 class="mt-2 text-lg font-semibold text-slate-900">Convide, confirme e receba</h3>
                    <p class="mt-3 text-sm text-slate-600">
                        Envie convites, acompanhe confirmacoes e habilite check-in
                        rapido no dia do evento.
                    </p>
                </div>
            </div>

            <div class="mt-8 grid gap-6 lg:grid-cols-2">
                <div class="rounded-2xl border border-slate-100 bg-white p-6">
                    <h3 class="text-lg font-semibold text-slate-900">Funcionalidades que voce encontra</h3>
                    <ul class="mt-4 space-y-2 text-sm text-slate-600">
                        <li>Calendario centralizado com alertas.</li>
                        <li>Gestao de equipes e tarefas.</li>
                        <li>Automacao de convites e mensagens.</li>
                        <li>Check-in rapido e relatorios.</li>
                    </ul>
                </div>
                <div class="rounded-2xl border border-slate-100 bg-white p-6">
                    <h3 class="text-lg font-semibold text-slate-900">Como usar no dia a dia</h3>
                    <ol class="mt-4 space-y-2 text-sm text-slate-600">
                        <li>1. Defina o evento e personalize a pagina.</li>
                        <li>2. Convide a equipe e ajuste tarefas.</li>
                        <li>3. Ative o check-in e acompanhe a presenca.</li>
                        <li>4. Revise indicadores e salve aprendizados.</li>
                    </ol>
                </div>
            </div>
        </section>
    </div>
</template>

<style scoped>
.landing-page {
    font-family: 'Sora', 'Avenir Next', 'Segoe UI', 'Helvetica Neue', sans-serif;
}
</style>
