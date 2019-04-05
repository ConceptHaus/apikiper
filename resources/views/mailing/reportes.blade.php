<body>
    <h1>¡Hola {{$user->nombre}} {{$user->apellido}}!</h1>
    <p>Este es tu reporte semanal</p>
    @foreach($result as $r)
    <p style="color:{{$r->color}};">{{$r->status}} {{$r->total}}</p>
    @endforeach
</body>

