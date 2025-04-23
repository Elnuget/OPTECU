<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calificar Pedido - ESCLERÓPTICA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .rating {
            display: flex;
            flex-direction: row-reverse;
            justify-content: center;
            gap: 10px;
        }
        .rating input {
            display: none;
        }
        .rating label {
            cursor: pointer;
            font-size: 50px;
            color: #ddd;
        }
        .rating input:checked ~ label {
            color: #ffd700;
        }
        .rating label:hover,
        .rating label:hover ~ label {
            color: #ffd700;
        }
        .container {
            max-width: 600px;
            margin-top: 50px;
        }
        .comentario-container {
            margin-top: 20px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="card-header text-center">
                <h3>Califica tu Experiencia</h3>
            </div>
            <div class="card-body">
                <h5 class="text-center mb-4">Hola {{ $pedido->cliente }}, nos gustaría conocer tu opinión acerca de la atención brindada por {{ $pedido->usuario }}</h5>

                <form action="{{ route('pedidos.guardar-calificacion-publica', ['id' => $pedido->id, 'token' => $token]) }}" method="POST">
                    @csrf
                    
                    @if(session('error'))
                        <div class="alert alert-danger">
                            {{ session('error') }}
                        </div>
                    @endif

                    <div class="rating mb-4">
                        @for($i = 5; $i >= 1; $i--)
                            <input type="radio" name="calificacion" value="{{ $i }}" id="star{{ $i }}" required>
                            <label for="star{{ $i }}">★</label>
                        @endfor
                    </div>

                    <div class="comentario-container">
                        <label for="comentario" class="form-label">Cuéntanos más sobre tu experiencia:</label>
                        <textarea class="form-control" id="comentario" name="comentario" rows="4" placeholder="Tu opinión nos ayuda a mejorar nuestro servicio..."></textarea>
                    </div>

                    <div class="text-center">
                        <button type="submit" class="btn btn-primary">Enviar Calificación</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html> 