<?php

function _HTMLshowDatosPerfil($path, $datos)
{
    $result = '<h2 class="text-left ml-16 mt-10 text-2xl font-bold text-gray-600 mb-6">Hola ' . $datos['nombre'] . ',</h2>';
    $result .= '<section class="m-16 flex">';
    $result .= '<div class="text-right text-lg mr-4 h-max"><p>Apellidos: </p><p>Dni: </p><p>Email: </p><p>Nº tarjeta: </p></div>';
    $result .= '<div class="text-lg font-semibold"><p>' . $datos['apellidos'] . '</p><p>' . $datos['dni'] . '</p><p>' . $datos['email'] . '</p><p>' . $datos['numero_tarjeta'] . '</p></div>';
    $result .= '</section>';
    $direccion = $path . '?p=editar_datos_usuario&dni_anterior=' . $datos['dni'];
    $result .= '<a href="' . $direccion . '" class="ml-16 p-3 font-bold text-xl bg-gray-200 text-blue-700 hover:bg-blue-700 rounded-lg hover:text-white">Editar datos';
    $result .= '</a>';

    return $result;
}



function _HTMLshowHabitaciones($datos)
{
    $result = <<<HTML
    <script>
    function confirmarBorrado() {
        return confirm('¿Estás seguro de que quieres eliminar esta habitación?');
    }
    </script>
    <section class="h-56 w-full bg-cover bg-fixed" style="background-image: url(../public/images/comedor.jpg)"></section>
    <section class="m-16">
        <h2 class="m-10 font-semibold font-serif text-3xl border-b-2">Nuestras Habitaciones</h2>
        <p class="mx-24 text-justify">
        En H&H Hotels, nuestras habitaciones están diseñadas para ofrecerte el máximo confort y estilo durante tu estancia. Cada habitación combina elegancia moderna con todas las comodidades necesarias para garantizar una experiencia relajante y placentera. Ya sea que estés de viaje por negocios o por placer, nuestras habitaciones son el refugio perfecto para descansar y rejuvenecer.        
        </p>
        <p class="mx-24 mt-5 text-justify">
        Descubre la diversidad de nuestras habitaciones, cada una cuidadosamente decorada y equipada con servicios de primera calidad. Desde amplias suites con impresionantes vistas hasta acogedoras habitaciones estándar, en H&H Hotels encontrarás el espacio ideal para satisfacer tus necesidades y preferencias. Explora nuestras opciones de alojamiento y elige la habitación perfecta para tu próxima estancia con nosotros.
        </p>
    </section>
    HTML;

    $path = $datos['SCRIPT_NAME'];

    if ($_SESSION['rol'] == 'Recepcionista') {

        $result .= '<div class="p-4 mb-16 text-center">';
        $result .= '<a class="p-6 px-32 font-semibold border-2 border-blue-700 text-3xl
                     text-blue-700 hover:text-white hover:bg-blue-700 rounded" href="' . $path . '?p=anadir_habitacion' . '">Añadir habitacion</a>';
        $result .= '</div>';
    }

    foreach ($datos['habitaciones'][0] as $habitacion) {

        $result .= '<section class="h-96 bg-gray-200 flex m-10 rounded-lg justify-center items-center hover:shadow-2xl">';
        $idEncontrado = false;
        foreach ($datos['imagenes'][0] as $imagen) {

            if ($imagen->devolverValor('id_habitacion') == $habitacion->devolverValor('id')) {

                $idEncontrado = true;
                $result .= '<img class="h-2/3 w-2/5 m-10" src="' . $imagen->devolverValor('url') . '" alt="foto habitacion">';
                break;
            }
        }
        if ($idEncontrado === false) {

            $result .= '<img class="h-2/3 w-2/5 m-10" src="../public/images/img_hab_defecto.jpg" alt="foto habitacion">';
        }
        $result .= '<div class="w-3/5 content-center">';
        $result .= '<h3 class="m-3 font-semibold font-serif text-xl border-b-2 border-gray-300">Habitación: ' . $habitacion->devolverValor('numero_habitacion') . '</h3>';
        $result .= '<p class="text-justify mt-4 p-4">' . $habitacion->devolverValor('descripcion') . '</p>';
        $result .= '<div class="flex p-4">';
        $result .= '<p class="w-1/2">Precio por noche: ' . $habitacion->devolverValor('precio_noche') . ' &euro;</p>';
        $result .= '<p class="w-1/2">Capacidad: ' . $habitacion->devolverValor('capacidad') . '</p>';
        $result .= '</div>';

        if ($_SESSION['rol'] == 'anonimo') {
            $result .= '<div class="p-4 pb-0 text-right">';
            $result .= '<a class="p-3 border-2 border-blue-700 text-blue-700 hover:text-white hover:bg-blue-700 rounded" href="' . $path . '?p=registrarse' . '">Regístrese para reservar</a>';
            $result .= '</div>';
        } else if ($_SESSION['rol'] == 'Recepcionista') {
            $result .= '<div class="p-4 pb-0 text-right">';
            $result .= '<a class="p-3 border-2 border-red-700 text-red-700 hover:text-white hover:bg-red-700 rounded" 
                        href="' . $path . '?p=borrar_habitacion&numero_habitacion=' . urlencode($habitacion->devolverValor('numero_habitacion')) . '" onclick="return confirmarBorrado();">Borrar Habitación</a>';
            $result .= '<a class="p-3 ml-5 border-2 border-blue-700 text-blue-700 hover:text-white hover:bg-blue-700 rounded"
                        href="' . $path . '?p=editar_habitacion&numero_hab_anterior=' . urlencode($habitacion->devolverValor('numero_habitacion')) . '">Editar Información</a>';
            $result .= '<a class="p-3 ml-5 border-2 border-blue-700 text-blue-700 hover:text-white hover:bg-blue-700 rounded"
                        href="' . $path . '?p=anadir_fotografia&numero_habitacion=' . urlencode($habitacion->devolverValor('numero_habitacion')) . '">Añadir Fotografía</a>';
            $result .= '</div>';
        }
        $result .= '</div>';
        $result .= '</section>';
    }

    return $result;
}

function _HTMLshowAnadirHabitacion($datos)
{
    if (isset($datos['editar_habitacion_validate'])) {
        $action = $datos['SCRIPT_NAME'] . '?p=editar_habitacion&numero_hab_anterior=' . $datos['numero_hab_anterior'];
    } else if (isset($datos['anadir_habitacion_validate']) || isset($datos['anadir_habitacion'])) $action = $datos['SCRIPT_NAME'] . '?p=validate_anadir_hab';

    $result  = '<section class="bg-gray-100 p-10">';
    $result .= '<form action="' . $action . '" method="post" novalidate class="p-6 w-4/5 mb-6 flex flex-col bg-white rounded-lg">';
    $result .= '<label class="m-2 flex items-center">';

    if (isset($datos['params'])) {
        if (isset($datos['anadir_habitacion']['numero_hab_error'])) {
            $result .= 'Número de la habitación: <input type="text" class="ml-10 p-2 border-2 rounded-lg" name="numero_hab" placeholder="Número de la habitacion">
                        <p class="text-red-500 text-sm pl-5">' . $datos['anadir_habitacion']['numero_hab_error'] . '</p>';
        } else {
            if (isset($datos['NoEditable'])) {
                $result .= 'Número de la habitación: <input type="text" class="ml-10 p-2 border-2 rounded-lg" name="numero_hab" readonly value="' . $datos['params']['numero_hab'] . '">';
            } else $result .= 'Número de la habitación: <input type="text" class="ml-10 p-2 border-2 rounded-lg" name="numero_hab" value="' . $datos['params']['numero_hab'] . '">';
        }
    } else {
        $result .= 'Número de la habitación: <input type="text" class="ml-10 p-2 border-2 rounded-lg" name="numero_hab" placeholder="Número de la habitacion">';
    }
    $result .= '</label>';
    $result .= '<label class="m-2 flex items-center">';
    if (isset($datos['params'])) {
        if (isset($datos['anadir_habitacion']['capacidad_error'])) {
            $result .= 'Capacidad de la habitación: <input type="text" class="ml-6 p-2 border-2 rounded-lg" name="capacidad" placeholder="Capacidad">
                        <p class="text-red-500 text-sm pl-5">' . $datos['anadir_habitacion']['capacidad_error'] . '</p>';
        } else {
            if (isset($datos['NoEditable'])) {
                $result .= 'Capacidad de la habitación: <input type="text" class="ml-6 p-2 border-2 rounded-lg" name="capacidad" readonly value="' . $datos['params']['capacidad'] . '">';
            } else $result .= 'Capacidad de la habitación: <input type="text" class="ml-6 p-2 border-2 rounded-lg" name="capacidad" value="' . $datos['params']['capacidad'] . '">';
        }
    } else {
        $result .= 'Capacidad de la habitación: <input type="text" class="ml-6 p-2 border-2 rounded-lg" name="capacidad" placeholder="Capacidad">';
    }
    $result .= '</label>';
    $result .= '<label class="m-2 flex items-center">';
    if (isset($datos['params'])) {
        if (isset($datos['anadir_habitacion']['precio_error'])) {
            $result .= 'Precio de la habitación: <input type="text" class="ml-14 p-2 border-2 rounded-lg" name="precio" placeholder="Precio">
                        <p class="text-red-500 text-sm pl-5">' . $datos['anadir_habitacion']['precio_error'] . '</p>';
        } else {
            if (isset($datos['NoEditable'])) {
                $result .= 'Precio de la habitación: <input type="text" class="ml-14 p-2 border-2 rounded-lg" name="precio" readonly value="' . $datos['params']['precio'] . '">';
            } else $result .= 'Precio de la habitación: <input type="text" class="ml-14 p-2 border-2 rounded-lg" name="precio" value="' . $datos['params']['precio'] . '">';
        }
    } else {
        $result .= 'Precio de la habitación: <input type="text" class="ml-14 p-2 border-2 rounded-lg" name="precio" placeholder="Precio">';
    }
    $result .= '</label>';
    $result .= '<label class="m-2 pt-2">';
    if (isset($datos['params'])) {
        if (isset($datos['anadir_habitacion']['descripcion_error'])) {
            $result .= 'Descripción de la habitación: <textarea type="text" class="m-4 w-5/6 h-32 block p-2 border-2 rounded-lg" name="descripcion" placeholder="Descripción"></textarea>
                        <p class="text-red-500 text-sm pl-5">' . $datos['anadir_habitacion']['descripcion_error'] . '</p>';
        } else {
            if (isset($datos['NoEditable'])) {
                $result .= 'Descripción de la habitación: <textarea type="text" class="m-4 w-5/6 h-32 block p-2 border-2 rounded-lg" readonly name="descripcion">' . $datos['params']['descripcion'] . '</textarea>';
            } else $result .= 'Descripción de la habitación: <textarea type="text" class="m-4 w-5/6 h-32 block p-2 border-2 rounded-lg" name="descripcion">' . $datos['params']['descripcion'] . '</textarea>';
        }
    } else {
        $result .= 'Descripción de la habitación: <textarea type="text" class="m-4 w-5/6 h-32 block p-2 border-2 rounded-lg" name="descripcion" placeholder="Descripción"></textarea>';
    }
    $result .= '</label>';
    $result .= '<label class="w-1/4 mx-auto">';
    if (isset($datos['NoEditable'])) $result .= '<input type="submit" name="confirmar" value="Confirmar" class="py-4 w-full font-semibold border-2 border-blue-700 text-blue-700 hover:text-white hover:bg-blue-700 rounded mb-2">';
    else $result .= '<input type="submit" value="Enviar" class="py-4 w-full font-semibold border-2 border-blue-700 text-blue-700 hover:text-white hover:bg-blue-700 rounded mb-2">';
    $result .= '</label>';
    $result .= '</form>';
    $result .= '</section>';

    return $result;
}

function _HTMLshowAnadirFotografia($datos)
{
    $numero_habitacion = $datos['numero_habitacion'];
    $action =  $datos['SCRIPT_NAME'] . '?p=anadir_fotografia&numero_habitacion=' . $numero_habitacion;

    $result = '<section class="p-10 bg-gray-100 h-full">';
    $result .= '<form id="myForm" class="p-4 bg-white rounded-lg " action="' . $action . '" method="post" enctype="multipart/form-data" novalidate>';
    $result .= '<h2 class="font-semibold text-xl p-4">Seleccione la fotografía que desee añadir a la habitación número: ' . $numero_habitacion . '</h2>';
    $result .= '<div id="imagenContenedor" class="h-full">';
    $result .= '<label>';
    $result .= '<input class="m-10" type="file" name="imagen[]">';
    $result .= '</label>';
    $result .= '</div>';
    $result .= '<button type="button" id="anadirImagen" class="py-4 w-1/3 ml-10 font-semibold border-2 border-blue-700 text-blue-700 hover:text-white hover:bg-blue-700 rounded mb-2">Añadir campo de imagen</button>';
    $result .= '<button type="button" id="eliminarImagen" class="py-4 w-1/3 ml-10 font-semibold border-2 border-blue-700 text-blue-700 hover:text-white hover:bg-blue-700 rounded mb-2">Eliminar campo de imagen</button>';
    $result .= '<input type="submit" value="Enviar" class="py-4 w-1/3 block ml-10 font-semibold border-2 border-blue-700 text-blue-700 hover:text-white hover:bg-blue-700 rounded">';
    $result .= '</form>';
    $result .= '</section>';

    $result .= <<<HTML
        <script>
        document.getElementById("anadirImagen").addEventListener("click", function() {
            var contenedor = document.getElementById("imagenContenedor");
            if (contenedor.children.length < 6) {
                var nuevoInput = document.createElement("input");
                nuevoInput.type = "file";
                nuevoInput.name = "imagen[]";
                nuevoInput.className = "m-10";
    
                var nuevoLabel = document.createElement("label");
                nuevoLabel.appendChild(nuevoInput);
                contenedor.appendChild(nuevoLabel);
            }
        });
    
        document.getElementById("eliminarImagen").addEventListener("click", function() {
            var contenedor = document.getElementById("imagenContenedor");
            if (contenedor.children.length > 1) {
                contenedor.removeChild(contenedor.lastChild);
            }
        });
        </script>
    HTML;

    return $result;
}

function _HTMLshowFormularioEditarDatos($datos)
{
    $rol = $_SESSION['rol'];
    $action =  $datos['SCRIPT_NAME'] . '?p=editar_datos_usuario';
    $result = '<section class="p-10 bg-gray-100">';
    $result .= '<form class="p-4 bg-white rounded-lg " action="' . $action . '" method="post" novalidate>';
    $result .= '<h2 class="font-semibold text-xl p-4"> Edición de datos de Usuario </h2>';
    $result .= '<div class="flex">';
    $result .= '<label class="h-28 w-72 ml-20 mt-10 block"><p class="v"<block>Nombre</p>';
    if ($rol == "Cliente" || isset($datos['NoEditable'])) $result .= '<input class="block p-1 px-3 mt-2 w-80 border-2 border-gray-200 rounded-lg" type="text" readonly name="nombre" value=' . $datos['nombre'] . ' >';
    else $result .= '<input class="block p-1 px-3 mt-2 w-80 border-2 border-gray-200 rounded-lg" type="text" name="nombre" value=' . $datos['nombre'] . ' >';
    if (isset($datos['nombre_error'])) $result .= '<p class="text-sm text-red-500">' . $datos['nombre_error'] . '</p>';
    $result .= '</label>';
    $result .= '<label class="h-28 w-1/3 ml-20 mt-10 block"><p class="v"<block>Apellidos</p>';
    if ($rol == "Cliente" || isset($datos['NoEditable'])) $result .= '<input class="block p-1 px-3 mt-2 w-80 border-2 border-gray-200 rounded-lg" type="text" readonly name="apellidos" value=' . $datos['apellidos'] . ' >';
    else $result .= '<input class="block p-1 px-3 mt-2 w-80 border-2 border-gray-200 rounded-lg" type="text" name="apellidos" value=' . $datos['apellidos'] . ' >';
    if (isset($datos['apellidos_error'])) $result .= '<p class="text-sm text-red-500">' . $datos['apellidos_error'] . '</p>';
    $result .= '</label>';
    $result .= '</div>';
    $result .= '<label class="h-28 w-1/3 ml-20 block"> Dni';
    if ($rol == "Cliente" || isset($datos['NoEditable'])) $result .= '<input class="block p-1 px-3 mt-2 w-80 border-2 border-gray-200 rounded-lg" type="text" readonly name="dni" value=' . $datos['dni'] . ' >';
    else $result .= '<input class="block p-1 px-3 mt-2 w-80 border-2 border-gray-200 rounded-lg" type="text" name="dni" value=' . $datos['dni'] . ' >';
    if (isset($datos['dni_error'])) $result .= '<p class="text-sm text-red-500">' . $datos['dni_error'] . '</p>';
    $result .= '</label>';
    $result .= '<div class="flex">';
    $result .= '<label class="h-28 w-72 ml-20 block"> Email';
    if (isset($datos['NoEditable'])) $result .= '<input class="block p-1 px-3 mt-2 w-80 border-2 border-gray-200 rounded-lg" type="text" readonly name="email" value=' . $datos['email'] . ' >';
    else $result .= '<input class="block p-1 px-3 mt-2 w-80 border-2 border-gray-200 rounded-lg" type="text" name="email" value=' . $datos['email'] . ' >';
    if (isset($datos['email_error'])) $result .= '<p class="text-sm text-red-500">' . $datos['email_error'] . '</p>';
    $result .= '</label>';
    $result .= '<label class="h-28 w-1/3 ml-20 block"> Clave';
    if (isset($datos['NoEditable'])) $result .= '<input class="block p-1 px-3 mt-2 w-80 border-2 border-gray-200 rounded-lg" type="password" readonly name="clave" value=' . $datos['clave'] . ' >';
    else $result .= '<input class="block p-1 px-3 mt-2 w-80 border-2 border-gray-200 rounded-lg" type="password" name="clave" value=' . $datos['clave'] . ' >';
    if (isset($datos['clave_error'])) $result .= '<p class="text-sm text-red-500">' . $datos['clave_error'] . '</p>';
    $result .= '</label>';
    $result .= '</div>';
    $result .= '<label class="h-28 w-1/3 ml-20 block"> Numero_tarjeta';
    if (isset($datos['NoEditable'])) $result .= '<input class="block p-1 px-3 mt-2 w-80 border-2 border-gray-200 rounded-lg" type="text" readonly name="numero_tarjeta" value=' . $datos['numero_tarjeta'] . ' >';
    else $result .= '<input class="block p-1 px-3 mt-2 w-80 border-2 border-gray-200 rounded-lg" type="text" name="numero_tarjeta" value=' . $datos['numero_tarjeta'] . ' >';
    if (isset($datos['numero_tarjeta_error'])) $result .= '<p class="text-sm text-red-500">' . $datos['numero_tarjeta_error'] . '</p>';
    $result .= '</label>';
    if ($rol == "Administrador") {
        $result .= '<label class="h-28 w-1/3 ml-20 block"><p class="mb-2">Rol</p>';
        if (isset($datos['NoEditable'])) {
            $result .= '<select name="rol">';
            $result .= '<option';
            if ($datos['rol'] == "Cliente") {
                $result .= ' selected ';
                $result .= '>Cliente</option>';
                $result .= '<option disabled >Recepcionista</option>';
                $result .= '<option disabled >Administrador</option>';
            } else {
                $result .= 'disabled >Cliente</option>';
                $result .= '<option';
                if ($datos['rol'] == "Recepcionista") {
                    $result .= ' selected ';
                    $result .= '>Recepcionista</option>';
                    $result .= '<option disabled >Administrador</option>';
                } else {
                    $result .= 'disabled >Recepcionista</option>';
                    $result .= '<option selected >Administrador</option>';
                }
            }
        } else {
            $result .= '<select name="rol">';
            $result .= '<option';
            if ($datos['rol'] == "Cliente") $result .= ' selected ';
            $result .= '>Cliente</option>';
            $result .= '<option';
            if ($datos['rol'] == "Recepcionista") $result .= ' selected ';
            $result .= '>Recepcionista</option>';
            $result .= '<option';
            if ($datos['rol'] == "Administrador") $result .= ' selected ';
            $result .= '>Administrador</option>';
            $result .= '</select>';
        }
        if (isset($datos['rol_error'])) $result .= '<p class="text-sm text-red-500">' . $datos['rol_error'] . '</p>';
    } else if ($_SESSION['rol'] == "Recepcionista") {
        $result .= '</label>';
        $result .= '<input type="hidden" name="rol" value="' . $datos['rol'] . '">';
        $result .= '<label>';
    }
    $result .= '</label>';
    $result .= '<input type="hidden" name="dni_anterior" value="' . $datos['dni_anterior'] . '">';
    $result .= '<label>';
    if (isset($datos['NoEditable'])) $result .= '<input type="submit" name="confirmar" value="Confirmar" class="py-4
    w-1/3 block ml-10 font-semibold border-2 border-blue-700 text-blue-700 hover:text-white hover:bg-blue-700 rounded mb-2">';
    else $result .= '<input type="submit" value="Enviar" class="py-4 w-1/3 block ml-10 
    font-semibold border-2 border-blue-700 text-blue-700 hover:text-white hover:bg-blue-700 rounded mb-2">';
    $result .= '</label>';
    $result .= '</form>';
    $result .= '</section>';

    return $result;
}


function _HTMLshowReservas($datos)
{
    $totalPaginas = $datos['totalPaginas'];
    $paginaActual = $datos['paginaActual'];

    $result = <<<HTML
    <script>
    function confirmarBorrado() {
        return confirm('¿Estás seguro de que quieres eliminar esta reserva?');
    }
    </script>
    HTML;
    $rol = $_SESSION['rol'];
    $path = $datos['SCRIPT_NAME'];
    $action = $datos['SCRIPT_NAME'] . '?p=mostrar_reservas';
    $result .= '<section class="h-56 w-full bg-cover bg-fixed" style="background-image: url(../public/images/comedor.jpg)"></section>';
    if ($rol == "Cliente") $result .= '<section class="m-16"><h2 class="m-10 font-semibold font-serif text-3xl border-b-2"> Tus reservas </h2></section>';
    if ($rol == "Recepcionista") $result .= '<section class="m-16"><h2 class="m-10 font-semibold font-serif text-3xl border-b-2"> Reservas del Hotel </h2></section>';
    if ($_SESSION['rol'] == 'Recepcionista' || $_SESSION['rol'] == 'Cliente') {
        $result .= '<div class="p-4 mb-16 text-center w-full">';
        $result .= '<a class="p-4 px-20 font-bold border-2 border-blue-700 text-base text-blue-700 hover:text-white hover:bg-blue-700 rounded" href="' . $path . '?p=anadir_reserva&fase=inicio' . '">Nueva Reserva</a>';
        $result .= '</div>';
    }

    $result .= '<form class="p-10 pt-1 bg-white rounded-lg" action="' . $action . '" method="post" novalidate>';
    if (isset($datos['motrar_error'])) {
        $result .= '<p class="text-center m-5 font-semibold">' . $datos['motrar_error'] . '</p>';
    } else {

        $result .= '<div class="flex justify-between items-center mb-6 w-full">
                    <select id="ordenarSelect" class="p-3 font-bold border-2 border-blue-700 text-base bg-white text-blue-700 rounded">
                    <option value="diasTotalesAsc">Total de días (Ascendente)</option>
                    <option value="diasTotalesDesc">Total de días (Descendente)</option>
                    <option value="antiguedadAsc">Antigüedad (Ascendente)</option>
                    <option value="antiguedadDesc">Antigüedad (Descendente)</option>
                    </select>';

        $result .= '<div class="w-1/3 flex items-center justify-center">
                    <label class="">Solo reservas con comentarios</label>
                    <input type="checkbox" name="comentario" value="comentario" class="">
                    </div>';

        $result .= '<div class="flex flex-col">
            <label class="">Fecha de inicio</label>
            <input type="date" id="fechaInicio" name="fechaInicio" class="border-2 border-blue-700 text-base bg-white rounded">
        
            <label class="">Fecha de fin</label>
            <input type="date" id="fechaFin" name="fechaFin" class="border-2 border-blue-700 text-base bg-white rounded">
        
            <button id="aplicarFiltro" class="mt-2 font-bold border-2 border-blue-700 text-base bg-white text-blue-700 rounded hover:text-white hover:bg-blue-700 ">Aplicar filtro</button>
            </div></div>';


        $result .= '<table class="w-full table-auto text-center text-sm">';
        $result .= '<thead>';
        $result .= '<tr>';
        if ($_SESSION['rol'] == "Recepcionista") $result .= '<th class="border px-4 py-2">Usuario</th>';
        $result .= '<th class="border px-4 py-2">Nº habitación</th>';
        $result .= '<th class="border px-4 py-2">Nº personas</th>';
        $result .= '<th class="border px-4 py-2">Fecha llegada</th>';
        $result .= '<th class="border px-4 py-2">Fecha salida</th>';
        $result .= '<th class="border px-4 py-2">Comentarios</th>';
        $result .= '</tr>';
        $result .= '</thead>';
        $result .= '<tbody>';
        foreach ($datos['reserva'][0] as $reserva) {
            if ($reserva->devolverValor('estado') == "Confirmada" || $reserva->devolverValor('estado') == "Mantenimiento") {
                $id_hab = $reserva->devolverValor('id_habitacion');
                $id_usuario = $reserva->devolverValor('id_usuario');
                if ($reserva->devolverValor('estado') == "Mantenimiento") $result .= '<tr class="bg-gray-200">';
                else $result .= '<tr>';
                if ($_SESSION['rol'] == "Recepcionista") $result .= '<td class="border px-4 py-2">' . $datos['nombre_usuario'][$id_usuario] . '</td>';
                $result .= '<td class="border px-4 py-2">' . $datos['num_habitacion'][$id_hab] . '</td>';
                $result .= '<td class="border px-4 py-2">' . $reserva->devolverValor('n_personas') . '</td>';
                $result .= '<td class="border px-4 py-2">' . $reserva->devolverValor('fecha_inicio') . '</td>';
                $result .= '<td class="border px-4 py-2">' . $reserva->devolverValor('fecha_fin') . '</td>';
                $result .= '<td class="border px-4 py-2">' . $reserva->devolverValor('comentarios') . '</td>';
                $result .= '<td class="border px-4 py-2"><a href="' . $datos['SCRIPT_NAME'] . '?p=editar_reserva&id=' . urlencode($reserva->devolverValor('id')) . '" class="text-blue-700 hover:underline">Editar</a></td>';
                $result .= '<td class="border px-4 py-2"><a href="' . $datos['SCRIPT_NAME'] . '?p=eliminar_reserva&id=' . urlencode($reserva->devolverValor('id')) . '" class="text-red-700 hover:underline" onclick="return confirmarBorrado();">Borrar</a></td>';
                $result .= '</tr>';
            }
        }
        $result .= '</tbody>';
        $result .= '</table>';
    }

    $result .= <<<HTML
        <div class="mt-4">
            <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
HTML;

    for ($i = 1; $i <= $totalPaginas; $i++) {
        if ($i == $paginaActual) {
            $result .= "<span class='relative inline-flex items-center px-4 py-2 border border-gray-300 bg-blue-500 text-sm font-medium text-white'>";
        } else {
            $result .= "<a href='?p=reservas&pagina=$i' class='relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50'>";
        }

        $result .= $i;

        if ($i == $paginaActual) {
            $result .= "</span>";
        } else {
            $result .= "</a>";
        }
    }

    $result .= '</nav></div></form>';

    $result .= <<<HTML
    <script>
        document.querySelector('#aplicarFiltro').addEventListener('click', function(e) {
            e.preventDefault();
            aplicarFiltros();
        });

        document.querySelector('input[name="comentario"]').addEventListener('change', function() {
            aplicarFiltros();
        });

        function aplicarFiltros() {
            var fechaInicio = new Date(document.querySelector('#fechaInicio').value);
            var fechaFin = new Date(document.querySelector('#fechaFin').value);
            var comentarioChecked = document.querySelector('input[name="comentario"]').checked;

            var tabla = document.querySelector('table');
            var cuerpoTabla = tabla.tBodies[0];
            var filas = Array.from(cuerpoTabla.rows);

            for (var i = 0; i < filas.length; i++) {
                var mostrarFila = true;

                // Filtrado por fecha
                var celdaFechaEntrada = filas[i].cells[3]; // Cambia el índice si la celda de fecha de entrada no es la cuarta celda
                var fechaEntrada = new Date(celdaFechaEntrada.innerText.trim());
                if (fechaEntrada < fechaInicio || fechaEntrada > fechaFin) {
                    mostrarFila = false;
                }

                // Filtrado por comentario
                var celdaComentarios = filas[i].cells[5]; // Cambia el índice si la celda de comentarios no es la sexta celda
                if (comentarioChecked && celdaComentarios.innerText.trim() === '') {
                    mostrarFila = false;
                }

                // Mostrar u ocultar la fila basada en las condiciones
                filas[i].style.display = mostrarFila ? '' : 'none';
            }
        }

        // Ordena las filas de la tabla según el criterio seleccionado
        document.getElementById('ordenarSelect').addEventListener('change', function() {
            var tabla = document.querySelector('table');
            var cuerpoTabla = tabla.tBodies[0];
            var filas = Array.from(cuerpoTabla.rows);
            var tipoOrdenamiento = this.value;
            var ascendente = tipoOrdenamiento.endsWith('Asc');
    
            if (tipoOrdenamiento.startsWith('diasTotales')) {
                filas.sort(function(a, b) {
                    var diasA = new Date(a.cells[4].innerText) - new Date(a.cells[3].innerText);
                    var diasB = new Date(b.cells[4].innerText) - new Date(b.cells[3].innerText);
                    return ascendente ? diasA - diasB : diasB - diasA;
                });
            } else if (tipoOrdenamiento.startsWith('antiguedad')) {
                filas.sort(function(a, b) {
                    var fechaA = new Date(a.cells[3].innerText);
                    var fechaB = new Date(b.cells[3].innerText);
                    return ascendente ? fechaA - fechaB : fechaB - fechaA;
                });
            } 
    
            // Agrega las filas ordenadas a la tabla
            for (var i = 0; i < filas.length; i++) {
                cuerpoTabla.appendChild(filas[i]);
            }
        });
    </script>
HTML;

    return $result;
}



function _HTMLshowAnadirReserva($datos)
{
    $rol = $_SESSION['rol'];
    $action =  $datos['SCRIPT_NAME'] . '?p=anadir_reserva&fase=inicio';
    $result = '<section class="p-10 bg-gray-100">';
    $result .= '<form class="p-4 pl-10 bg-white rounded-lg " action="' . $action . '" method="post" novalidate>';
    $result .= '<h2 class="font-semibold text-xl p-6"> Creando una nueva reserva </h2>';

    $result .= '<label class="w-1/3 inline-block"><p>Nº de personas</p>';
    if (isset($datos['NoEditable'])) $result .= '<input class="block p-1 px-3 mx-3 mt-2 w-4/5 border-2 border-gray-200 rounded-lg" type="text" readonly name="n_personas" value=' . $datos['n_personas'] . ' >';
    else $result .= '<input class="p-1 px-3 mt-2 w-4/5 border-2 border-gray-200 rounded-lg" type="text" name="n_personas" value=' . $datos['n_personas'] . ' >';
    if (isset($datos['anadir_reserva']['n_personas_error'])) $result .= '<p class="text-sm text-red-500">' . $datos['anadir_reserva']['n_personas_error'] . '</p>';
    $result .= '</label>';

    $result .= '<label class="w-1/3 inline-block"><p>Fecha de llegada</p>';
    if (isset($datos['NoEditable'])) $result .= '<input class="block p-1 px-3 mt-2 w-4/5 border-2 border-gray-200 rounded-lg" type="date" readonly name="fecha_inicio" value=' . $datos['fecha_inicio'] . ' >';
    else $result .= '<input class="p-1 px-3 mt-2 w-4/5 border-2 border-gray-200 rounded-lg" type="date" name="fecha_inicio" value=' . $datos['fecha_inicio'] . ' >';
    if (isset($datos['anadir_reserva']['fecha_inicio_error'])) $result .= '<p class="text-sm text-red-500">' . $datos['anadir_reserva']['fecha_inicio_error'] . '</p>';
    $result .= '</label>';

    $result .= '<label class="w-1/3 inline-block"><p>Fecha de salida</p>';
    if (isset($datos['NoEditable'])) $result .= '<input class="block p-1 px-3 mt-2 w-4/5 border-2 border-gray-200 rounded-lg" type="date" readonly name="fecha_fin" value=' . $datos['fecha_fin'] . ' >';
    else $result .= '<input class="p-1 px-3 mt-2 w-4/5 border-2 border-gray-200 rounded-lg" type="date" name="fecha_fin" value=' . $datos['fecha_fin'] . ' >';
    if (isset($datos['anadir_reserva']['fecha_fin_error'])) $result .= '<p class="text-sm text-red-500">' . $datos['anadir_reserva']['fecha_fin_error'] . '</p>';
    $result .= '</label>';

    $result .= '<label class="mt-10 inline-block w-2/3 pr-10"><p>Comentarios</p>';
    if (isset($datos['NoEditable'])) $result .= '<textarea class="block p-1 px-3 mt-2 w-full border-2 border-gray-200 rounded-lg" type="text" readonly name="comentarios">'. $datos['comentarios'] .'</textarea>';
    else $result .= '<textarea class="h-24 block p-1 px-3 mt-2 w-full border-2 border-gray-200 rounded-lg" type="text" name="comentarios">' . $datos['comentarios'] . '</textarea>';
    $result .= '</label>';

    if ($_SESSION['rol'] == "Cliente" and isset($datos['NoEditable'])) {
        $result .= '<label>';
        $result .= '<input type="hidden" name="id_usuario" value="' . $_SESSION['id'] . '">';
        $result .= '</label>';
    }

    if ($_SESSION['rol'] == "Recepcionista") {
        $result .= '<label class=" w-1/3 align-top mt-10 inline-block"><p class="mb-2">Seleccione el DNI del usuario y un número de habitación para mostrarla como mantenimiento</p>';
        if (isset($datos['NoEditable'])) {
            $result .= '<select name="dni_usuario">';
            $result .= '<option>' . $datos['dni_usuario'] . '</option>';
            $result .= '</select>';
            $result .= '<label>';
            $result .= '<input type="hidden" name="id_usuario" value="' . $datos['id_usuario'] . '">';
            $result .= '</label>';

            $result .= '<select name="num_habitacion">';
            $result .= '<option>' . $datos['num_habitacion'] . '</option>';
            $result .= '</select>';
            $result .= '<label>';
            $result .= '<input type="hidden" name="num_habitacion" value="' . $datos['num_habitacion'] . '">';
            $result .= '</label>';
        } else {
            $result .= '<select name="dni_usuario">';
            foreach ($datos['datos_usuario'] as $usuario) {
                $result .= '<option>' . $usuario . '</option>';
            }
            $result .= '</select>';
            $result .= '<select class="ml-3" name="num_habitacion">';
            foreach ($datos['datos_habitaciones'] as $habitaion) {
                $result .= '<option>' . $habitaion . '</option>';
            }
            $result .= '</select>';
        }
        $result .= '</label>';
    }

    $result .= '<label>';
    if (isset($datos['NoEditable'])) $result .= '<input type="submit" name="confirmar" value="Confirmar" class="py-4 w-1/3 mt-10 ml-10 font-semibold 
    border-2 border-blue-700 text-blue-700 hover:text-white hover:bg-blue-700 rounded mb-2">';
    else $result .= '<input type="submit" value="Enviar" class="py-4 w-1/3 ml-10 mt-10
    font-semibold border-2 border-blue-700 text-blue-700 hover:text-white hover:bg-blue-700 rounded mb-2">';
    $result .= '</label>';

    $result .= '</form>';
    $result .= '</section>';

    return $result;
}

function _HTMLshowConfirmarReserva($datos)
{
    $rol = $_SESSION['rol'];
    $action =  $datos['SCRIPT_NAME'] . '?p=anadir_reserva&fase=segunda';
    $result = '<section class="p-10 bg-gray-100">';
    $result .= '<div class="p-4 bg-white rounded-lg">';
    if (isset($datos['mantenimiento'])) $result .= '<h2 class="font-semibold text-xl p-4"> La siguiente habitacion será puesta en mantenimiento </h2>';
    else $result .= '<h2 class="font-semibold text-xl p-4"> Hemos encontrado una habitación acorde a tus necesidades, ¿Que te parece? </h2>';
    $result .= '<div class="flex">';
    $result .= '<div class="text-right text-lg mr-4 h-full"><p>Numero de personas: </p><p>Fecha de llegada: </p><p>Fecha de salida: </p><p>Comentarios: </p><p>Número de la habitación asignada: </p><p>Precio por noche: </p></div>';
    $result .= '<div class="text-lg font-semibold"><p>' . $datos['n_personas'] . '</p><p>' . $datos['fecha_inicio'] . '</p><p>' . $datos['fecha_fin'] . '</p>';
    if($datos['comentarios']=="") $result .= '<p>nada</p>';
    else $result .= '<p>' . $datos['comentarios'] . '</p>';
    $result .= '<p>' . $datos['numero_hab'] . '</p><p>' . $datos['precio'] . '&euro;</p></div>';
    $result .= '</div>';
    $result .= '<div class="inline-block">';
    $result .= '<a href="' . $action . '&aceptar=' . $datos['id_reserva_creada'] . '" class="inline-block mt-10 ml-36 p-3 font-bold text-xl bg-gray-200 text-blue-700 hover:bg-blue-700 rounded-lg hover:text-white">Aceptar</a>';
    $result .= '<a href="' . $action . '&cancelar=' . $datos['id_reserva_creada'] . '" class="ml-16 p-3 font-bold text-xl bg-gray-200 text-red-700 hover:bg-red-700 rounded-lg hover:text-white">Cancelar</a>';
    $result .= '</div>';
    $result .= '</div>';
    $result .= '</section>';

    return $result;
}

function _HTMLshowEditarReserva($datos)
{
    $rol = $_SESSION['rol'];
    $action =  $datos['SCRIPT_NAME'] . '?p=editar_reserva';
    $result = '<section class="p-10 bg-gray-100">';
    $result .= '<form class="p-4 pl-10 bg-white rounded-lg " action="' . $action . '" method="post" novalidate>';
    $result .= '<h2 class="font-semibold text-xl p-6"> Modificando reserva </h2>';

    $result .= '<label class="w-1/3 inline-block"><p>Nº de personas</p>';
    $result .= '<input class="block p-1 px-3 mx-3 mt-2 w-4/5 border-2 border-gray-200 rounded-lg" type="text" readonly name="n_personas" value=' . $datos['reserva']->devolverValor('n_personas') . ' >';
    $result .= '</label>';

    $result .= '<label class="w-1/3 inline-block"><p>Fecha de llegada</p>';
    $result .= '<input class="block p-1 px-3 mt-2 w-4/5 border-2 border-gray-200 rounded-lg" type="date" readonly name="fecha_inicio" value=' . $datos['reserva']->devolverValor('fecha_inicio') . ' >';
    $result .= '</label>';

    $result .= '<label class="w-1/3 inline-block"><p>Fecha de salida</p>';
    $result .= '<input class="block p-1 px-3 mt-2 w-4/5 border-2 border-gray-200 rounded-lg" type="date" readonly name="fecha_fin" value=' . $datos['reserva']->devolverValor('fecha_fin') . ' >';
    $result .= '</label>';

    $result .= '<label class="mt-10 inline-block w-2/3 pr-10"><p>Comentarios</p>';
    if (isset($datos['no_editar'])) $result .= '<textarea class="block p-1 px-3 mt-2 w-full border-2 border-gray-200 rounded-lg" type="text" readonly name="comentarios">' . $datos['comentarios'] . '</textarea>';
    else $result .= '<textarea class="block p-1 px-3 mt-2 w-full border-2 border-gray-200 rounded-lg" type="text" name="comentarios">' . $datos['comentarios'] . '</textarea>';
    $result .= '</label>';

    $result .= '<label>';
    $result .= '<input type="hidden" name="id" value="' . $datos['reserva']->devolverValor('id') . '">';
    $result .= '</label>';

    $result .= '<label>';
    if (isset($datos['no_editar'])) $result .= '<input type="submit" name="Confirmar" value="Confirmar" class="py-4 w-1/3 mt-10 block ml-10 font-semibold 
    border-2 border-blue-700 text-blue-700 hover:text-white hover:bg-blue-700 rounded mb-2">';
    else $result .= '<input type="submit" name="Enviar" value="Enviar" class="py-4 w-1/3 ml-10 mt-10
    font-semibold border-2 border-blue-700 text-blue-700 hover:text-white hover:bg-blue-700 rounded mb-2">';
    $result .= '</label>';

    $result .= '</form>';
    $result .= '</section>';

    return $result;
}
