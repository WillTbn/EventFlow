@component('mail::message')
# {{ $title }}

{{ $slot }}

@isset($actionUrl)
@component('mail::button', ['url' => $actionUrl])
{{ $actionText }}
@endcomponent
@endisset

@isset($subcopy)
{{ $subcopy }}
@endisset

@isset($signature)
{{ $signature }}
@endisset
@endcomponent
