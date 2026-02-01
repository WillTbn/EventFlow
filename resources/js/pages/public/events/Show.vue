<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

import { useTenantUrl } from '@/composables/useTenantUrl';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { index } from '@/routes/eventos';

interface WorkspacePayload {
    name: string | null;
    slug: string | null;
    logo_url: string | null;
    logo_thumb_url: string | null;
}

interface EventPayload {
    id: number;
    hash_id: string;
    title: string;
    description: string | null;
    location: string | null;
    starts_at: string | null;
    ends_at: string | null;
    status: string;
    is_public: boolean;
    capacity: number | null;
    rsvp_count: number;
    cover_url: string | null;
}

interface EventPhotoPayload {
    id: number;
    medium_url: string;
    thumb_url: string;
}

const props = defineProps<{
    workspace: WorkspacePayload;
    event: EventPayload;
    photos: EventPhotoPayload[];
}>();

const page = usePage();
const { withTenantUrl } = useTenantUrl();

const shareCopied = ref(false);
const shareUrl = computed(() =>
    typeof window === 'undefined' ? '' : window.location.href,
);
const rawDescription = computed(() => props.event.description?.trim() || '');
const metaDescription = computed(() => {
    const fallback = props.workspace.name
        ? `Evento publico do workspace ${props.workspace.name}.`
        : 'Evento publico do workspace.';
    const base = rawDescription.value || fallback;
    return base.replace(/\s+/g, ' ').slice(0, 160);
});

const canShare = computed(
    () => props.event.is_public && props.event.status === 'published',
);

const shareLabel = computed(() =>
    shareCopied.value ? 'Link copiado' : 'Compartilhar',
);

const termsUrl = computed(
    () => (page.props.legal as { terms_url?: string | null } | undefined)?.terms_url ?? null,
);

const isRsvpOpen = ref(false);
const isSubmitting = ref(false);
const rsvpStatus = ref<'created' | 'updated' | null>(null);
const rsvpMessage = ref<string | null>(null);
const fieldErrors = ref<Record<string, string>>({});

const rsvpForm = ref({
    name: '',
    email: '',
    phone_whatsapp: '',
    communication_preference: 'email',
    notifications_scope: 'event_only',
    accept_terms: false,
    company: '',
});

const rsvpUrl = computed(() =>
    withTenantUrl(`/eventos/${props.event.hash_id}/rsvp`),
);
const icsUrl = computed(() =>
    withTenantUrl(`/eventos/${props.event.hash_id}/calendar.ics`),
);

const requiresPhone = computed(() =>
    ['whatsapp', 'sms'].includes(rsvpForm.value.communication_preference),
);

const canSubmitRsvp = computed(() => {
    if (requiresPhone.value && !rsvpForm.value.phone_whatsapp.trim()) {
        return false;
    }

    if (termsUrl.value && !rsvpForm.value.accept_terms) {
        return false;
    }

    return true;
});

const googleCalendarUrl = computed(() => {
    if (!props.event.starts_at) {
        return '';
    }

    const start = toGoogleDate(props.event.starts_at);
    const end = toGoogleDate(props.event.ends_at ?? props.event.starts_at);
    const details = `${props.event.description ?? ''}\n\n${shareUrl.value}`.trim();

    return `https://calendar.google.com/calendar/render?action=TEMPLATE&text=${encodeURIComponent(
        props.event.title,
    )}&dates=${start}/${end}&details=${encodeURIComponent(
        details,
    )}&location=${encodeURIComponent(props.event.location ?? '')}`;
});

const outlookCalendarUrl = computed(() => {
    if (!props.event.starts_at) {
        return '';
    }

    const start = toOutlookDate(props.event.starts_at);
    const end = toOutlookDate(props.event.ends_at ?? props.event.starts_at);
    const details = `${props.event.description ?? ''}\n\n${shareUrl.value}`.trim();

    return `https://outlook.live.com/calendar/0/deeplink/compose?path=/calendar/action/compose&rru=addevent&subject=${encodeURIComponent(
        props.event.title,
    )}&startdt=${encodeURIComponent(start)}&enddt=${encodeURIComponent(
        end,
    )}&body=${encodeURIComponent(details)}&location=${encodeURIComponent(
        props.event.location ?? '',
    )}`;
});

async function submitRsvp(): Promise<void> {
    fieldErrors.value = {};
    rsvpMessage.value = null;

    if (requiresPhone.value && !rsvpForm.value.phone_whatsapp.trim()) {
        fieldErrors.value.phone_whatsapp =
            'Informe o telefone para contato por WhatsApp ou SMS.';
        return;
    }

    if (termsUrl.value && !rsvpForm.value.accept_terms) {
        fieldErrors.value.accept_terms = 'Aceite os termos para continuar.';
        return;
    }

    isSubmitting.value = true;

    try {
        const csrfToken = document
            .querySelector('meta[name="csrf-token"]')
            ?.getAttribute('content');

        const response = await fetch(rsvpUrl.value, {
            method: 'POST',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken ?? '',
            },
            credentials: 'same-origin',
            body: JSON.stringify(rsvpForm.value),
        });

        if (response.status === 422) {
            const payload = (await response.json()) as {
                errors?: Record<string, string[]>;
            };

            fieldErrors.value = Object.fromEntries(
                Object.entries(payload.errors ?? {}).map(([key, messages]) => [
                    key,
                    messages[0],
                ]),
            );
            return;
        }

        if (!response.ok) {
            throw new Error('failed');
        }

        const payload = (await response.json()) as {
            status?: 'created' | 'updated';
        };

        rsvpStatus.value = payload.status ?? 'created';
        rsvpMessage.value =
            rsvpStatus.value === 'updated'
                ? 'Presenca atualizada com sucesso.'
                : 'Presenca confirmada com sucesso.';
    } catch {
        rsvpMessage.value = 'Nao foi possivel confirmar sua presenca.';
    } finally {
        isSubmitting.value = false;
    }
}

function toGoogleDate(value: string): string {
    const normalized = value.replace(' ', 'T');
    const parsed = new Date(`${normalized}:00`);
    return parsed.toISOString().replace(/[-:]/g, '').split('.')[0] + 'Z';
}

function toOutlookDate(value: string): string {
    const normalized = value.replace(' ', 'T');
    const parsed = new Date(`${normalized}:00`);
    return parsed.toISOString();
}

async function handleShare(): Promise<void> {
    if (!canShare.value || !shareUrl.value) {
        return;
    }

    const payload = {
        title: props.event.title,
        text: metaDescription.value,
        url: shareUrl.value,
    };

    if (navigator.share) {
        await navigator.share(payload);
        return;
    }

    if (navigator.clipboard?.writeText) {
        await navigator.clipboard.writeText(shareUrl.value);
        shareCopied.value = true;
        window.setTimeout(() => {
            shareCopied.value = false;
        }, 2200);
    }
}
</script>

<template>
    <Head :title="event.title">
        <meta head-key="description" name="description" :content="metaDescription" />
        <meta head-key="og:title" property="og:title" :content="event.title" />
        <meta head-key="og:description" property="og:description" :content="metaDescription" />
        <meta head-key="og:type" property="og:type" content="article" />
        <meta v-if="shareUrl" head-key="og:url" property="og:url" :content="shareUrl" />
        <meta
            v-if="event.cover_url"
            head-key="og:image"
            property="og:image"
            :content="event.cover_url"
        />
        <meta head-key="twitter:card" name="twitter:card" content="summary_large_image" />
        <meta head-key="twitter:title" name="twitter:title" :content="event.title" />
        <meta head-key="twitter:description" name="twitter:description" :content="metaDescription" />
        <meta
            v-if="event.cover_url"
            head-key="twitter:image"
            name="twitter:image"
            :content="event.cover_url"
        />
    </Head>

    <div class="landing-page space-y-10 bg-white dark:bg-white dark:text-slate-900">
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
                    <div class="flex flex-wrap items-center gap-3">
                        <Link
                            class="rounded-full border border-slate-300 px-5 py-2 text-sm font-medium text-slate-700 transition hover:-translate-y-0.5 hover:border-slate-400"
                            :href="withTenantUrl(index())"
                        >
                            Voltar para eventos
                        </Link>
                        <Dialog v-model:open="isRsvpOpen">
                            <DialogTrigger as-child>
                                <Button
                                    class="rounded-full bg-slate-900 px-5 py-2 text-sm font-medium text-white transition hover:-translate-y-0.5 hover:bg-slate-800"
                                >
                                    Confirmar presença
                                </Button>
                            </DialogTrigger>
                            <DialogContent class="sm:max-w-lg">
                                <DialogHeader class="space-y-2" v-if="!rsvpStatus">
                                    <DialogTitle>Confirmar presenca</DialogTitle>
                                    <DialogDescription>
                                        Preencha os dados abaixo para garantir sua vaga.
                                    </DialogDescription>
                                </DialogHeader>

                                <div v-if="rsvpStatus" class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-900">
                                    <p class="font-semibold">{{ rsvpMessage }}</p>
                                    <p class="mt-1 text-emerald-700">Adicionar ao seu calendario:</p>
                                    <div class="mt-3 flex flex-wrap gap-2">
                                        <a
                                            :href="icsUrl"
                                            class="rounded-full border border-emerald-200 bg-white px-4 py-2 text-xs font-semibold text-emerald-800"
                                        >
                                            Baixar .ics
                                        </a>
                                        <a
                                            v-if="googleCalendarUrl"
                                            :href="googleCalendarUrl"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            class="rounded-full border border-emerald-200 bg-white px-4 py-2 text-xs font-semibold text-emerald-800"
                                        >
                                            Google Calendar
                                        </a>
                                        <a
                                            v-if="outlookCalendarUrl"
                                            :href="outlookCalendarUrl"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            class="rounded-full border border-emerald-200 bg-white px-4 py-2 text-xs font-semibold text-emerald-800"
                                        >
                                            Outlook
                                        </a>
                                    </div>
                                </div>

                                <form v-else class="mt-4 space-y-4" @submit.prevent="submitRsvp">
                                    <div class="space-y-2">
                                        <Label for="rsvp-name">Nome</Label>
                                        <Input
                                            id="rsvp-name"
                                            v-model="rsvpForm.name"
                                            type="text"
                                            placeholder="Seu nome completo"
                                        />
                                        <p v-if="fieldErrors.name" class="text-xs text-red-600">
                                            {{ fieldErrors.name }}
                                        </p>
                                    </div>

                                    <div class="space-y-2">
                                        <Label for="rsvp-email">Email</Label>
                                        <Input
                                            id="rsvp-email"
                                            v-model="rsvpForm.email"
                                            type="email"
                                            placeholder="voce@email.com"
                                        />
                                        <p v-if="fieldErrors.email" class="text-xs text-red-600">
                                            {{ fieldErrors.email }}
                                        </p>
                                    </div>

                                    <div class="space-y-2">
                                        <Label for="rsvp-phone">Whatsapp / SMS (opcional)</Label>
                                        <Input
                                            id="rsvp-phone"
                                            v-model="rsvpForm.phone_whatsapp"
                                            type="tel"
                                            placeholder="+55 11 99999-9999"
                                        />
                                        <p class="text-xs text-slate-500">
                                            Se preferir WhatsApp ou SMS, informe o telefone.
                                        </p>
                                        <p v-if="fieldErrors.phone_whatsapp" class="text-xs text-red-600">
                                            {{ fieldErrors.phone_whatsapp }}
                                        </p>
                                    </div>

                                    <div class="grid gap-4 sm:grid-cols-2">
                                        <div class="space-y-2">
                                            <Label for="rsvp-preference">Preferencia</Label>
                                            <select
                                                id="rsvp-preference"
                                                v-model="rsvpForm.communication_preference"
                                                class="w-full rounded-md border border-slate-200 bg-white px-3 py-2 text-sm"
                                            >
                                                <option value="email">Email</option>
                                                <option value="whatsapp">WhatsApp</option>
                                                <option value="sms">SMS</option>
                                            </select>
                                            <p v-if="fieldErrors.communication_preference" class="text-xs text-red-600">
                                                {{ fieldErrors.communication_preference }}
                                            </p>
                                        </div>
                                        <div class="space-y-2">
                                            <Label for="rsvp-scope">Notificacoes</Label>
                                            <select
                                                id="rsvp-scope"
                                                v-model="rsvpForm.notifications_scope"
                                                class="w-full rounded-md border border-slate-200 bg-white px-3 py-2 text-sm"
                                            >
                                                <option value="event_only">Somente este evento</option>
                                                <option value="workspace">Eventos do workspace</option>
                                                <option value="platform">Atualizacoes da plataforma</option>
                                            </select>
                                            <p v-if="fieldErrors.notifications_scope" class="text-xs text-red-600">
                                                {{ fieldErrors.notifications_scope }}
                                            </p>
                                        </div>
                                    </div>

                                    <div v-if="termsUrl" class="rounded-xl border border-slate-200 bg-slate-50 p-3 text-xs text-slate-600">
                                        <label class="flex items-start gap-2">
                                            <input
                                                v-model="rsvpForm.accept_terms"
                                                type="checkbox"
                                                class="mt-0.5 h-4 w-4 rounded border-slate-300"
                                            />
                                            <span>
                                                Eu li e aceito os
                                                <a
                                                    :href="termsUrl"
                                                    target="_blank"
                                                    rel="noopener noreferrer"
                                                    class="font-semibold text-slate-900 underline"
                                                >
                                                    termos e politica
                                                </a>
                                            </span>
                                        </label>
                                        <p v-if="fieldErrors.accept_terms" class="mt-2 text-xs text-red-600">
                                            {{ fieldErrors.accept_terms }}
                                        </p>
                                    </div>

                                    <input v-model="rsvpForm.company" type="text" class="hidden" tabindex="-1" autocomplete="off" />

                                    <div class="flex flex-wrap items-center justify-between gap-3 pt-2">
                                        <p v-if="rsvpMessage" class="text-sm text-red-600">
                                            {{ rsvpMessage }}
                                        </p>
                                        <Button
                                            type="submit"
                                            class="rounded-full bg-slate-900 px-5 py-2 text-sm font-medium text-white"
                                            :disabled="isSubmitting || !canSubmitRsvp"
                                        >
                                            {{ isSubmitting ? 'Enviando...' : 'Confirmar' }}
                                        </Button>
                                    </div>
                                </form>
                            </DialogContent>
                        </Dialog>
                        <button
                            v-if="canShare"
                            type="button"
                            class="rounded-full border border-slate-300 px-5 py-2 text-sm font-medium text-slate-700 transition hover:-translate-y-0.5 hover:border-slate-400 disabled:cursor-not-allowed disabled:opacity-70"
                            :disabled="!shareUrl"
                            @click="handleShare"
                        >
                            {{ shareLabel }}
                        </button>
                    </div>
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

        <section class="grid gap-8 lg:grid-cols-[1.4fr_0.6fr] lg:items-start bg-white dark:bg-white dark:text-slate-900">
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
