<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Factura;

class FacturaAutorizada extends Mailable
{
    use Queueable, SerializesModels;

    public $factura;
    public $xmlPath;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Factura $factura, $xmlPath)
    {
        $this->factura = $factura;
        $this->xmlPath = $xmlPath;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $mail = $this->subject('Factura Electrónica Autorizada - OPTECU')
                     ->view('emails.factura-autorizada')
                     ->with([
                         'factura' => $this->factura,
                         'numeroFactura' => $this->factura->numero_factura,
                         'fechaAutorizacion' => $this->factura->fecha_autorizacion,
                         'numeroAutorizacion' => $this->factura->numero_autorizacion,
                         'total' => $this->factura->total
                     ]);

        // Adjuntar el XML si existe
        if ($this->xmlPath && file_exists($this->xmlPath)) {
            $nombreArchivo = 'factura_' . $this->factura->numero_factura . '_autorizada.xml';
            $mail->attach($this->xmlPath, [
                'as' => $nombreArchivo,
                'mime' => 'application/xml',
            ]);
        }

        return $mail;
    }
}
