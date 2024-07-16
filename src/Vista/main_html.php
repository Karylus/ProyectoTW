<?php
require_once '../src/Vista/html_extend.php';

function HTMLrenderWeb($data)
{
  // Cabecera head del documento
  $head = _HTMLhead($data['title']);

  $nav = isset($data['navegacion']) ? _HTMLnavegacion($data['navegacion'], $data['navegacion_login']) : '';

  // Cuerpo del documento
  $cuerpo = '';

  if (isset($data['home'])) {
    $cuerpo .= _HTMLshowHome();
  }
  if (isset($data['servicios'])) {
    $cuerpo .= _HTMLshowServicios();
  }
  if (isset($data['registrarse'])) {
    $cuerpo .= _HTMLshowFormularioRegistrarse($data);
  }
  if (isset($data['iniciar_sesion'])) {
    $cuerpo .= _HTMLshowFormularioIniciarSesion($data);
  }
  if (isset($data['ver_perfil'])) {
    $cuerpo .= _HTMLshowDatosPerfil($data['SCRIPT_NAME'], $data['ver_perfil']);
  }
  if (isset($data['editar_datos_usuario'])) {
    $cuerpo .= _HTMLshowFormularioEditarDatos($data);
  }
  if (isset($data['habitaciones'])) {
    $cuerpo .= _HTMLshowHabitaciones($data);
  }
  if (isset($data['anadir_habitacion']) || isset($data['anadir_habitacion_validate']) ||  isset($data['editar_habitacion_validate'])) {
    $cuerpo .= _HTMLshowAnadirHabitacion($data);
  }
  if (isset($data['anadir_fotografia'])) {
    $cuerpo .= _HTMLshowAnadirFotografia($data);
  }
  if (isset($data['logs'])) {
    $cuerpo .= _HTMLshowLog($data);
  }
  if (isset($data['base_datos'])) {
    $cuerpo .= _HTMLshowBaseDatos($data);
  }
  if (isset($data['ver_usuarios'])) {
    $cuerpo .= _HTMLshowVerUsuarios($data);
  }
  if (isset($data['eliminar_usuario'])) {
    $cuerpo .= _HTMLshowVerUsuarios($data);
  }
  if (isset($data['mostrar_reservas'])) {
    $cuerpo .= _HTMLshowReservas($data);
  }
  if (isset($data['anadir_reserva'])) {
    $cuerpo .= _HTMLshowAnadirReserva($data);
  }
  if (isset($data['confirmar_reserva'])) {
    $cuerpo .= _HTMLshowConfirmarReserva($data);
  }
  if (isset($data['editar_reserva'])) {
    $cuerpo .= _HTMLshowEditarReserva($data);
  }
  if (isset($_SESSION['info'])){   // Mostrar mensajes informativos
    if($_SESSION['info'] != ""){
      $cuerpo .= _HTMLshowInfo($_SESSION['info'], 'info');
      $_SESSION['info'] = "";
    }
  }
  if (isset($data['info']))   // Mostrar mensajes informativos
    $cuerpo .= _HTMLshowInfo($data['info'], 'info');
 
  if (isset($data['error']))  // Mostrar mensajes de error
    $cuerpo .= _HTMLshowInfo($data['error'], 'error');

  $footer = _HTMLfooter();

  return <<<HTML
  <!DOCTYPE html>
  <html>
    $head
    <body class="flex flex-col min-h-screen">
      <div class="relative mb-auto">
        $nav
        <main>
          $cuerpo
        </main>
      </div>
      $footer
    </body>
  </html>
  HTML;
}
