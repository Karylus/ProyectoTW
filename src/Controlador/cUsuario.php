<?php
require_once '../src/Vista/html.php';
require_once '../src/Modelo/Usuario.php';
require_once '../src/Modelo/Reserva.php';
require_once '../src/Modelo/Log.php';

class cUsuario
{
    private $data;
    private $request;

    public function __construct($data, $request)
    {
        $this->data = $data;
        $this->request = $request;
    }

    public function registrarse()
    {
        $this->data['registrarse'] = "registrarse";
        $this->data['SCRIPT_NAME'] = $this->request['server']['SCRIPT_NAME'];
    }

    public function iniciar_sesion()
    {
        $this->data['iniciar_sesion'] = "iniciar_sesion";
        $this->data['SCRIPT_NAME'] = $this->request['server']['SCRIPT_NAME'];
    }

    public function validate_inicio()
    {
        if (isset($this->request['params']['email']) and isset($this->request['params']['clave'])) {

            $email = trim($this->request['params']['email']);
            $clave = trim($this->request['params']['clave']);

            //Si el email o la clave están vacíos
            if ($email == "") {
                $this->data['errores']['email'] = "El email no puede estar vacio";
            }
            if ($clave == "") {
                $this->data['errores']['clave'] = "La clave no puede estar vacia";
            }

            $usuario = Usuario::obtenerUsuarioEmail($email);

            //Si el email no existe
            if (!$usuario) {
                $this->data['errores']['email'] = "El email no se ha registrado";
            }
            //Si el email existe
            else {
                //Si la clave no es correcta
                if (!password_verify($clave, $usuario->devolverValor('clave'))) {
                    $this->data['errores']['clave'] = "La clave no es correcta";
                }
                //Si la clave es correcta
                else {
                    $_SESSION['dni'] = $usuario->devolverValor('dni');
                    $_SESSION['rol'] = $usuario->devolverValor('rol');
                    $_SESSION['id'] = $usuario->devolverValor('id');

                    //Registro la trasacción en el log
                    Log::insertarLog('Inicio de sesión: ' . $_SESSION['dni'] . ' ' . $_SESSION['rol']);

                    header('Location: index.php?p=home');
                    exit();
                }
            }

            if (isset($this->data['errores'])) {
                //Registro en el log el fallo
                Log::insertarLog('Inicio de sesión fallido');

                $this->data['iniciar_sesion'] = "iniciar_sesion";
                $this->data['SCRIPT_NAME'] = $this->request['server']['SCRIPT_NAME'];
            }
        }
    }

    public function validate_registro()
    {
        // Verifica si todos los campos necesarios están presentes en la solicitud
        if (
            isset($this->request['params']['email']) and isset($this->request['params']['clave']) and
            isset($this->request['params']['nombre']) and isset($this->request['params']['apellidos']) and
            isset($this->request['params']['dni']) and isset($this->request['params']['numero_tarjeta'])
        ) {
            // Si todos los campos están presentes, los recoge y los limpia de espacios en blanco
            $email = htmlentities(trim($this->request['params']['email']));
            $clave = htmlentities(trim($this->request['params']['clave']));
            $nombre = htmlentities(trim($this->request['params']['nombre']));
            $apellidos = htmlentities(trim($this->request['params']['apellidos']));
            $dni = htmlentities(trim($this->request['params']['dni']));
            $numero_tarjeta = htmlentities(trim($this->request['params']['numero_tarjeta']));
            $guardar = isset($this->request['params']['guardar']) ? true : false;

            // Comienza a validar cada campo
            // Verifica si el email es válido
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->data['errores']['email'] = "Introduce un email válido";
            }

            // Verifica si el email ya está registrado
            $usuario = Usuario::obtenerUsuarioEmail($email);
            if ($usuario) {
                $this->data['errores']['email'] = "El email ya está registrado";
            }

            // Verifica si el DNI es válido
            if (!preg_match('/^[0-9]{8}[A-Z]$/', $dni)) {
                $this->data['errores']['dni'] = "El formato del DNI no es válido";
            } else {
                // Si el formato del DNI es correcto, verifica si la letra del DNI es válida
                $letra = substr($dni, -1);
                $numeros = substr($dni, 0, -1);
                if ($letra !== $this->calcularLetraDNI($numeros)) {
                    $this->data['errores']['dni'] = "La letra del DNI no es válida";
                }
            }

            // Verifica si el DNI ya está registrado
            $usuario = Usuario::obtenerUsuario($dni);
            if ($usuario) {
                $this->data['errores']['dni'] = "El dni ya está registrado";
            }

            // Verifica si el número de tarjeta es válido
            if (!preg_match('/^[0-9]{16}$/', $numero_tarjeta)) {
                $this->data['errores']['numero_tarjeta'] = "El formato del número de tarjeta no es válido";
            } else if (!$this->validarTarjeta($numero_tarjeta)) {
                $this->data['errores']['numero_tarjeta'] = "El número de tarjeta no es válido";
            }

            // Verifica si la clave tiene al menos 3 caracteres
            if (strlen($clave) < 3) {
                $this->data['errores']['clave'] = "La clave debe tener al menos 3 caracteres";
            }

            // Prepara un array con los nombres de los campos para su uso en mensajes de error
            $campos_vacios = [
                'email' => 'El email',
                'clave' => 'La clave',
                'nombre' => 'El nombre',
                'apellidos' => 'Los apellidos',
                'dni' => 'El dni',
                'numero_tarjeta' => 'El numero de tarjeta'
            ];

            foreach ($campos_vacios as $campo => $campo_nombre) {
                if ($$campo == "") {
                    $this->data['errores'][$campo] = "$campo_nombre no puede estar vacio";
                }
            }
            // Si no hay errores y es la primera vez que se envía, solo hacer la vista previa
            if (empty($this->data['errores']) && !isset($_SESSION['formulario_enviado'])) {
                $campos = ['email', 'clave', 'nombre', 'apellidos', 'dni', 'numero_tarjeta'];
                foreach ($campos as $campo) {
                    $_SESSION[$campo] = $$campo;  // Guardar los valores en la sesión
                    $this->data['params'][$campo] = $$campo;  // Guardar los valores en data
                }

                $_SESSION['formulario_enviado'] = true;

                $this->data['registrarse'] = "registrarse";
                $this->data['SCRIPT_NAME'] = $this->request['server']['SCRIPT_NAME'];
            }

            // Si no hay errores y ya se envió una vez, registrar en la base de datos
            else if (empty($this->data['errores']) && isset($_SESSION['formulario_enviado']) && $guardar) {

                $idusuario = Usuario::insertarUsuario($nombre, $apellidos, $dni, $email, $clave, $numero_tarjeta, 'Cliente');

                $this->data['info'] = "Usuario registrado correctamente";
                
                if (!isset($_SESSION['rol']) || $_SESSION['rol'] == 'anonimo') {
                    $_SESSION['rol'] = 'Cliente';
                }
                if (!isset($_SESSION['dni'])) {
                    $_SESSION['dni'] = $dni;
                }
                if (!isset($_SESSION['id'])) {
                    $_SESSION['id'] = $idusuario;
                }

                //Registro la trasacción en el log
                Log::insertarLog('Registro de Usuario: ' . $_SESSION['dni'] );

                $_SESSION['info'] = "Usuario registrado correctamente";

                unset($_SESSION['formulario_enviado']);  // Limpiar la sesión
                header('Location: index.php?p=home');  // Redirigir al home después del registro
                exit();
            }

            // Si hay errores, permitir edición
            if (!empty($this->data['errores'])) {
                unset($_SESSION['formulario_enviado']);  // Limpiar la variable de sesión
            }

            $campos = ['email', 'clave', 'nombre', 'apellidos', 'dni', 'numero_tarjeta'];
            foreach ($campos as $campo) {
                $this->data['params'][$campo] = !isset($this->data['errores'][$campo]) ? $$campo : '';
            }

            $this->data['registrarse'] = "registrarse";
            $this->data['SCRIPT_NAME'] = $this->request['server']['SCRIPT_NAME'];
        }
    }

    private function calcularLetraDNI($dni)
    {
        $letras = "TRWAGMYFPDXBNJZSQVHLCKE";
        return $letras[$dni % 23];
    }

    private function validarTarjeta($numeros)
    {
        $suma = 0;
        $numDigitos = strlen($numeros) - 1;
        $paridad = $numDigitos % 2;

        for ($i = $numDigitos; $i >= 0; $i--) {
            $digito = $numeros[$i];

            if (!$paridad == ($i % 2)) {
                $digito <<= 1;
            }

            $digito = ($digito > 9) ? ($digito - 9) : $digito;
            $suma += $digito;
        }

        return ($suma % 10) == 0;
    }

    public function cerrar_sesion()
    {
        //Registro la trasacción en el log
        Log::insertarLog('Cierre de sesión: ' . $_SESSION['dni'] . ' ' . $_SESSION['rol']);
        
        session_destroy();
        header('Location: index.php?p=home');
        exit();
    }

    public function ver_perfil()
    {
        $usuario = Usuario::obtenerUsuario($_SESSION['dni']);
        $this->data['ver_perfil']['nombre'] = $usuario->devolverValor('nombre');
        $this->data['ver_perfil']['apellidos'] = $usuario->devolverValor('apellidos');
        $this->data['ver_perfil']['dni'] = $usuario->devolverValor('dni');
        $this->data['ver_perfil']['email'] = $usuario->devolverValor('email');
        $this->data['ver_perfil']['numero_tarjeta'] = $usuario->devolverValor('numero_tarjeta');
        $this->data['SCRIPT_NAME'] = $this->request['server']['SCRIPT_NAME'];

        $_SESSION['datos_usuario']['dni'] = $usuario->devolverValor('dni');
        $_SESSION['datos_usuario']['nombre'] = $usuario->devolverValor('nombre');
        $_SESSION['datos_usuario']['apellidos'] = $usuario->devolverValor('apellidos');
        $_SESSION['datos_usuario']['email'] = $usuario->devolverValor('email');
        $_SESSION['datos_usuario']['clave'] = $usuario->devolverValor('clave');
        $_SESSION['datos_usuario']['numero_tarjeta'] = $usuario->devolverValor('numero_tarjeta');
    }

    public function editar_datos_usuario()
    {
        if ($_SESSION['rol'] != 'Cliente' && $_SESSION['rol'] != 'Recepcionista' && $_SESSION['rol'] != 'Administrador') {
            $this->data['error'] = "No tienes permisos para acceder a esta página";
            return;
        }else{
            if ($_SESSION['rol'] == 'Cliente'){
                if($_SESSION['dni'] != $this->request['params']['dni_anterior']){
                    $this->data['error'] = "No tienes permisos para acceder a esta página";
                    return;
                }
            }
        }

        $dni_anterior = $this->request['params']['dni_anterior'];
        $this->data['dni_anterior'] = $dni_anterior;
        if(isset($this->request['params']['confirmar'])){
            if($_SESSION['rol']=="Cliente"){
                Usuario::modificarUsuario($this->request['params']['nombre'], $this->request['params']['apellidos'], $this->request['params']['dni'],
                                          $this->request['params']['email'], $this->request['params']['clave'], $this->request['params']['numero_tarjeta'],
                                          $_SESSION['rol']);
                $descripcion = "El cliente con DNI: ".$this->request['params']['dni']." ha editado sus datos.";
                Log::insertarLog($descripcion);
                $this->data['info'] = "Datos editados correctamente";
                $this->ver_perfil();
            }else if($_SESSION['rol']=="Recepcionista" || $_SESSION['rol']=="Administrador"){
                Usuario::modificarUsuario($this->request['params']['nombre'], $this->request['params']['apellidos'], $this->request['params']['dni'],
                                          $this->request['params']['email'], $this->request['params']['clave'], $this->request['params']['numero_tarjeta'],
                                          $this->request['params']['rol']);
                $this->data['info'] = "Datos editados correctamente";
                $descripcion = "El usuario del sistema con DNI: ".$_SESSION['dni']." ha editado los datos del usuario con DNI: ".$this->request['params']['dni'];
                Log::insertarLog($descripcion);
                $this->ver_usuarios();
            }
        }else{
            if($_SESSION['rol']=="Cliente"){
                $usuario = Usuario::obtenerUsuario($_SESSION['dni']);
            }else if($_SESSION['rol']=="Recepcionista" || $_SESSION['rol']=="Administrador"){
                $usuario = Usuario::obtenerUsuario($this->request['params']['dni_anterior']);
                $this->data['id'] = $usuario->devolverValor('id');
            }
            if($usuario){
                // Compruebo todos los errores que pueden haber en el formulario
                if(isset($this->request['params']['nombre'])){
                    if($this->request['params']['nombre'] != ""){
                        $this->data['nombre'] = $this->request['params']['nombre'];
                    }else{
                        $this->data['nombre_error'] = "Es obligatorio indicar el nombre";
                        $this->data['nombre'] = $usuario->devolverValor('nombre');
                        $this->data['editar_error'] = "Hay error";
                    }
                }else{
                    $this->data['nombre'] = $usuario->devolverValor('nombre');
                    $this->data['editar_error'] = "Hay error";
                }
                if(isset($this->request['params']['apellidos'])){
                    if($this->request['params']['apellidos'] != ""){
                        $this->data['apellidos'] = $this->request['params']['apellidos'];
                    }else{
                        $this->data['apellidos_error'] = "Es obligatorio indicar el apellidos";
                        $this->data['apellidos'] = $usuario->devolverValor('apellidos');
                        $this->data['editar_error'] = "Hay error";
                    }
                }else $this->data['apellidos'] = $usuario->devolverValor('apellidos');
                if(isset($this->request['params']['dni'])){
                    if($this->request['params']['dni'] != ""){
                        $usuario_test = Usuario::obtenerUsuario($this->request['params']['dni']);
                        if($usuario_test){
                            if($this->request['params']['dni']==$dni_anterior){
                                if (preg_match('/^[0-9]{8}[A-Z]$/', $this->request['params']['dni'])) {
                                    $letra = substr($this->request['params']['dni'], -1);
                                    $numeros = substr($this->request['params']['dni'], 0, -1);
                                    if ($letra == $this->calcularLetraDNI($numeros)) {
                                        $this->data['dni'] = $this->request['params']['dni'];
                                    }else{
                                        $this->data['dni_error'] = "La letra del DNI no es válida";
                                        $this->data['dni'] = $usuario->devolverValor('dni');
                                        $this->data['editar_error'] = "Hay error";
                                    }
                                }else{
                                    $this->data['dni_error'] = "El formato del DNI no es válido";
                                    $this->data['dni'] = $usuario->devolverValor('dni');
                                    $this->data['editar_error'] = "Hay error";
                                }
                            }else{
                                $this->data['dni_error'] = "Ese DNI ya está registrado";
                                $this->data['dni'] = $usuario->devolverValor('dni');
                            }
                        }else{
                            if (preg_match('/^[0-9]{8}[A-Z]$/', $this->request['params']['dni'])) {
                                $letra = substr($this->request['params']['dni'], -1);
                                $numeros = substr($this->request['params']['dni'], 0, -1);
                                if ($letra == $this->calcularLetraDNI($numeros)) {
                                    $this->data['dni'] = $this->request['params']['dni'];
                                }else{
                                    $this->data['dni_error'] = "La letra del DNI no es válida";
                                    $this->data['dni'] = $usuario->devolverValor('dni');
                                    $this->data['editar_error'] = "Hay error";
                                }
                            }else{
                                $this->data['dni_error'] = "El formato del DNI no es válido";
                                $this->data['dni'] = $usuario->devolverValor('dni');
                                $this->data['editar_error'] = "Hay error";
                            }
                        }                    
                    }else{
                        $this->data['dni_error'] = "Es obligatorio indicar el dni";
                        $this->data['dni'] = $usuario->devolverValor('dni');
                        $this->data['editar_error'] = "Hay error";
                    }
                }else $this->data['dni'] = $usuario->devolverValor('dni');
                if(isset($this->request['params']['email'])){
                    if($this->request['params']['email'] != ""){
                        if (filter_var($this->request['params']['email'], FILTER_VALIDATE_EMAIL)) {
                            $this->data['email'] = $this->request['params']['email'];
                        }else{
                            $this->data['email_error'] = "Introduce un email válido";
                            $this->data['email'] = $usuario->devolverValor('email');
                            $this->data['editar_error'] = "Hay error";
                        }
                    }else{
                        $this->data['email_error'] = "Es obligatorio indicar el email";
                        $this->data['email'] = $usuario->devolverValor('email');
                        $this->data['editar_error'] = "Hay error";
                    }
                }else $this->data['email'] = $usuario->devolverValor('email');
                if(isset($this->request['params']['clave'])){
                    if($this->request['params']['clave'] != ""){
                        if (strlen($this->request['params']['clave']) >= 3) {
                            $this->data['clave'] = $this->request['params']['clave'];
                        }else{
                            $this->data['clave_error'] = "La clave debe tener al menos 3 caracteres";
                            $this->data['clave'] = "";
                            $this->data['editar_error'] = "Hay error";
                        }
                    }else{
                        $this->data['clave_error'] = "Es obligatorio indicar la clave";
                        $this->data['clave'] = "";
                        $this->data['editar_error'] = "Hay error";
                    }
                }else $this->data['clave'] = "";
                if(isset($this->request['params']['numero_tarjeta'])){
                    if($this->request['params']['numero_tarjeta'] != ""){
                        if (preg_match('/^[0-9]{16}$/', $this->request['params']['numero_tarjeta'])) {
                            if ($this->validarTarjeta($this->request['params']['numero_tarjeta'])) {
                                $this->data['numero_tarjeta'] = $this->request['params']['numero_tarjeta'];
                            }else{
                                $this->data['numero_tarjeta_error'] = "El formato del número de tarjeta no es válido";
                                $this->data['numero_tarjeta'] = $usuario->devolverValor('numero_tarjeta');
                                $this->data['editar_error'] = "Hay error";
                            }
                        }else{
                            $this->data['numero_tarjeta_error'] = "El número de tarjeta no es válido";
                            $this->data['numero_tarjeta'] = $usuario->devolverValor('numero_tarjeta');
                            $this->data['editar_error'] = "Hay error";
                        }
                    }else{
                        $this->data['numero_tarjeta_error'] = "Es obligatorio indicar el numero_tarjeta";
                        $this->data['numero_tarjeta'] = $usuario->devolverValor('numero_tarjeta');
                        $this->data['editar_error'] = "Hay error";
                    }
                }else $this->data['numero_tarjeta'] = $usuario->devolverValor('numero_tarjeta');
                if($_SESSION['rol']=="Administrador" || $_SESSION['rol']=="Recepcionista"){
                    if(isset($this->request['params']['rol'])){
                        if($this->request['params']['rol'] != ""){
                            $this->data['rol'] = $this->request['params']['rol'];
                        }else{
                            $this->data['rol_error'] = "Es obligatorio indicar el rol";
                            $this->data['rol'] = $usuario->devolverValor('rol');
                            $this->data['editar_error'] = "Hay error";
                        }
                    }else $this->data['rol'] = $usuario->devolverValor('rol');
                }
                if( !isset($this->data['editar_error'])) $this->data['NoEditable'] = 'NoEditable';
                $this->data['editar_datos_usuario'] = "editar_datos_usuario";
                $this->data['SCRIPT_NAME'] = $this->request['server']['SCRIPT_NAME'];
            }else{
                $this->data['error'] = "Algo ha fallado al obtener los datos del usuario";
            }
        }
    }

    public function ver_usuarios()
    {
        $rol = $_SESSION['rol'];
        
        if ($rol == 'Administrador' || $rol == 'Recepcionista') {

            $paginaActual = isset($_GET['pagina']) ? $_GET['pagina'] : 1;
            $numeroFilas = 5;
            $filaInicio = ($paginaActual - 1) * $numeroFilas;
            $filtro = null;

            if ($rol == 'Recepcionista') {
                $filtro = 'Cliente';
            }
            
            $usuarios = Usuario::obtenerUsuarios($filaInicio, $numeroFilas, 'rol DESC', $filtro);

            if ($rol == 'Administrador') {
                $this->data['usuarios'] = $usuarios[0];
            } elseif ($rol == 'Recepcionista'){
                foreach ($usuarios[0] as $usuario) {
                    if ($usuario->devolverValor('rol') == 'Cliente') {

                        $this->data['usuarios'][] = $usuario;
                    }
                }
            }

            $totalUsuarios = $usuarios[1];
            $totalPaginas = ceil($totalUsuarios / $numeroFilas);

            $this->data['ver_usuarios'] = 'ver_usuarios';
            $this->data['totalPaginas'] = $totalPaginas;
            $this->data['paginaActual'] = $paginaActual;
            
            $this->data['SCRIPT_NAME'] = $this->request['server']['SCRIPT_NAME'];

        } else {
            $this->data['error'] = "No tienes permisos para acceder a esta página";
        }
    }

    public function eliminar_usuario()
    {
        if ($_SESSION['rol'] != 'Administrador' and $_SESSION['rol'] != 'Recepcionista') {

            $this->data['error'] = "No tienes permisos para acceder a esta página";
            return;
        }else if($_SESSION['rol'] == 'Recepcionista'){
            if(isset($this->request['params']['dni'])){
                $usuario = Usuario::obtenerUsuario($this->request['params']['dni']);
                $rol_usuario_eliminar = $usuario->devolverValor('rol');
                if($rol_usuario_eliminar != "Cliente"){
                    $this->data['error'] = "Como recepcionista sólo puede eliminar clientes";
                    return;
                }
            }
        }

        if (isset($this->request['params']['dni'])) {
            $dni = $this->request['params']['dni'];
            Reserva::eliminarReservasUsuario($dni);
            Usuario::eliminarUsuario($dni);

            $_SESSION['info'] = "Usuario eliminado correctamente";

            //Registro las trasacciones en el log
            Log::insertarLog('Eliminación de Reservas de Usuario: ' . $dni);
            Log::insertarLog('Eliminación de Usuario: ' . $dni);

            header('Location: index.php?p=ver_usuarios');
            exit();
        }
    }

    public function render()
    {
        return HTMLrenderWeb($this->data);
    }
}
