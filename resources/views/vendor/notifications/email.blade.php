<x-mail::message>
{{-- Saudação --}}
@if (! empty($greeting))
# {{ $greeting }}
@else
@if ($level === 'error')
# @lang('Ops!')
@else
# @lang('Olá!')
@endif
@endif

{{-- Linhas de introdução --}}
@foreach ($introLines as $line)
{{ $line }}

@endforeach

{{-- Botão de ação --}}
@isset($actionText)
<?php
    $color = match ($level) {
        'success', 'error' => $level,
        default => 'primary',
    };
?>
<x-mail::button :url="$actionUrl" :color="$color">
{{ $actionText }}
</x-mail::button>
@endisset

{{-- Linhas finais --}}
@foreach ($outroLines as $line)
{{ $line }}

@endforeach

{{-- Saudações finais --}}
@if (! empty($salutation))
{{ $salutation }}
@else
@lang('Atenciosamente,')<br>
{{ config('app.name') }}
@endif

{{-- Subcopy --}}
@isset($actionText)
<x-slot:subcopy>
@lang(
    "Se você estiver com dificuldades para clicar no botão \":actionText\", copie e cole a URL abaixo\n".
    'no seu navegador:',
    [
        'actionText' => $actionText,
    ]
)
<span class="break-all">[{{ $displayableActionUrl }}]({{ $actionUrl }})</span>
</x-slot:subcopy>
@endisset
</x-mail::message>
