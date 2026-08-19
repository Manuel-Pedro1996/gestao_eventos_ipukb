<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />

<title>
    {{ $title ?? config('app.name', 'Gestão de Eventos') }}
</title>

<!-- Favicon com override total -->
<link rel="icon" type="image/png" href="{{ asset('img/ti.png') }}?v={{ time() }}">
<link rel="shortcut icon" type="image/png" href="{{ asset('img/ti.png') }}?v={{ time() }}">

@fonts

@vite(['resources/css/app.css', 'resources/js/app.js'])
@fluxAppearance