
<html>
  Hola, {{$msg['admin']}}!,
  <br>
  <br>
  Esta es la notificacion número:  {{$msg['attempt']}} para {{$msg['colaborador']}}.
  <br>
  La oportunidad {{$msg['nombre_oportunidad']}} ha estado inactiva por mas de {{$msg['inactivity_period']}} horas.
  <br>
  <br>
  <a href="{{env('FRONT_END_URL')}}/#/perfil-prospecto/{{$msg['id_prospecto']}}">
    <button>
      Ver prospecto
    </button>
  </a>
  <br>
  <br>
  -Kiper

</html>
                