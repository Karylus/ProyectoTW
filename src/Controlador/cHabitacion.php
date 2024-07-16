<?php
require_once '../src/Vista/html.php';
require_once '../src/Modelo/Habitacion.php';
require_once '../src/Modelo/Log.php';
require_once("../config/database.php");

class cHabitacion
{
    private $data;
    private $request;

    // constructror de la clase
    public function __construct($data, $request)
    {
        $this->data = $data;
        $this->request = $request;
    }

    // funcion para mostrar las habitaciones del hotel, pueden acceder todos los usuarios
    public function mostrar_habitaciones()
    {
        $habitaciones = Habitacion::obtenerTodasHabitaciones();
        $this->data['habitaciones'] = $habitaciones;
        $imagenes = Habitacion::obtenerImagenes();
        $this->data['imagenes'] = $imagenes;
        $this->data['SCRIPT_NAME'] = $this->request['server']['SCRIPT_NAME'];
    }

    // añadir habitacion al hotel, unicamente el recepcionista puede, sólo se accede al formulario para añadir la habitacion
    public function anadir_habitacion()
    {
        if ($_SESSION['rol'] == "Recepcionista") {
            $this->data['anadir_habitacion'] = "anadir_habitacion";
            $this->data['SCRIPT_NAME'] = $this->request['server']['SCRIPT_NAME'];
        } else $this->data['error'] = "No tienes privilegios para poder añadir una habitación";
    }

    // para comprobar el formulario estilo sticky
    public function validate_anadir_hab()
    {
        if($_SESSION['rol'] != "Recepcionista"){

            $this->data['error'] = "No tienes privilegios para añadir una habitacion";
        }
        else{ if (isset($this->request['params']['confirmar'])) {
                // Si confirma el usuario que quiere añadir la habitacion se crea la tupla en la BBDD
                Habitacion::insertarHabitacion(
                    NULL,
                    $this->request['params']['numero_hab'],
                    $this->request['params']['capacidad'],
                    $this->request['params']['precio'],
                    $this->request['params']['descripcion']
                );
                //Registro la transacción en el log
                $descripcion = "Se ha añadido una nueva habitación con número " . $this->request['params']['numero_hab'];
                Log::insertarLog($descripcion);
                //Tras añadir la habitacion se informa al usuario y se muestra de nuevo todas las habitaciones, haciendo un header oara evitar reenviar el formulario
                $_SESSION['info'] = "Se ha añadido la habitación correctamente";
                header('Location: ' . $this->request['server']['SCRIPT_NAME'] . '?p=habitaciones');
                exit();
            } else {
                // si todavia no ha confirmado por que no estan todos los datos bien
                // vamos leyendo los datos y comprobando que sean validos para en el caso de que no lo sean informar al usuario
                if (isset($this->request['params']['numero_hab'])) {
                    if ($this->request['params']['numero_hab'] == "") {
                        $this->data['anadir_habitacion']['numero_hab_error'] = "Es obligatorio indicar el número de habitación";
                        $this->data['anadir_habitacion']['error'] = "Hay error";
                    } else {
                        $habitacion = Habitacion::obtenerHabitacion($this->request['params']['numero_hab']);
                        if ($habitacion) {
                            if($habitacion->devolverValor('numero_habitacion')==$this->request['params']['numero_hab']){
                                $this->data['anadir_habitacion']['numero_hab_error'] = "Esa habitacion ya está registrada";
                                $this->data['anadir_habitacion']['error'] = "Hay error";
                            }
                        }
                    }
                }
                if ($this->request['params']['capacidad'] == "") {
                    $this->data['anadir_habitacion']['capacidad_error'] = "Es obligatorio indicar la capacidad de la habitación";
                    $this->data['anadir_habitacion']['error'] = "Hay error";
                }
                if ($this->request['params']['precio'] == "") {
                    $this->data['anadir_habitacion']['precio_error'] = "Es obligatorio indicar el precio de la habitación";
                    $this->data['anadir_habitacion']['error'] = "Hay error";
                }
                if ($this->request['params']['descripcion'] == "") {
                    $this->data['anadir_habitacion']['descripcion_error'] = "Es obligatorio indicar la descripcion de la habitación";
                    $this->data['anadir_habitacion']['error'] = "Hay error";
                }
                if (!isset($this->data['anadir_habitacion']['error'])) $this->data['NoEditable'] = 'NoEditable';
                $this->data['params'] = $this->request['params'];
                $this->data['anadir_habitacion_validate'] = "anadir_habitacion_validate";
                $this->data['SCRIPT_NAME'] = $this->request['server']['SCRIPT_NAME'];
            }
        }
    }

    // borrar habitacion 
    public function borrar_habitacion()
    {
        if ($_SESSION['rol'] == "Recepcionista") {
            if (isset($this->request['params']['numero_habitacion'])) {
                $numero_habitacion = $this->request['params']['numero_habitacion'];
                $habitacion = Habitacion::obtenerHabitacion($numero_habitacion);
                if ($habitacion) {
                    Habitacion::eliminarImagenes($habitacion->devolverValor('id'));
                    Habitacion::eliminarHabitacion($numero_habitacion);
                    Log::insertarLog('Eliminación de la habitación número: ' . $numero_habitacion);
                    $_SESSION['info'] = "Se ha eliminado la habitación correctamente";
                    header('Location: ' . $this->request['server']['SCRIPT_NAME'] . '?p=habitaciones');
                    exit();
                } else $this->data['error'] = "No existe la habitación que desea eliminar";
            } else $this->data['error'] = 'No se ha pasado el parámetro "numero_habitacion" necesario para eliminar una habitacion';
        } else $this->data['error'] = "No tienes privilegios para poder eliminar una habitación";
    }

    // editar los datos de una habitacion
    public function editar_habitacion()
    {
        if($_SESSION['rol'] != "Recepcionista"){

            $this->data['error'] = "No tienes privilegios para añadir una habitacion";
        }else{
            $numero_anterior = $this->request['params']['numero_hab_anterior'];
            $this->data['numero_hab_anterior'] = $numero_anterior;

            if (isset($this->request['params']['confirmar'])) {
                // si el usuario confirma los datos editados regustramos la operacion y cambiamos la tupla de la BBDD
                //Registro la transacción en el log
                if ($numero_anterior != $this->request['params']['numero_hab']) {
                    $descripcion = "Se ha editado la habitación que antes era la " . $numero_anterior . " y ahora pasa a ser la " . $this->request['params']['numero_hab'];
                    Log::insertarLog($descripcion);
                } else {
                    $descripcion = "Se ha editado la información de la habitación " . $numero_anterior;
                    Log::insertarLog($descripcion);
                }

                $habitacion = Habitacion::obtenerHabitacion($numero_anterior);

                Habitacion::modificarHabitacion(
                    $habitacion->devolverValor('id'),
                    $this->request['params']['numero_hab'],
                    $this->request['params']['capacidad'],
                    $this->request['params']['precio'],
                    $this->request['params']['descripcion']
                );
                // Si todo ha ido bien informamos y usamos header para evitar el reenvio del formulario
                $_SESSION['info'] = "Se ha añadido editado los datos correctamente";
                header('Location: ' . $this->request['server']['SCRIPT_NAME'] . '?p=habitaciones');
                exit();
            } else {

                $habitacion = Habitacion::obtenerHabitacion($numero_anterior);

                if (!isset($this->request['params']['numero_hab'])) {
                    $this->request['params']['numero_hab'] = $habitacion->devolverValor('numero_habitacion');
                    $this->data['anadir_habitacion']['error'] = "Hay error";
                }

                if ($this->request['params']['numero_hab'] == "") {
                    $this->data['anadir_habitacion']['numero_hab_error'] = "Es obligatorio indicar el número de habitación";
                    $this->data['anadir_habitacion']['error'] = "Hay error";
                } else if ($this->request['params']['numero_hab'] != $numero_anterior) {
                    $habitacion = Habitacion::obtenerHabitacion($this->request['params']['numero_hab']);
                    if ($habitacion) {
                        if($habitacion->devolverValor('numero_habitacion')==$this->request['params']['numero_hab']){
                            $this->data['anadir_habitacion']['numero_hab_error'] = "Esa habitacion ya está registrada";
                            $this->data['anadir_habitacion']['error'] = "Hay error";
                        }
                    }
                }
                if (!isset($this->request['params']['capacidad']))
                    $this->request['params']['capacidad'] = $habitacion->devolverValor('capacidad');

                if ($this->request['params']['capacidad'] == "") {
                    $this->data['anadir_habitacion']['capacidad_error'] = "Es obligatorio indicar la capacidad de la habitación";
                    $this->data['anadir_habitacion']['error'] = "Hay error";
                }

                if (!isset($this->request['params']['precio']))
                    $this->request['params']['precio'] = $habitacion->devolverValor('precio_noche');

                if ($this->request['params']['precio'] == "") {
                    $this->data['anadir_habitacion']['precio_error'] = "Es obligatorio indicar el precio de la habitación";
                    $this->data['anadir_habitacion']['error'] = "Hay error";
                }

                if (!isset($this->request['params']['descripcion']))
                    $this->request['params']['descripcion'] = $habitacion->devolverValor('descripcion');

                if ($this->request['params']['descripcion'] == "") {
                    $this->data['anadir_habitacion']['descripcion_error'] = "Es obligatorio indicar la descripcion de la habitación";
                    $this->data['anadir_habitacion']['error'] = "Hay error";
                }
                if (!isset($this->data['anadir_habitacion']['error'])) $this->data['NoEditable'] = 'NoEditable';
                $this->data['params'] = $this->request['params'];
                $this->data['editar_habitacion_validate'] = "editar_habitacion_validate";
                $this->data['SCRIPT_NAME'] = $this->request['server']['SCRIPT_NAME'];
            }
        }
    }

    public function anadir_fotografia()
    {
        if ($_SESSION['rol'] == "Recepcionista") {
            $this->data['numero_habitacion'] = $this->request['params']['numero_habitacion'];
            $habitacion = Habitacion::obtenerHabitacion($this->data['numero_habitacion']);

            $idHabitacion = $habitacion->devolverValor('id');
            $numHabitacion = $habitacion->devolverValor('numero_habitacion');

            if (isset($_FILES['imagen'])) {

                $carpeta_imagenes = "../public/images/habitaciones/";

                if (!file_exists($carpeta_imagenes)) {
                    mkdir($carpeta_imagenes, 0777, true);
                }

                // Iterar sobre cada archivo
                for ($i = 0; $i < count($_FILES['imagen']['name']); $i++) {
                    // Generamos 5 letras aleatorias par el nombre de la imagen
                    $random_string = substr(str_shuffle("abcdefghijklmnopqrstuvwxyz"), 0, 5);
                    $nombre_archivo = $numHabitacion . '_' . $random_string;

                    // Cogemos la extension de la imagen
                    $tipo_imagen = strtolower(pathinfo($_FILES["imagen"]["name"][$i], PATHINFO_EXTENSION));

                    // Combinamos el nombre de la imagen con su extension
                    $fichero_imagen = $carpeta_imagenes . basename($nombre_archivo . '.' . $tipo_imagen);

                    // Comprobamos si el archivo es una imagen
                    $check = getimagesize($_FILES["imagen"]["tmp_name"][$i]);
                    $subida_correcta = ($check !== false) ? 1 : 0;

                    // Comprobamos el tamaño de la imagen
                    $subida_correcta = ($_FILES["imagen"]["size"][$i] > 500000) ? 0 : $subida_correcta;

                    // Solo permitimos ciertos tipos de archivos
                    $allowed_formats = ["jpg", "png", "jpeg", "gif", "webp"];
                    $subida_correcta = (in_array($tipo_imagen, $allowed_formats)) ? $subida_correcta : 0;

                    // Si $subida_correcta es 1, movemos el archivo y lo insertamos en la BBDD
                    if ($subida_correcta == 1) {
                        if (move_uploaded_file($_FILES["imagen"]["tmp_name"][$i], $fichero_imagen)) {
                            Habitacion::insertarImagen($idHabitacion, $fichero_imagen);
                            //Registro la transacción en el log
                            $descripcion = "Se ha añadido una nueva imagen a la habitación con número " . $numHabitacion;
                            Log::insertarLog($descripcion);
                            $_SESSION['info'] = "Se ha añañido la imagen correctamente";
                            header('Location: ' . $this->request['server']['SCRIPT_NAME'] . '?p=habitaciones');
                            exit();
                        }
                    }
                }
    
            } else {
                $this->data['anadir_fotografia'] = "anadir_fotografia";
                $this->data['SCRIPT_NAME'] = $this->request['server']['SCRIPT_NAME'];
            }
        } else return _HTMLshowInfo('No tienes permisos para acceder a esta página', 'error');
    }

    public function render()
    {
        return HTMLrenderWeb($this->data);
    }
}
