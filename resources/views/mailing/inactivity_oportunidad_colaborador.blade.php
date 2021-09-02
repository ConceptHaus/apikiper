
<!-- <html>
  Hola, {{$msg['colaborador']}}!,
  <br>
  <br>
  La oportunidad {{$msg['nombre_oportunidad']}} ha estado inactiva por mas de {{$msg['inactivity_period']}} horas.
  <br>
  <br>
  <a href="{{env('FRONT_END_URL')}}/#/detalle-oportunidad/{{$msg['id_oportunidad']}}">
    <button>
      Ver oportunidad
    </button>
  </a>
  <br>
  <br>
  -Kiper

</html> -->

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>Document</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&display=swap" rel="stylesheet">
  <style>
  body {font-family: 'Inter', sans-serif;}
  </style>
</head>
<body style="background:#F6F8FA">
  <table align="center" cellpadding="0" cellspacing="0" width="550" style="background:#FFF; border-radius:7px; padding:30px; margin-top:35px">
    <tbody>
      <tr>
        <td width="135">
          <img src="https://kiper-bucket.s3.us-east-2.amazonaws.com/generales/logoEmail.jpg">
        </td>
      </tr>
        <td>
          <table>
            <tbody style="vertical-align:top">
              <tr>
                <td height="20"></td>
              </tr>
              <tr>
                <td>
                  <span style="font-size:16px;font-weight:bold;color:#000000">Hola, {{$msg['colaborador']}}!</span>
                </td>
              </tr>
              <tr>
                <td height="20"></td>
              </tr>
              <tr>
                <td>
                  <p style="font-size:14px;color:#000">
                    La oportunidad {{$msg['nombre_oportunidad']}}  ha estado <b style="font-weight:bold;">inactiva por mas de {{$msg['inactivity_period']}} horas</b>.
                  </p>
                </td>
              </tr>
              <tr>
                <td height="20"></td>
              </tr>
              <tr>
                <td>
                  <a href="{{env('FRONT_END_URL')}}/#/detalle-oportunidad/{{$msg['id_oportunidad']}}" style="font-size:14px;font-weight:bold;color:#FFF;padding:10px 30px; display:inline-block;background:#000;text-decoration:none;border-radius:4px;">
                    Ver Oportunidad
                  </a>
                </td>
              </tr>
              <tr>
                <td height="20"></td>
              </tr>
              <tr>
                <td>
                  <p style="font-size:14px;color:#000">Atentamente,<br>
                  <b style="font-weight:bold;">El equipo de Kiper</b>
                  </p>
                </td>
              </tr>
              <tr>
                <td height="50" style="text-align:right">
                  <img src="https://kiper-bucket.s3.us-east-2.amazonaws.com/generales/email1.jpg">
                </td>
              </tr>
            </tbody>
          </table>
        </td>
      </tr>
    </tbody>
  </table>
</body>
</html>

                