@component('mail::message')
# Hola {{ $pedido->cliente }}

Gracias por tu compra en L BARBOSA. Nos gustaría conocer tu opinión sobre nuestro servicio.

Por favor, toma un momento para calificar tu experiencia haciendo clic en el siguiente botón:

@component('mail::button', ['url' => $url])
Calificar mi Experiencia
@endcomponent

Tu opinión es muy importante para nosotros y nos ayuda a mejorar nuestro servicio.

Gracias,<br>
{{ config('app.name') }}
@endcomponent 