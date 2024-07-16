<?php
require_once '../src/Controlador/cInfo.php';
require_once '../src/Controlador/cUsuario.php';
require_once '../src/Controlador/cHabitacion.php';
require_once '../src/Controlador/cReserva.php';
require_once '../src/Controlador/cBackup.php';

require_once '../src/Vista/main_html.php';
require_once '../src/Vista/html.php';
require_once '../src/Vista/html_extend.php';

session_start();

$data = array();
$data['title'] = 'M&M Hotels';

$data['navegacion'] = [
    ['texto' => 'Inicio', 'url' => $_SERVER['SCRIPT_NAME'] . '?p=home'],
    ['texto' => 'Servicios', 'url' => $_SERVER['SCRIPT_NAME'] . '?p=servicios'],
    ['texto' => 'Habitaciones', 'url' => $_SERVER['SCRIPT_NAME'] . '?p=habitaciones']
];

if (!isset($_SESSION['rol'])) {

    $_SESSION['rol'] = 'anonimo';

    $data['navegacion_login'][] = ['texto' => 'Register', 'url' => $_SERVER['SCRIPT_NAME'] . '?p=registrarse'];
    $data['navegacion_login'][] = ['texto' => 'Login', 'url' => $_SERVER['SCRIPT_NAME'] . '?p=iniciar_sesion'];
} else if ($_SESSION['rol'] == 'anonimo') {

    $data['navegacion_login'][] = ['texto' => 'Register', 'url' => $_SERVER['SCRIPT_NAME'] . '?p=registrarse'];
    $data['navegacion_login'][] = ['texto' => 'Login', 'url' => $_SERVER['SCRIPT_NAME'] . '?p=iniciar_sesion'];
} else if ($_SESSION['rol'] == 'Cliente') {

    $data['navegacion'][] = ['texto' => 'Tus reservas', 'url' => $_SERVER['SCRIPT_NAME'] . '?p=reservas'];
    $data['navegacion_login'][] = ['texto' => 'Ver perfil', 'url' => $_SERVER['SCRIPT_NAME'] . '?p=ver_perfil'];
    $data['navegacion_login'][] = ['texto' => 'Log out', 'url' => $_SERVER['SCRIPT_NAME'] . '?p=cerrar_sesion'];
} else if ($_SESSION['rol'] == 'Recepcionista') {

    $data['navegacion'][] = ['texto' => 'Usuarios', 'url' => $_SERVER['SCRIPT_NAME'] . '?p=ver_usuarios'];
    $data['navegacion'][] = ['texto' => 'Reservas', 'url' => $_SERVER['SCRIPT_NAME'] . '?p=reservas'];
    $data['navegacion_login'][] = ['texto' => 'Log out', 'url' => $_SERVER['SCRIPT_NAME'] . '?p=cerrar_sesion'];
} else if ($_SESSION['rol'] == 'Administrador') {

    $data['navegacion'][] = ['texto' => 'Usuarios', 'url' => $_SERVER['SCRIPT_NAME'] . '?p=ver_usuarios'];
    $data['navegacion'][] = ['texto' => 'Log', 'url' => $_SERVER['SCRIPT_NAME'] . '?p=log'];
    $data['navegacion'][] = ['texto' => 'BBDD', 'url' => $_SERVER['SCRIPT_NAME'] . '?p=base_datos'];
    $data['navegacion_login'][] = ['texto' => 'Log out', 'url' => $_SERVER['SCRIPT_NAME'] . '?p=cerrar_sesion'];
}


$acc = getAction($_GET);

$request = array();
$request['params'] = $_GET + $_POST;
$request['server'] = $_SERVER;
$request['files'] = $_FILES;

try {
    if ($acc['controlador'] == 'info') {
        $ct = new cInfo($data, $request);
        switch ($acc['metodo']) {
            case 'home':
                $ct->home();
                break;
            case 'servicios':
                $ct->servicios();
                break;
            case 'log':
                $ct->log();
                break;
            default:
                $ct->showinfo('Acción desconocida para este controlador');  // No debería ocurrir
                break;
        }
    } else if ($acc['controlador'] == 'usuario') {
        $ct = new cUsuario($data, $request);
        switch ($acc['metodo']) {
            case 'registrarse':
                $ct->registrarse();
                break;
            case 'iniciar_sesion':
                $ct->iniciar_sesion();
                break;
            case 'validate_inicio':
                $ct->validate_inicio();
                break;
            case 'validate_registro':
                $ct->validate_registro();
                break;
            case 'cerrar_sesion':
                $ct->cerrar_sesion();
                break;
            case 'ver_perfil':
                $ct->ver_perfil();
                break;
            case 'editar_datos_usuario':
                $ct->editar_datos_usuario();
                break;
            case 'ver_usuarios':
                $ct->ver_usuarios();
                break;
            case 'eliminar_usuario':
                $ct->eliminar_usuario();
                break;
            default:
                #$ct->showinfo('Acción desconocida para este controlador');  // No debería ocurrir
                break;
        }
    } else if ($acc['controlador'] == 'habitaciones') {
        $ct = new cHabitacion($data, $request);
        switch ($acc['metodo']) {
            case 'habitaciones':
                $ct->mostrar_habitaciones();
                break;
            case 'anadir_habitacion':
                $ct->anadir_habitacion();
                break;
            case 'validate_anadir_hab':
                $ct->validate_anadir_hab();
                break;
            case 'editar_habitacion':
                $ct->editar_habitacion();
                break;
            case 'borrar_habitacion':
                $ct->borrar_habitacion();
                break;
            case 'anadir_fotografia':
                $ct->anadir_fotografia();
                break;

            default:
                //$ct->showinfo('Acción desconocida para este controlador', 'error');  // No debería ocurrir
                break;
        }
    } else if ($acc['controlador'] == 'reserva') {
        $ct = new cReserva($data, $request);
        switch ($acc['metodo']) {
            case 'mostrar_reservas':
                $ct->mostrar_reservas();
                break;
            case 'anadir_reserva':
                $ct->anadir_reserva();
                break;
            case 'eliminar_reserva':
                $ct->eliminar_reserva();
                break;
            case 'editar_reserva':
                $ct->editar_reserva();
                break;

            default:
                #  $ct->showinfo('Acción desconocida para este controlador');  // No debería ocurrir
                break;
        }
    }
    else if ($acc['controlador']=='backup') {
       $ct = new cBackup($data,$request);
       switch ($acc['metodo']) {
        case 'base_datos':
            $ct->base_datos();
            break;
        default:
            $ct = new cInfo($data, $request);
            $ct->showinfo('Acción desconocida para este controlador');  // No debería ocurrir
            break;
        } 
    }
    else {
        $ct = new cInfo($data, $request);
        $ct->showerror('Se ha solicitado un controlador desconocido');  // No debería ocurrir
    }
} catch (Exception $e) {
    $ct = new cInfo($data, $request);
    $ct->showerror('ERROR: ' . $e->getMessage());
}


// Crear y devolver página resultante
echo $ct->render();


function getAction($p)
{
    $r = [];
    if (!isset($p['p'])) {
        $r['controlador'] = 'info';
        $r['metodo'] = 'home';
    } else
        switch ($p['p']) {
            case 'home':
                $r['controlador'] = 'info';
                $r['metodo'] = 'home';
                break;

            case 'servicios':
                $r['controlador'] = 'info';
                $r['metodo'] = 'servicios';
                break;

            case 'registrarse':
                $r['controlador'] = 'usuario';
                $r['metodo'] = 'registrarse';
                break;

            case 'iniciar_sesion':
                $r['controlador'] = 'usuario';
                $r['metodo'] = 'iniciar_sesion';
                break;

            case 'validate_inicio':
                $r['controlador'] = 'usuario';
                $r['metodo'] = 'validate_inicio';
                break;

            case 'validate_registro':
                $r['controlador'] = 'usuario';
                $r['metodo'] = 'validate_registro';
                break;

            case 'cerrar_sesion':
                $r['controlador'] = 'usuario';
                $r['metodo'] = 'cerrar_sesion';
                break;

            case 'ver_perfil':
                $r['controlador'] = 'usuario';
                $r['metodo'] = 'ver_perfil';
                break;

            case 'editar_datos_usuario':
                $r['controlador'] = 'usuario';
                $r['metodo'] = 'editar_datos_usuario';
                break;

            case 'habitaciones':
                $r['controlador'] = 'habitaciones';
                $r['metodo'] = 'habitaciones';
                break;

            case 'anadir_habitacion':
                $r['controlador'] = 'habitaciones';
                $r['metodo'] = 'anadir_habitacion';
                break;

            case 'validate_anadir_hab':
                $r['controlador'] = 'habitaciones';
                $r['metodo'] = 'validate_anadir_hab';
                break;

            case 'editar_habitacion':
                $r['controlador'] = 'habitaciones';
                $r['metodo'] = 'editar_habitacion';
                break;

            case 'anadir_fotografia':
                $r['controlador'] = 'habitaciones';
                $r['metodo'] = 'anadir_fotografia';
                break;

            case 'borrar_habitacion':
                $r['controlador'] = 'habitaciones';
                $r['metodo'] = 'borrar_habitacion';
                break;

            case 'log':
                $r['controlador'] = 'info';
                $r['metodo'] = 'log';
                break;

            case 'base_datos':
                $r['controlador'] = 'backup';
                $r['metodo'] = 'base_datos';
                break;

            case 'ver_usuarios':
                $r['controlador'] = 'usuario';
                $r['metodo'] = 'ver_usuarios';
                break;

            case 'eliminar_usuario':
                $r['controlador'] = 'usuario';
                $r['metodo'] = 'eliminar_usuario';
                break;

            case 'anadir_reserva':
                $r['controlador'] = 'reserva';
                $r['metodo'] = 'anadir_reserva';
                break;
            
            case 'eliminar_reserva':
                $r['controlador'] = 'reserva';
                $r['metodo'] = 'eliminar_reserva';
                break;

            case 'editar_reserva':
                $r['controlador'] = 'reserva';
                $r['metodo'] = 'editar_reserva';
                break;

            case 'reservas':
                $r['controlador'] = 'reserva';
                $r['metodo'] = 'mostrar_reservas';
                break;

            default:
                $r['controlador'] = 'info';
                $r['metodo'] = 'hello';
        }
    return $r;
}
