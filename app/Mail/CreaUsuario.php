<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CreaUsuario extends Mailable
{
    use Queueable, SerializesModels;

    public $personal;

    public function __construct($personal)
    {
        $this->personal = $personal;
    }

    public function envelope()
    {
        return new Envelope(
            subject: 'Credenciales de Acceso',
        );
    }

    public function content()
    {
        return new Content(
            view: 'mail.creausuario',
        );
    }

    public function attachments()
    {
        return [];
    }
}
