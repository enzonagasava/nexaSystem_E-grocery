<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\User;
use App\Models\Empresa;

class BemVindoEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;

    public function __construct(User $user)
    {
        $this->user = $user;
        $this->empresa = Empresa::first();
    }

    public function build()
    {
        return $this
            ->subject('Bem-vindo ao ' . config('app.name'))
            ->markdown('emails.bemvindo')
            ->with([
                'logo' => asset('storage/images/logo' . $this->empresa->logo),
                'empresa' => $this->empresa,
                'user' => $this->user,
            ]);
    }
}
