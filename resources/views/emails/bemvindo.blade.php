@component('mail::layout')

    @slot('header')
        @component('mail::header', [
            'url' => config('app.url'),
            'logo' => $logo
        ])
        @endcomponent
    @endslot


# Olá, {{ $user->name }}!

Obrigado por se cadastrar no **{{ config('app.name') }}**.
Seu acesso foi criado com sucesso.

@component('mail::button', ['url' => url('/')])
Acessar o Sistema
@endcomponent

Se você não realizou este cadastro, apenas ignore este e-mail.

Atenciosamente,
**{{ config('app.name') }}**

    @slot('footer')
        @component('mail::footer')
        © {{ date('Y') }} {{ config('app.name') }}. Todos os direitos reservados.
        @endcomponent
    @endslot

@endcomponent
