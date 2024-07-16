<?php
require_once("../config/database.php");

class Log extends DataObject
{
    protected $datos = array(
        "id" => "",
        "fecha_hora" => "",
        "descripcion" => ""
    );

    public static function obtenerLogs($filaInicio, $numeroFilas, $orden)
    {
        $conexion = parent::conectar();

        $sql = "SELECT SQL_CALC_FOUND_ROWS * FROM " . TABLA_LOG .
            " ORDER BY " . $orden . " LIMIT :filaInicio, :numeroFilas";

        try {
            $st = $conexion->prepare($sql);

            $st->bindValue(":filaInicio", $filaInicio, PDO::PARAM_INT);
            $st->bindValue(":numeroFilas", $numeroFilas, PDO::PARAM_INT);
            $st->execute();

            $logs = array();

            foreach ($st->fetchAll() as $fila) {
                $logs[] = new Log($fila);
            }

            $st = $conexion->query("SELECT found_rows() AS filasTotales");

            $fila = $st->fetch();

            parent::desconectar($conexion);

            return array($logs, $fila["filasTotales"]);
        } catch (PDOException $e) {
            parent::desconectar($conexion);

            die("Consulta fallida: " . $e->getMessage());
        }
    }

    public static function insertarLog(
        $descripcion
    ) {
        $conexion = parent::conectar();

        $sql = "INSERT INTO " . TABLA_LOG . " (descripcion) 
                VALUES (:descripcion)";

        try {

            $st = $conexion->prepare($sql);
            $st->bindParam(':descripcion', $descripcion, PDO::PARAM_STR);
            $st->execute();

            $fila = $st->fetch();

            parent::desconectar($conexion);

            if ($fila)
                return new Log($fila);
        } catch (PDOException $e) {
            parent::desconectar($conexion);

            die("Consulta fallada: " . $e->getMessage());
        }
    }
}