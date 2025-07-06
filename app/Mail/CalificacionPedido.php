<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Pedido;

class CalificacionPedido extends Mailable
{
    use Queueable, SerializesModels;

    public $pedido;
    public $token;
    public $url;

    public function __construct(Pedido $pedido)
    {
        $this->pedido = $pedido;
        $this->token = hash('sha256', $pedido->id . $pedido->created_at);
        $this->url = route('pedidos.calificar-publico', ['id' => $pedido->id, 'token' => $this->token]);
    }

    public function build()
    {
        return $this->subject('Califica tu experiencia en L BARBOSA')
                    ->markdown('emails.calificacion-pedido');
    }
} 