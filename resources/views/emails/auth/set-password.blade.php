@component('emails.layouts.welcome')
@slot('title')
Crie sua senha
@endslot

Olá{{ $user->name ? ' ' . $user->name : '' }},

Você foi convidado para acessar o {{ config('app.name') }}@if($tenant) no workspace "{{ $tenant->name }}"@endif.

Clique no botão abaixo para definir sua senha e começar a usar a plataforma.

@slot('actionUrl')
{{ $url }}
@endslot

@slot('actionText')
Definir senha
@endslot

@slot('subcopy')
Este link expira em {{ $expire }} minutos. Se você não esperava este convite, pode ignorar este e-mail.
@endslot

@slot('signature')
Obrigado,  
{{ config('app.name') }}
@endslot
@endcomponent
