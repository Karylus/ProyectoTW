<?php
require_once '../src/Vista/html.php';
require_once '../src/Modelo/Reserva.php';
require_once '../src/Modelo/Habitacion.php';
require_once '../src/Modelo/Usuario.php';
require_once '../src/Modelo/Log.php';
require_once("../config/database.php");

class cReserva
{
    private $data;
    private $request;

    // constructor
    public function __construct($data, $request)
    {
        $this->data = $data;
        $this->request = $request;
    }

    // mostramos las habitaciones
    public function mostrar_reservas()
    {
        // comprobamos si tenemos en la base de datos reservas pendientes que hayan cadudado
        $this->eliminarReservasPendientesCaducadas();
        // para hacer el paginado
        $paginaActual = isset($_GET['pagina']) ? $_GET['pagina'] : 1;
        $numeroFilas = 5;
        $filaInicio = ($paginaActual - 1) * $numeroFilas;
        // dependiendo de si el usuario es cliente o recepcionista podrá ver solo las suyas o todas las reservas
        if ($_SESSION['rol'] == "Cliente") {
            // obtenemos reservas
            $reservas = Reserva::obtenerReservasID($_SESSION['id'], $filaInicio, $numeroFilas);
            if ($reservas[1] != 0) {
                $this->data['reserva'] = $reservas;
                $num_habitacion = [];
                foreach ($reservas[0] as $reserva) {
                    $id_hab = $reserva->devolverValor('id_habitacion');
                    $hab = Habitacion::obtenerHabitacionID("$id_hab");
                    $num_habitacion[$id_hab] = $hab->devolverValor('numero_habitacion');
                }
                $this->data['num_habitacion'] = $num_habitacion;

                $totalReservas = $reservas[1];
                $totalPaginas = ceil($totalReservas / $numeroFilas);
            
                $this->data['totalPaginas'] = $totalPaginas;
                $this->data['paginaActual'] = $paginaActual;
            } else {
                $this->data['motrar_error'] = "No tienes ninguna reserva";
            }
        } else {
            if ($_SESSION['rol'] == "Recepcionista" || $_SESSION['rol'] == "Administrador") {
                // obtenemos todas las reservas
                $reservas = Reserva::obtenerReservas($filaInicio, $numeroFilas, 'id_usuario');
                if ($reservas[1] != 0) {
                    $this->data['reserva'] = $reservas;
                    $num_habitacion = [];
                    $nombre_usuario = [];
                    foreach ($reservas[0] as $reserva) {
                        $id_hab = $reserva->devolverValor('id_habitacion');
                        $hab = Habitacion::obtenerHabitacionID("$id_hab");
                        $num_habitacion[$id_hab] = $hab->devolverValor('numero_habitacion');
                        $id_usuario = $reserva->devolverValor('id_usuario');
                        $usuario = Usuario::obtenerUsuarioID("$id_usuario");
                        $nombre_usuario[$id_usuario] = $usuario->devolverValor('nombre') . ' ' . $usuario->devolverValor('apellidos');
                    }

                    $totalReservas = $reservas[1];
                    $totalPaginas = ceil($totalReservas / $numeroFilas);

                    $this->data['num_habitacion'] = $num_habitacion;
                    $this->data['nombre_usuario'] = $nombre_usuario;
                    $this->data['totalPaginas'] = $totalPaginas;
                    $this->data['paginaActual'] = $paginaActual;
                } else {
                    $this->data['motrar_error'] = "No hay ninguna reserva en la BBDD";
                }
            }
        }

        $this->data['mostrar_reservas'] = "mostrar_reservas";
        $this->data['SCRIPT_NAME'] = $this->request['server']['SCRIPT_NAME'];
    }

    // añadir reserva
    public function anadir_reserva()
    {
        // solo pueden añadir reserva los Recepcionistas y los Clientes
        if($_SESSION['rol']=="Recepcionista" || $_SESSION['rol']=="Cliente"){
            // comprobamos si tenemos en la base de datos reservas pendientes que hayan cadudado
            $this->eliminarReservasPendientesCaducadas();
            // al añadir reserva hay dos fases, la primera es el formulario que reyena el usuario, y luego la pagina para preguntarle 
            // si quiere aceptar la reserva o no
            if ($this->request['params']['fase'] == "inicio" || isset($this->request['params']['cancelar'])) {
                // en el caso de que no quiera la habitacion que le hemos asignado, borraremos la reserva de la BBDD
                if (isset($this->request['params']['cancelar'])) {
                    Reserva::eliminarReserva($this->request['params']['cancelar']);
                    Log::insertarLog("Se ha cancelado la reserva con id: " . $this->request['params']['cancelar']);
                    $this->request['params']['fase'] = "null";
                }
                // si completa el primer formulario pasamos a buscar la habitacion para el usuario
                if (isset($this->request['params']['confirmar'])) {
                    //creamos reserva si es que se hay alguna habitacion disponible, valida
                    //// buscamos las habitaciones no son validas, que tienen reservas sobre las fechas que el usuario ha indicado
                    //// dentro de las reservas se añaden tambien los mantenimientos
                    $reservas = Reserva::obtenerTodasReservas();
                    $hab_no_validas = [];
                    foreach ($reservas[0] as $reserva) {
                        if ($this->request['params']['fecha_inicio'] <= $reserva->devolverValor('fecha_fin') && $this->request['params']['fecha_fin'] >= $reserva->devolverValor('fecha_inicio')) {
                            $hab_no_validas[] = $reserva->devolverValor('id_habitacion');
                        }
                    }
                    //// Para las habitaciones que se indiquen que van a esta en mantenimiento
                    if ($_SESSION['rol'] == "Recepcionista" and $this->request['params']['num_habitacion'] != "Ninguna") {
                        // la obtenemos de la base de datos y comprobamos que se pueda poner en mantenimeinto, es decir, que no hayas reservas ya
                        $habitacion = Habitacion::obtenerHabitacion($this->request['params']['num_habitacion']);
                        $id_hab = $habitacion->devolverValor('id');
                        if (!in_array($id_hab, $hab_no_validas)) {
                            $this->data['mantenimiento'] = "mantenimiento";
                            $this->data['id_hab_encontrada'] = $id_hab;
                            $this->data['id_reserva_creada'] = Reserva::insertarReserva(
                                $this->request['params']['id_usuario'],
                                $this->data['id_hab_encontrada'],
                                $this->request['params']['fecha_inicio'],
                                $this->request['params']['fecha_fin'],
                                $this->request['params']['comentarios'],
                                $this->request['params']['n_personas']
                            );
                            $usuario = Usuario::obtenerUsuarioID($this->request['params']['id_usuario']);
                            Log::insertarLog("Se ha puesto en mantenimiento la habitacion: " . $this->request['params']['num_habitacion']);
                            $this->request['params']['fase'] = "segunda";
                        } else {
                            $this->data['error'] = "La habitacion que pretendes poner en mantenimiento está reservada por un cliente";
                        }
                    } else {
                        //// buscamos la primera que cumpla mejor las necesidades de capacidad
                        //// obtenerHabitacionAjustada -> devuelve las habitaciones que superen la capacidad ordenadas de menor a mayor en capacidad
                        $habitaciones_orden_mejor = Habitacion::obtenerHabitacionAjustada($this->request['params']['n_personas']);
                        if ($habitaciones_orden_mejor[1] != 0) {
                            foreach ($habitaciones_orden_mejor[0] as $habitacion) {
                                // buscamos la primera en estas que sea valida
                                if (!in_array($habitacion->devolverValor('id'), $hab_no_validas)) {
                                    $this->data['id_hab_encontrada'] = $habitacion->devolverValor('id');
                                    break;
                                }
                            }
                            if (isset($this->data['id_hab_encontrada'])) {
                                // creamos reserva
                                $this->data['id_reserva_creada'] = Reserva::insertarReserva(
                                    $this->request['params']['id_usuario'],
                                    $this->data['id_hab_encontrada'],
                                    $this->request['params']['fecha_inicio'],
                                    $this->request['params']['fecha_fin'],
                                    $this->request['params']['comentarios'],
                                    $this->request['params']['n_personas']
                                );
                                $usuario = Usuario::obtenerUsuarioID($this->request['params']['id_usuario']);
                                Log::insertarLog("Se ha creado una reserva temporalmente para el usuario " . $usuario->devolverValor('nombre') . ' ' . $usuario->devolverValor('apellidos'));
                                $this->request['params']['fase'] = "segunda";
                            } else {
                                $this->data['error'] = "No hay ninguna habitación disponible en las fechas marcadas, lo sentimos mucho.";
                            }
                        } else {
                            $this->data['error'] = "No hay ninguna habitación con la capacidad que necesitas, lo sentimos mucho.";
                        }
                    }
                } else {
                    // primer formulario que tiene que reyenarse para realizar la reserva
                    // si es recepcionista tenemos que mostrar todos los usuarios y las habitaciones para el caso de los mantenimientos
                    if ($_SESSION['rol'] == 'Recepcionista') {
                        $usuarios = Usuario::obtenerTodosUsuarios();
                        foreach ($usuarios[0] as $usuario) {
                            $this->data['datos_usuario'][] = $usuario->devolverValor('dni');
                        }
                        $habitaciones = Habitacion::obtenerTodasHabitaciones();
                        $this->data['datos_habitaciones'][] = "Ninguna";
                        foreach ($habitaciones[0] as $habitacion) {
                            $this->data['datos_habitaciones'][] = $habitacion->devolverValor('numero_habitacion');
                        }
                    }
                    // Comprobamos que todos los parametros del formulario esten correctos
                    if (isset($this->request['params']['n_personas'])) {
                        if ($this->request['params']['n_personas'] == "") {
                            $this->data['anadir_reserva']['n_personas_error'] = "Es obligatorio indicar el número de personas";
                            $this->data['anadir_reserva']['error'] = "Hay error";
                            $this->data['n_personas'] = "";
                        } else $this->data['n_personas'] = htmlentities(trim($this->request['params']['n_personas']));
                    } else {
                        $this->data['n_personas'] = "";
                        $this->data['anadir_reserva']['error'] = "Hay error";
                    }

                    if (isset($this->request['params']['fecha_inicio'])) {
                        if ($this->request['params']['fecha_inicio'] == "") {
                            $this->data['anadir_reserva']['fecha_inicio_error'] = "Es obligatorio indicar la fecha de llegada";
                            $this->data['anadir_reserva']['error'] = "Hay error";
                            $this->data['fecha_inicio'] = "";
                        } else $this->data['fecha_inicio'] = $this->request['params']['fecha_inicio'];
                    } else $this->data['fecha_inicio'] = "";

                    if (isset($this->request['params']['fecha_fin'])) {
                        if ($this->request['params']['fecha_fin'] == "") {
                            $this->data['anadir_reserva']['fecha_fin_error'] = "Es obligatorio indicar el la fecha de salida";
                            $this->data['anadir_reserva']['error'] = "Hay error";
                            $this->data['fecha_fin'] = "";
                        } else $this->data['fecha_fin'] = $this->request['params']['fecha_fin'];
                    } else $this->data['fecha_fin'] = "";

                    if (isset($this->request['params']['comentarios'])) {
                        $this->data['comentarios'] = htmlentities(trim($this->request['params']['comentarios']));
                    } else $this->data['comentarios'] = "";


                    if (!isset($this->data['anadir_reserva']['error'])) {
                        $this->data['NoEditable'] = 'NoEditable';
                        $this->data['anadir_reserva'] = "anadir_reserva";

                        if ($_SESSION['rol'] == "Recepcionista") {
                            $dni_usuario = $this->request['params']['dni_usuario'];
                            $this->data['dni_usuario'] = $dni_usuario;
                            $this->data['id_usuario'] = Usuario::obtenerUsuario($dni_usuario)->devolverValor('id');
                            if ($this->request['params']['num_habitacion'] != "Ninguna") {
                                $num_hab = $this->request['params']['num_habitacion'];
                                $this->data['num_habitacion'] = $num_hab;
                                $this->data['id_habitacion'] = Habitacion::obtenerHabitacion($num_hab)->devolverValor('id');
                            } else $this->data['num_habitacion'] = "Ninguna";
                        }
                    }
                }
            }
            // segunda fase del proceso donde mostramos la habitacion encontrada y preguntamos al usuario si la acepta o no
            if ($this->request['params']['fase'] == "segunda") {

                if (isset($this->request['params']['aceptar'])) {

                    //comprobamos que la reserva siga siendo válida y la aceptamos
                    $reserva = Reserva::obtenerIDReserva($this->request['params']['aceptar']);
                    $marca_tiempo_inicio = $reserva->devolverValor('marca_tiempo');
                    $marca_tiempo_inicio = new DateTime($marca_tiempo_inicio);
                    $marca_tiempo_actual = new DateTime();
                    $diferencia = $marca_tiempo_actual->diff($marca_tiempo_inicio);
                    $segundosTranscurridos = ($diferencia->days * 24 * 60 * 60) +
                        ($diferencia->h * 60 * 60) +
                        ($diferencia->i * 60) +
                        $diferencia->s;
                    if ($segundosTranscurridos <= 30) {
                        $id_usuario_reserva = $reserva->devolverValor('id_usuario');
                        $rol_usuario = Usuario::obtenerUsuarioID($id_usuario_reserva)->devolverValor('rol');
                        if ($rol_usuario == "Recepcionista") {
                            Reserva::modificarEstadoReserva($this->request['params']['aceptar'], 'Mantenimiento');
                            $_SESSION['info'] = "Se ha puesto la reserva en mantenimiento";
                        } else {
                            Reserva::modificarEstadoReserva($this->request['params']['aceptar'], 'Confirmada');
                            $_SESSION['info'] = "Se ha creado la reserva correctamente";
                        }
                        $usuario = Usuario::obtenerUsuarioID($reserva->devolverValor('id_usuario'));
                        Log::insertarLog("Se ha confirmado la reserva del usuario " . $usuario->devolverValor('nombre') . ' ' . $usuario->devolverValor('apellidos'));
                        header('Location: ' . $this->request['server']['SCRIPT_NAME'] . '?p=reservas');
                        exit();
                    } else {
                        Reserva::eliminarReserva($this->request['params']['aceptar']);
                        $this->data['error'] = "Lo sentimos mucho, el tiempo para hacer la reserva ha expirado, comienza de nuevo.";
                    }
                } else {

                    $id_hab = $this->data['id_hab_encontrada'];
                    $this->data['n_personas'] = $this->request['params']['n_personas'];
                    $this->data['fecha_inicio'] = $this->request['params']['fecha_inicio'];
                    $this->data['fecha_fin'] = $this->request['params']['fecha_fin'];
                    $this->data['comentarios'] = $this->request['params']['comentarios'];
                    $this->data['numero_hab'] = Habitacion::obtenerHabitacionID($id_hab)->devolverValor('numero_habitacion');
                    $this->data['precio'] = Habitacion::obtenerHabitacionID($id_hab)->devolverValor('precio_noche');
                    $this->data['confirmar_reserva'] = "confirmar_reserva";
                }
            }

            $this->data['SCRIPT_NAME'] = $this->request['server']['SCRIPT_NAME'];
        }else $this->data['error'] = "No tienes privilegios para añadir una reserva";
    }

    public function eliminar_reserva()
    {
        // eliminar las reservas, para ello el recepcionista peuede eliminar todas, pero el cliente solo las suyas
        $id = $this->request['params']['id'];
        if($_SESSION['rol']=="Recepcionista"){
            Reserva::eliminarReserva($id);
            //Registro la trasacción en el log
            Log::insertarLog('Eliminación de la Reserva: ' . $id);
            $_SESSION['info'] = "Se ha eliminado correctamente la reserva";
            header('Location: ' . $this->request['server']['SCRIPT_NAME'] . '?p=reservas');
            exit();
        }else if($_SESSION['rol']=="Cliente"){
            $reserva = Reserva::obtenerIDReserva($id);
            if($_SESSION['id']==$reserva->devolverValor('id_usuario')){
                Reserva::eliminarReserva($id);
                //Registro la trasacción en el log
                Log::insertarLog('Eliminación de la Reserva: ' . $id);
                $_SESSION['info'] = "Se ha eliminado correctamente la reserva";
                header('Location: ' . $this->request['server']['SCRIPT_NAME'] . '?p=reservas');
                exit();
            }else $this->data['error'] = "No puedes modificar las reservas de otro usuario";
        }else $this->data['error'] = "No tienes privilegios para añadir una reserva";
    }

    public function editar_reserva()
    {
        // editar los comentarios de una reserva, para ello el recepcionista puede hacerlo en todas las reservas, pero
        // el cliente solo puedo eliminar las suyas
        $id_reserva = $this->request['params']['id'];
        $reserva = Reserva::obtenerIDReserva($id_reserva);
        $usuario = Usuario::obtenerUsuarioID($reserva->devolverValor('id_usuario'));
        $id_usuario = $usuario->devolverValor('id');

        $this->data['reserva'] = $reserva;
        $this->data['SCRIPT_NAME'] = $this->request['server']['SCRIPT_NAME'];

        if ($_SESSION['id'] == $id_usuario || $_SESSION['rol'] == 'Recepcionista') {

            if (isset($this->request['params']['comentarios'])) {
                $this->data['comentarios'] = htmlentities(trim($this->request['params']['comentarios']));
                // cuando el usuario haya seleccionado enviar el formulario se le preguntara por la confirmacion
                if (isset($this->request['params']['Enviar'])) {
                    $this->data['editar_reserva'] = true;
                    $this->data['no_editar'] = true;
                } else if (isset($this->request['params']['Confirmar'])) {
                    // una vez que confirme modificamos la reserva
                    Reserva::modificarComentariosReserva($id_reserva, $this->data['comentarios']);
                    $_SESSION['info'] = "Datos de la reserva editados correctamente";
                    $descripcion = "El usuario del sistema con DNI: " . $_SESSION['dni'] . " ha editado los datos de la reserva con id: " . $id_reserva;
                    Log::insertarLog($descripcion);
                    header('Location: ' . $this->request['server']['SCRIPT_NAME'] . '?p=reservas');
                    exit();
                }
            } else {
                $this->data['editar_reserva'] = true;
                $this->data['comentarios'] = $reserva->devolverValor('comentarios');
            }
        } else $this->data['error'] = "No tienes privilegios para editar esta reserva";
    }

    public function eliminarReservasPendientesCaducadas()
    {
        // metodo para eliminar las reservas en pendiente que hayan excedido el tiempo máximo de de 30 segundos
        $reservas = Reserva::obtenerTodasReservas();
        // para cada una de las reservas de la base de datos en pendiente calculamos el tiempo transcurrido desde su creacion
        foreach ($reservas[0] as $reserva) {
            if ($reserva->devolverValor('estado') == "Pendiente") {
                $id_reserva = $reserva->devolverValor('id');
                $marca_tiempo_inicio = $reserva->devolverValor('marca_tiempo');
                $marca_tiempo_inicio = new DateTime($marca_tiempo_inicio);
                $marca_tiempo_actual = new DateTime();
                $diferencia = $marca_tiempo_actual->diff($marca_tiempo_inicio);
                $segundosTranscurridos = ($diferencia->days * 24 * 60 * 60) +
                    ($diferencia->h * 60 * 60) +
                    ($diferencia->i * 60) +
                    $diferencia->s;
                if ($segundosTranscurridos > 30) {

                    Log::insertarLog('Eliminación de la Reserva por exceso de tiempo: ' . $id_reserva);
                    Reserva::eliminarReserva($id_reserva);
                }
            }
        }
    }

    public function render()
    {
        return HTMLrenderWeb($this->data);
    }
}
