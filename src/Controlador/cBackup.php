<?php
require_once '../src/Vista/html.php';
require_once '../config/conexion.php';

class cBackup
{
    private $data;
    private $request;

    public function __construct($data, $request)
    {
        $this->data = $data;
        $this->request = $request;
    }

    public function base_datos()
    {
        $this->data['base_datos'] = "base_datos";
        $this->data['SCRIPT_NAME'] = $this->request['server']['SCRIPT_NAME'];

        //Depende el boton que se pulse se ejecutara una funcion
        if (isset($_POST['boton'])) {
            switch ($_POST['boton']) {
                case "backup":
                    $this->crearBackup();
                    break;

                case "restore":
                    $this->restaurarBackup();
                    break;

                case "reset":
                    $this->resetearBaseDatos();
                    break;
            }
        }
    }

    private function crearBackup()
    {
        $db = self::conectar();

        // Obtener todas las tablas
        $tablas = $db->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);

        $backup = '';

        $backup .= "SET FOREIGN_KEY_CHECKS = 0;\n\n";

        // Borrar cada tabla 
        foreach ($tablas as $tabla) {
            $backup .= "DROP TABLE IF EXISTS $tabla;\n\n";
        }

        foreach ($tablas as $tabla) {
            // Para cada tabla, obtener las columnas y las tuplas
            $create = $db->query("SHOW CREATE TABLE $tabla")->fetch(PDO::FETCH_ASSOC);
            $backup .= $create['Create Table'] . ";\n\n";

            $tuplas = $db->query("SELECT * FROM $tabla")->fetchAll(PDO::FETCH_NUM);
            foreach ($tuplas as $tupla) {
                $values = array_map([$db, 'quote'], $tupla);
                $backup .= "INSERT INTO $tabla VALUES(" . implode(', ', $values) . ");\n";
            }
            $backup .= "\n\n";
        }

        $backup .= "SET FOREIGN_KEY_CHECKS = 1;\n";

        $backup .= "-- Fin del backup";

        // Guardar el backup en un archivo temporal
        $file = tempnam(sys_get_temp_dir(), 'backup');
        file_put_contents($file, $backup);

        // Establecer los encabezados para la descarga
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename=' . basename($file));
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($file));

        // Limpiar el buffer de salida
        ob_clean();
        flush();

        // Leer y enviar el contenido del archivo al navegador
        readfile($file);

        // Eliminar el archivo temporal
        unlink($file);

        $this->data['info'] = "Backup creado correctamente";
    }

    private function restaurarBackup()
    {
        $db = self::conectar();

        // Leer el archivo subido
        $backup = file_get_contents($_FILES['backup']['tmp_name']);

        // Separar las sentencias SQL
        $sentencias = explode(';', $backup);

        // Ejecutar cada sentencia
        foreach ($sentencias as $sentencia) {
            $sentencia = trim($sentencia);
            if (!empty($sentencia)) {
                $db->exec($sentencia);
            }
        }

        $_SESSION['info'] = "Base de datos restaurada correctamente";
    }

    private function resetearBaseDatos()
    {
        $db = self::conectar();

        // Ejecuta el archivo de estructura de la base de datos
        $db->exec(file_get_contents('../config/estructura.sql'));

        $_SESSION['info'] = "Base de datos reseteada correctamente";
    }

    protected static function conectar()
    {
        try {
            $conexion = new PDO(DB_DSN, DB_USUARIO, DB_CONTRASENIA);

            $conexion->setAttribute(PDO::ATTR_PERSISTENT, true);
            $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Conexión fallida: " . $e->getMessage());
        }

        return $conexion;
    }

    protected static function desconectar($conexion)
    {
        $conexion = null;
    }

    public function render()
    {
        return HTMLrenderWeb($this->data);
    }
}
