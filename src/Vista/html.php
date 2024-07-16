<?php

function _HTMLhead($titulo)
{
    $result = <<<HTML
    <head>
      <meta charset="utf-8">
      <title>$titulo</title>
      <link rel="stylesheet" href="../public/css/styles.css">
      <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    </head>
    HTML;
    return $result;
}

function _HTMLnavegacion($datos, $login)
{
    $result = <<<HTML
      <nav
          class="flex items-center justify-between flex-wrap bg-white py-4 lg:px-12 shadow border-solid border-t-2 border-blue-700">
          <div class="flex justify-between lg:w-auto w-full lg:border-b-0 pl-6 pr-2 border-solid border-b-2 border-gray-300 pb-5 lg:pb-0">
              <div class="flex items-center flex-shrink-0 text-gray-800 mr-16">
                  <span class="font-bold text-xl tracking-tight font-serif" >M&M Hotels</span>
              </div>
          </div>
          <div class="menu w-full flex-grow lg:flex lg:items-center lg:w-auto lg:px-3 px-8">
              <div class="text-md font-bold text-blue-700 lg:flex-grow">
      HTML;
    foreach ($datos as $item) {

        $result .= '<a href="' . $item['url'] . '"';
        $result .= ' class="block mt-4 lg:inline-block lg:mt-0 hover:text-white px-4 py-2 rounded hover:bg-blue-700 mr-2">';
        $result .= $item['texto'] . '</a>';
    }

    $result .= '</div>';

    if (isset($_SESSION['rol'])) {

        $result .= '<div class="flex ">';
        foreach ($login as $item) {

            $result .= '<a href="' . $item['url'] . '" class=" block text-md px-4 py-2 rounded text-blue-700 ml-2 font-bold hover:text-white mt-4 hover:bg-blue-700 lg:mt-0">' . $item['texto'] . '</a>';
        }
        $result .= '</div>';
    }
    $result .= '</div>';
    $result .= '</nav>';

    return $result;
}

function _HTMLshowHome()
{
    $result = <<<HTML
    <section class="h-fit w-full bg-cover bg-fixed flex items-center justify-items-center flex-col" style="background-image: url(../public/images/portada_hotel.webp)">
        <h2 class="p-32 font-serif text-4xl">Descubre el lujo y confort en M&M Hotel</h2>
        <div class="flex">
            <section class="flex-auto w-4/5 p-14 m-10 mt-4 bg-white rounded-lg bg-opacity-95 text-justify">
                <h3 class="m-4 mb-8 font-semibold font-serif text-xl border-b-2">Bienvenidos a M&M Hotel</h3>
                <p class="m-4 ">
                    En M&M Hotel, te invitamos a experimentar el lujo y la comodidad en su máxima expresión. Situados en ubicaciones privilegiadas, nuestros hoteles combinan 
                    elegancia moderna con un servicio excepcional para crear una estancia inolvidable. Desde nuestras habitaciones exquisitamente decoradas hasta nuestras instalaciones 
                    de primer nivel, cada detalle está diseñado para ofrecerte una experiencia única y personalizada. Disfruta de nuestra exquisita gastronomía, relájate en nuestros spas 
                    de clase mundial, y déjate mimar por nuestro atento personal. En M&M Hotel, tu satisfacción es nuestra prioridad.
                </p>
                <p class="m-4 ">
                    Nuestras habitaciones están diseñadas pensando en tu confort y estilo. Con una decoración sofisticada y vistas impresionantes, cada estancia se convierte 
                    en un refugio de paz y tranquilidad. Disfruta de comodidades de lujo, como camas king-size, ropa de cama de alta calidad, y baños equipados con productos 
                    exclusivos. Además, todas nuestras habitaciones cuentan con la última tecnología para que te sientas como en casa.
                </p>
                <p class="m-4 ">
                    En M&M Hotel, la experiencia culinaria es una parte esencial de tu estancia. Nuestros chefs galardonados preparan una variedad de platos que combinan ingredientes 
                    frescos y técnicas innovadoras para ofrecerte una experiencia gastronómica inigualable. Desde desayunos gourmet hasta cenas elegantes, cada comida es una celebración 
                    de sabores y creatividad. No olvides visitar nuestro bar para disfrutar de una selección de cócteles artesanales y vinos finos.
                </p>
                <p class="m-4 ">
                    Nuestro compromiso con tu bienestar se refleja en nuestras instalaciones de spa y fitness. Relájate y rejuvenece en nuestro spa de clase mundial, donde ofrecemos 
                    una amplia gama de tratamientos personalizados. Desde masajes terapéuticos hasta tratamientos faciales revitalizantes, nuestros expertos te ayudarán a encontrar 
                    el equilibrio perfecto entre cuerpo y mente. Para aquellos que buscan mantenerse activos, nuestro gimnasio de última generación está equipado con todo lo necesario 
                    para tu rutina de ejercicios.                
                </p>
            </section>
            <aside class="h-96 flex-auto w-1/5 pt-14 p-5 m-5 mt-4 bg-white rounded-lg bg-opacity-95 ">
                <h3 class="m-4 mb-8 font-semibold font-serif text-xl border-b-2 text-center">Información</h3>
    HTML;

    $nHabitaciones = Habitacion::obtenerHabitaciones(0, 100000, 'id')[1];
    $nHuespedesActuales = Reserva::contarHuespedesActuales();

    $reservas = Reserva::obtenerReservas(0, 100000, 'id', 'Confirmada')[0];
    $nReservas = 0;

    //Contar cuantas reservas hay en el momento actual
    foreach ($reservas as $reserva) {
       $reserva->devolverValor('fecha_inicio') <= date('Y-m-d') && $reserva->devolverValor('fecha_fin') >= date('Y-m-d') ? $nReservas++ : null;
    }

    $nHabitacionesLibres = $nHabitaciones - $nReservas;

    $nHuespedesTotales = Habitacion::capacidadTotalHotel();

    $result .= "<p>Nº habitaciones: $nHabitaciones</p>
                <p>Nº habitaciones libres: $nHabitacionesLibres</p>
                <p>Nº huespedes total: $nHuespedesTotales</p>
                <p>Nº huespedes actual: $nHuespedesActuales</p>
            </aside>
        </div>
    </section>";

    return $result;
}

function _HTMLshowServicios()
{
    $result = <<<HTML
    <section class="h-56 w-full bg-contain bg-fixed" style="background-image: url(../public/images/servicios_hotel_portada.jpg)">
    </section>
    <section class="m-16">
        <h2 class="m-10 font-semibold font-serif text-3xl border-b-2">Nuestros Servicios</h2>
        <p class="mx-24 text-justify">
            En M&M Hotel, nos enorgullecemos de ofrecer una gama completa de servicios diseñados para hacer que tu estancia sea excepcional. Desde el momento en que llegas, nuestro equipo dedicado está aquí para atender cada una de tus necesidades y asegurarse de que disfrutes de una experiencia inolvidable. Descubre nuestros servicios exclusivos y déjate sorprender por el lujo y la comodidad que solo M&M Hotel puede ofrecer.
        </p>
    </section>
    <section class="p-10 h-fit w-full bg-cover bg-fixed" style="background-image: url(../public/images/comedor.jpg)">
        <div class="p-10 w-2/3 bg-white bg-opacity-95 text-justify rounded-lg">
            <h2 class="m-3 font-semibold font-serif text-xl border-b-2">Servicio de Comedor</h2>
            <p class="m-7">
                En M&M Hotel, nuestro servicio de comedor está diseñado para deleitar tus sentidos en cada comida del día. Ofrecemos desayunos energizantes, almuerzos deliciosos y cenas gourmet en un ambiente elegante y acogedor. Nuestro talentoso equipo de chefs utiliza ingredientes frescos y de alta calidad para preparar platos que combinan sabores locales e internacionales, asegurando una experiencia culinaria excepcional.            
            </p>
            <p class="m-7">
                Además, nuestro bar está disponible todo el día, ofreciendo una selección exclusiva de bebidas y cócteles artesanales. Ya sea que desees relajarte con un café por la mañana, disfrutar de un refrescante cóctel por la tarde o finalizar tu día con una copa de vino selecto, nuestro bar es el lugar perfecto para cada ocasión.            
            </p>
            <p class="m-7">
                En M&M Hotel, nos comprometemos a ofrecerte la mejor comida y el mejor servicio, para que cada momento que pases con nosotros sea inolvidable.
            </p>
        </div>
    </section>
    <section class="h-3 w-full">
    </section>
    <section class="p-10 h-fit w-full bg-cover bg-fixed" style="background-image: url(../public/images/spa.jpg)">
        <div class="p-10 ml-96 w-2/3 bg-white bg-opacity-95 text-justify rounded-lg">
            <h2 class="m-3 font-semibold font-serif text-xl border-b-2">Experiencia en el SPA</h2>
            <p class="m-7">
                En M&M Hotel, tu bienestar es nuestra prioridad, y nuestro servicio de spa está diseñado para ofrecerte una experiencia de relajación y rejuvenecimiento incomparable. Sumérgete en un mundo de tranquilidad y revitalización en nuestro lujoso spa, donde te esperan una variedad de tratamientos y servicios diseñados para nutrir tu cuerpo, mente y espíritu.
            </p>
            <p class="m-7">
                Desde masajes terapéuticos hasta tratamientos faciales rejuvenecedores, cada servicio en nuestro spa es realizado por terapeutas expertos que utilizan técnicas avanzadas y productos de alta calidad para garantizar resultados óptimos. Ya sea que desees aliviar el estrés, mejorar la circulación o simplemente desconectar del mundo exterior, nuestro spa es el refugio perfecto para encontrar equilibrio y armonía.    
            </p>
            <p class="m-7">
                Además de nuestros tratamientos individuales, también ofrecemos paquetes de spa diseñados para ofrecerte una experiencia completa de bienestar. Sumérgete en la serenidad de nuestro entorno y déjate llevar por la sensación de paz y rejuvenecimiento que solo nuestro spa puede ofrecer. En M&M Hotel, te invitamos a cuidar de ti mismo y a disfrutar de una experiencia de spa que superará todas tus expectativas.
            </p>
        </div>
    </section>
    <section class="m-16 text-justify flex flex-wrap">
        <h2 class="m-10 w-full font-semibold font-serif text-xl border-b-2">Organización de Actividades por Granada</h2>
        <div class="flex-auto w-1/2">
            <p class="mt-2">
                En H&H Hotels, nos esforzamos por ofrecer una experiencia completa y enriquecedora durante tu estancia en Granada. Como parte de nuestros servicios exclusivos, ofrecemos la organización de actividades para que puedas explorar y disfrutar de todo lo que esta maravillosa ciudad tiene para ofrecer.
            </p>
            <p class="mt-2">
                Desde visitas guiadas a los monumentos más emblemáticos hasta excursiones a los pintorescos pueblos de los alrededores, nuestro equipo dedicado está aquí para ayudarte a planificar y coordinar una variedad de actividades que se adapten a tus intereses y preferencias.
            </p>
            <p class="mt-2">
                Ya sea que desees sumergirte en la rica historia y cultura de Granada, explorar sus encantadoras calles empedradas o disfrutar de la impresionante belleza natural de la región, estamos aquí para asegurarnos de que tu tiempo en Granada sea inolvidable.
            </p>
            <p class="mt-2">
                Déjanos encargarnos de todos los detalles para que puedas relajarte y disfrutar al máximo de tu estancia en esta fascinante ciudad. En H&H Hotels, estamos comprometidos a hacer que cada momento de tu viaje sea una experiencia memorable y enriquecedora.
            </p>
        </div>
        <div class="flex-auto w-1/2 p-14">
            <img src="../public/images/granada_calles.webp" alt="Descripción de la imagen">
        </div>
    </section>
    <section class="p-10 h-fit w-full bg-cover bg-fixed" style="background-image: url(../public/images/piscina.jpg)">
        <div class="p-10 w-2/3 bg-white bg-opacity-95 text-justify rounded-lg">
            <h2 class="m-3 font-semibold font-serif text-xl border-b-2">Servicio de Piscina</h2>
            <p class="m-7">
                Disfruta de un oasis de relajación en la piscina de M&M Hotel, donde cada detalle está diseñado para ofrecerte una experiencia refrescante y revitalizante. Nuestra espectacular piscina, rodeada de un entorno elegante y sereno, es el lugar ideal para desconectar y disfrutar del sol.            
            </p>
            <p class="m-7">
                Abierta durante todo el día, la piscina ofrece cómodas tumbonas y sombrillas para que puedas relajarte mientras tomas el sol. Nuestro personal atento está siempre a tu disposición para ofrecerte toallas limpias y bebidas refrescantes, asegurando que tengas todo lo que necesitas para una experiencia perfecta.
            </p>
            <p class="m-7">
                Además, contamos con una zona de piscina infantil, para que los más pequeños también puedan disfrutar de un chapuzón seguro y divertido. Ya sea que desees nadar unos largos, relajarte en una tumbona o disfrutar de un cóctel junto a la piscina, el servicio de piscina de M&M Hotel te promete un día de pura satisfacción y placer.
            </p>
        </div>
    </section>
    <section class="h-3 w-full">
    </section>
    <section class="p-10 h-fit w-full bg-cover bg-fixed" style="background-image: url(../public/images/gimnasio.jpeg)">
        <div class="p-10 ml-96 w-2/3 bg-white bg-opacity-95 text-justify rounded-lg">
            <h2 class="m-3 font-semibold font-serif text-xl border-b-2">Gimnasio</h2>
            <p class="m-7">
                En M&M Hotel, nos enorgullece ofrecer un Fitness Center de clase mundial, donde podrás mantener tu rutina de ejercicio incluso mientras estás lejos de casa. Nuestras instalaciones están equipadas con lo último en equipos de fitness de alta tecnología, diseñados para satisfacer las necesidades de todos, desde principiantes hasta atletas experimentados.    
            </p>
            <p class="m-7">
                Con una amplia gama de máquinas cardiovasculares, pesas libres y equipos de entrenamiento funcional, encontrarás todo lo que necesitas para lograr tus objetivos de fitness. Nuestro equipo de entrenadores certificados está disponible para ofrecerte asesoramiento personalizado y ayudarte a diseñar un programa de ejercicios que se adapte a tus necesidades y objetivos individuales.
            </p>
            <p class="m-7">
                Además, ofrecemos una variedad de clases de fitness dirigidas por instructores altamente cualificados, que incluyen yoga, pilates, spinning y más. Ya sea que prefieras un entrenamiento en solitario o te unas a una clase grupal, nuestro Fitness Center te proporcionará el ambiente perfecto para alcanzar tus metas de fitness y mejorar tu bienestar general.
            </p>
            <p class="m-7">
                Después de tu entrenamiento, no olvides relajarte y recuperarte en nuestras instalaciones de bienestar, que incluyen saunas, baños de vapor y áreas de relajación. En M&M Hotel, te invitamos a cuidar de tu cuerpo y mente mientras disfrutas de una experiencia de fitness incomparable.
            </p>
        </div>
    </section>
    <section class="h-3 w-full">
    </section>
    HTML;
    return $result;
}

function _HTMLshowLog($datos)
{
    $action = '?p=log';
    $paginaActual = $datos['paginaActual'];
    $totalPaginas = $datos['numeroPaginas'];

    if ($_SESSION['rol'] != 'Administrador') {
        return _HTMLshowInfo('No tienes permisos para acceder a esta página', 'error');
    }

    $result = <<<HTML
    <section class="h-56 w-full bg-cover bg-fixed" style="background-image: url(../public/images/portada_hotel.webp)"></section>

    <section class="m-16">
        <h2 class="m-10 font-semibold font-serif text-3xl border-b-2">Logs</h2>
        <table class="w-full table-auto">
            <thead>
                <tr>
                    <th class="border px-4 py-2">Fecha y Hora</th>
                    <th class="border px-4 py-2">Descripción</th>
                </tr>
            </thead>
            <tbody>
    HTML;

    foreach ($datos['logs'] as $log) {
        $result .= '<tr>';
        $result .= '<td class="border px-4 py-2">' . $log->devolverValor('fecha_hora') . '</td>';
        $result .= '<td class="border px-4 py-2">' . $log->devolverValor('descripcion') . '</td>';
        $result .= '</tr>';
    }

    $result .= <<<HTML
            </tbody>
        </table>

        <div class="mt-4 flex space-x-96">    
            <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Paginacion">
    HTML;

    for ($i = 1; $i <= $totalPaginas; $i++) {
        if ($i == $paginaActual) {
            $result .= "<span class='relative inline-flex items-center px-4 py-2 border border-gray-300 bg-blue-500 text-sm font-medium text-white'>";
        } else {
            $result .= "<a href='?p=log&pagina=$i' class='relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50'>";
        }

        $result .= $i;

        if ($i == $paginaActual) {
            $result .= "</span>";
        } else {
            $result .= "</a>";
        }
    }

    $result .= <<<HTML
            </nav>
        </div>

    </section>
    HTML;

    return $result;
}

function _HTMLshowFormularioIniciarSesion($datos)
{
    $action = $datos['SCRIPT_NAME'] . '?p=validate_inicio';
    $result = <<<HTML
    <div class="flex justify-center items-center bg-gray-100 p-10">
    <div class="w-full md:w-1/3 flex flex-col items-center bg-white py-6 rounded-3xl" >
    <h3 class="text-center text-2xl font-bold text-gray-600 mb-16">Log in</h3>
    HTML;
    $result .= '<form action="' . $action . '" method="post" novalidate class="w-full text-center">';
    $result .= <<<HTML
        <div class="w-3/4 mb-6 mx-auto flex-col items-center">
            <input type="email" name="email" class="border-2 w-full py-3 px-4 bg-slate-200 placeholder:font-semibold rounded-lg hover:ring-1 outline-blue-500" placeholder="Correo Electrónico">
    HTML;
    if (isset($datos['errores']['email'])) {
        $result .= '<p class="text-red-500 text-sm text-left">' . $datos['errores']['email'] . '</p>';
    }
    $result .= <<<HTML
        </div>

        <div class="w-3/4 mb-6 mx-auto flex-col items-center">
            <input type="password" name="clave" class="border-2 w-full py-3 px-4 bg-slate-200 placeholder:font-semibold rounded-lg hover:ring-1 outline-blue-500 " placeholder="Contraseña">
        HTML;
    if (isset($datos['errores']['clave'])) {
        $result .= '<p class="text-red-500 text-sm text-left">' . $datos['errores']['clave'] . '</p>';
    }
    $result .= <<<HTML
        </div>

        <div class="w-3/4 mt-10 mx-auto flex items-center">
            <button type="submit" class="w-full py-4 font-semibold border-2 border-blue-700 text-blue-700 hover:text-white hover:bg-blue-700 rounded mb-2">Iniciar Sesión</button>
        </div>
    </form>
    <div class="w-4/5 flex flex-row mx-auto justify-end">
        <div>
            <a href="./index.php?p=registrarse" class="text-sm text-slate-400 hover:text-blue-700">¿Aún no registrado?</a>
        </div>
    </div>
    </div>
    </div>
    HTML;
    return $result;
}

function _HTMLshowBaseDatos($datos)
{
    if ($_SESSION['rol'] != 'Administrador') {
        return _HTMLshowInfo('No tienes permisos para acceder a esta página', 'error');
    }

    $action = $datos['SCRIPT_NAME'] . '?p=base_datos';

    $result = <<<HTML
    <section class="h-56 w-full bg-cover bg-fixed" style="background-image: url(../public/images/portada_hotel.webp)"></section>
    <section class="m-16">
        <h2 class="m-10 font-semibold font-serif text-3xl border-b-2">Base de Datos</h2>
    </section>

    <section class="m-16 flex justify-center items-center">
        <form action="$action" method="post" enctype="multipart/form-data" novalidate>
            <button type="submit" name="boton" value="backup" class="py-4 px-2 font-semibold border-2 border-blue-700 text-blue-700 hover:text-white hover:bg-blue-700 rounded mr-4">Crear Copia de Seguridad</button>
            <button type="submit" name="boton" value="restore" class="py-4 px-2 font-semibold border-2 border-blue-700 text-blue-700 hover:text-white hover:bg-blue-700 rounded mr-4" onclick="return confirmarRestore()">Restaurar Copia de Seguridad</button>
            <button type="submit" name="boton" value="reset" class="py-4 px-2 font-semibold border-2 border-blue-700 text-blue-700 hover:text-white hover:bg-blue-700 rounded mr-4" onclick="return confirm('¿Estás seguro de que quieres resetear la base de datos?')">Reiniciar Base de Datos</button>
            <input type="file" name="backup" value="backup" class="w-full mt-4">
        </form>
    </section>
    
    <script>
    function confirmarRestore() {
        var fileInput = document.querySelector('input[type=file]');
        if (fileInput.files.length > 0) {
            return confirm('¿Estás seguro de que quieres restaurar la base de datos desde este backup?');
        } else {
            alert('Por favor, selecciona un backup antes de restaurar.');
            return false;
        }
    }
    </script>

    HTML;

    return $result;
}

function _HTMLshowFormularioRegistrarse($datos)
{
    $editable = isset($_SESSION['formulario_enviado']) ? false : true;


    $action = $datos['SCRIPT_NAME'] . '?p=validate_registro';
    $campos = [
        ['nombre', 'text', 'Nombre'],
        ['apellidos', 'text', 'Apellidos'],
        ['dni', 'text', 'DNI'],
        ['email', 'email', 'Correo Electrónico'],
        ['clave', 'password', 'Contraseña'],
        ['numero_tarjeta', 'text', 'Tarjeta de Crédito'],
    ];

    $result = <<<HTML
    <div class="flex justify-center items-center py-7 bg-gray-100">
        <div class="w-full md:w-1/2 flex flex-col items-center bg-white py-5 rounded-3xl">
            <h3 class="text-center text-2xl font-bold text-gray-600 mb-12 mt-4">Register</h3>
            <form action="$action" method="post" novalidate class="w-full text-center" novalidate>
    HTML;
    $contador = 0;
    foreach ($campos as $campo) {
        if($contador%2==0) $result .= '<div class="">';
        $result .= crearInputFormulario($campo[0], $campo[1], $campo[2], $datos, $editable);
        if($contador%2==1) $result .= "</div>"; 
        $contador = $contador + 1;
    }

    if ($editable) {
        $result .= <<<HTML
            <div class="w-3/4 mt-4 mx-auto flex-col items-center">
                <button type="submit" class="py-4 w-full font-semibold border-2 border-blue-700 text-blue-700 hover:text-white hover:bg-blue-700 rounded mb-2">Crear Cuenta</button>
            </div>
        HTML;
    } else {
        $result .= <<<HTML
            <div class="w-3/4 mt-4 mx-auto flex-col items-center">
                <button type="submit" class="py-4 bg-green-400 w-full rounded text-blue-50 font-bold hover:bg-green-700 mb-2">Confirmar Registro</button>
            </div>
        HTML;
    }

    if (isset($_SESSION['formulario_enviado'])) {
        $result .= '<input type="hidden" name="guardar" value="1">';
    }

    $result .= <<<HTML
                <input type="hidden" name="rol" value="Cliente">
            </form>
            <div class="w-4/5 flex flex-row mx-auto justify-end">
                <div>
                    <a href="./index.php?p=iniciar_sesion" class="text-sm text-slate-400 hover:text-blue-700">¿Ya tienes cuenta?</a>
                </div>
            </div>
        </div>
    </div>
    HTML;

    return $result;
}

function _HTMLshowFormularioEditarDato($datos)
{
    $editable = isset($_SESSION['formulario_enviado']) ? false : true;

    $action = $datos['SCRIPT_NAME'] . '?p=cliente_editar_datos';
    $campos = [
        ['nombre', 'text', 'Nombre'],
        ['apellidos', 'text', 'Apellidos'],
        ['dni', 'text', 'DNI'],
        ['email', 'email', 'Correo Electrónico'],
        ['clave', 'password', 'Contraseña'],
        ['numero_tarjeta', 'text', 'Tarjeta de Crédito'],
    ];

    $result = <<<HTML
    <div class="flex justify-center items-center my-7">
        <div class="w-full md:w-1/2 flex flex-col items-center bg-blue-100 py-5 rounded-3xl">
            <h3 class="text-center text-2xl font-bold text-gray-600 mb-6">Editar Datos</h3>
            <form action="$action" method="post" novalidate class="w-full text-center">
    HTML;
    
    foreach ($campos as $campo) {
        // Solo permitir la edición de 'email', 'clave' y 'numero_tarjeta'
        $campoEditable = $editable && in_array($campo[0], ['email', 'clave', 'numero_tarjeta']);
        
        $result .= crearInputFormulario($campo[0], $campo[1], $campo[2], $datos, $campoEditable);

    }

    if ($editable) {
        $result .= <<<HTML
            <div class="w-3/4 mt-4 mx-auto flex-col items-center">
                <button type="submit" class="py-4 bg-blue-400 w-full rounded text-blue-50 font-bold hover:bg-blue-700 mb-2">Crear Cuenta</button>
            </div>
        HTML;
    } else {
        $result .= <<<HTML
            <div class="w-3/4 mt-4 mx-auto flex-col items-center">
                <button type="submit" class="py-4 bg-green-400 w-full rounded text-blue-50 font-bold hover:bg-green-700 mb-2">Confirmar Registro</button>
            </div>
        HTML;
    }

    if (isset($_SESSION['formulario_enviado'])) {
        $result .= '<input type="hidden" name="guardar" value="1">';
    }

    $result .= <<<HTML
                <input type="hidden" name="rol" value="Cliente">
            </form>
        </div>
    </div>
    HTML;

    return $result;
}

function crearInputFormulario($name, $type, $placeholder, $datos, $editable)
{
    $value = isset($datos['params'][$name]) ? htmlspecialchars($datos['params'][$name], ENT_QUOTES, 'UTF-8') : '';

    $error = isset($datos['errores'][$name]) ? '<p class="text-red-500 text-sm text-left">' . htmlspecialchars($datos['errores'][$name], ENT_QUOTES, 'UTF-8') . '</p>' : '';

    if ($editable) {
        $input = <<<HTML
        <input type="$type" name="$name" class=" border-2 w-full inline-block py-3 px-4 bg-slate-200 placeholder:font-semibold rounded-lg hover:ring-1 outline-blue-500" placeholder="$placeholder" value="$value">
        HTML;
    } else {
        if($type=="password") $input = '<div class="border-2 w-full py-3 px-4 inline-block bg-slate-200 rounded-lg">****</div>';
        else $input = '<div class="border-2 w-full py-3 px-4 inline-block bg-slate-200 rounded-lg">'.$value.'</div>';
        $input .= '<input type="hidden" name="'.$name.'" value="'.$value.'">';
    }

    return <<<HTML
    <div class="w-1/2 mb-6 mx-auto px-5 inline-block flex-col items-center">
        $input
        $error
    </div>
    HTML;
}

function _HTMLshowVerUsuarios($data)
{
    if ($_SESSION['rol'] != 'Administrador' && $_SESSION['rol'] != 'Recepcionista') {
        return _HTMLshowInfo('No tienes permisos para acceder a esta página', 'error');
    }

    $totalPaginas = $data['totalPaginas'];
    $paginaActual = $data['paginaActual'];

    $result = <<<HTML
    <script>
    function confirmarBorrado() {
        return confirm('¿Estás seguro de que quieres eliminar este usuario?');
    }
    </script>
    <section class="h-56 w-full bg-cover bg-fixed" style="background-image: url(../public/images/portada_hotel.webp)"></section>
    <section class="m-16  mb-2">
        <h2 class="m-10 mb-0 font-semibold font-serif text-3xl border-b-2">Usuarios</h2>
    </section>

    <section class="m-16 mt-0">
        <form class="text-center" action="?p=registrarse" method="post" novalidate>
            <button class="m-10 py-4 bg-white w-1/4 rounded border-2 border-blue-700 text-blue-700 font-bold hover:text-white hover:bg-blue-700 mt-4">Añadir Usuario</button>
        </form>
        <table class="w-full table-auto">
            <thead>
                <tr>
                    <th class="border px-4 py-2">Nombre</th>
                    <th class="border px-4 py-2">Apellidos</th>
                    <th class="border px-4 py-2">DNI</th>
                    <th class="border px-4 py-2">Email</th>
                    <th class="border px-4 py-2">Tarjeta de Crédito</th>
                    <th class="border px-4 py-2">Rol</th>
                </tr>
            </thead>
            <tbody>
    HTML;

    $usuarios = $data['usuarios'];
    $data['usuarios']['dni'] = $data['usuarios'][0]->devolverValor('dni');

    foreach ($usuarios as $usuario) {
        $result .= '<tr>';
        $result .= '<td class="border px-4 py-2">' . $usuario->devolverValor('nombre') . '</td>';
        $result .= '<td class="border px-4 py-2">' . $usuario->devolverValor('apellidos') . '</td>';
        $result .= '<td class="border px-4 py-2">' . $usuario->devolverValor('dni') . '</td>';
        $result .= '<td class="border px-4 py-2">' . $usuario->devolverValor('email') . '</td>';
        $result .= '<td class="border px-4 py-2">' . $usuario->devolverValor('numero_tarjeta') . '</td>';
        $result .= '<td class="border px-4 py-2">' . $usuario->devolverValor('rol') . '</td>';
        $result .= '<td class="border px-4 py-2"><a href="' . $data['SCRIPT_NAME'] . '?p=editar_datos_usuario&dni_anterior=' . urlencode($usuario->devolverValor('dni')) . '" class="text-blue-700 hover:underline">Editar</a></td>';
        $result .= '<td class="border px-4 py-2"><a href="' . $data['SCRIPT_NAME'] . '?p=eliminar_usuario&dni=' . urlencode($usuario->devolverValor('dni')) . '" 
        class="text-red-700 hover:underline" onclick="return confirmarBorrado();">Eliminar</a></td>';
        $result .= '</tr>';
    }

    $result .= <<<HTML
            </tbody>
        </table>
        <div class="mt-4">
            <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Paginacion">
    HTML;

    for ($i = 1; $i <= $totalPaginas; $i++) {
        if ($i == $paginaActual) {
            $result .= "<span class='relative inline-flex items-center px-4 py-2 border border-gray-300 bg-blue-500 text-sm font-medium text-white'>";
        } else {
            $result .= "<a href='?p=ver_usuarios&pagina=$i' class='relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50'>";
        }

        $result .= $i;

        if ($i == $paginaActual) {
            $result .= "</span>";
        } else {
            $result .= "</a>";
        }
    }

    $result .= <<<HTML
            </nav>
        </div>
    </section>
    HTML;

    return $result;
}

function _HTMLfooter()
{
    $result = <<<HTML
    <footer class="bg-blue-700 text-white text-center py-4 w-full justify-center flex">
        <div class="w-1/2 px-4">
            <p>&copy; 2024 M&M Hotels. Todos los derechos reservados.</p>
            <p>Proyecto hecho por Jose Antonio Marqués Ponce y Juan Pedro Moreno Ruiz</p>
        </div>

        <div class="w-1/2 px-4">
            <a href="../public/restaurarBD" class="text-white hover:text-blue-200 mx-2 block">Restaurar BBDD con datos de prueba</a>
            <a href="../public/Informe.pdf" class="text-white hover:text-blue-200 mx-2 block">Documentación prácticas</a>
        </div>
    </footer>
    HTML;
    return $result;
}

function _HTMLshowInfo($msg, $cls)
{
    $res = '<script>
                setTimeout(function() {
                    var element = document.getElementById("infoSection");
                    element.parentNode.removeChild(element);
                }, 3000);
             </script>';

    if ($cls == "info") {
        $res .= '<section id="infoSection" class="bg-green-300 opacity-90 fixed left-64 top-5 w-1/2 h-20 font-semibold pt-6 text-center text-2xl m-10 p-4 rounded-lg">';
        $res .= _HTMLrecursivePrint($msg);
        $res .= '</section>';
    } else if ($cls == "error") {
        $res = '<section id="infoSection" class="m-10 p-4 bg-red-200 font-semibold rounded-lg">';
        $res .= _HTMLrecursivePrint($msg);
        $res .= '</section>';
    }
    return $res;
}

function _HTMLrecursivePrint($msg)
{
    if (is_array($msg))
        if (count($msg) == 0)
            return '';
        else
            return _HTMLrecursivePrint($msg[0]) . _HTMLrecursivePrint(array_slice($msg, 1));
    else
        return "<p>$msg</p>";
}
