<?php
require_once("../config/database.php");

class Habitacion extends DataObject
{
    protected $datos = array(
        "id" => "",
        "numero_habitacion" => "",
        "capacidad" => "",
        "precio_noche" => "",
        "descripcion" => "",
        "id_img" => "",
        "id_habitacion" => "",
        "url" => ""
    );

    public static function obtenerTodasHabitaciones()
    {
        $conexion = parent::conectar();

        $sql = "SELECT * FROM " . TABLA_HABITACIONES . " ORDER BY numero_habitacion ASC";

        try {
            $st = $conexion->prepare($sql);

            $st->execute();

            $habitaciones = array();

            foreach ($st->fetchAll() as $fila) {
                $habitaciones[] = new Habitacion($fila);
            }

            $st = $conexion->query("SELECT found_rows() AS filasTotales");

            $fila = $st->fetch();

            parent::desconectar($conexion);

            return array($habitaciones, $fila["filasTotales"]);
        } catch (PDOException $e) {
            parent::desconectar($conexion);

            die("Consulta fallida: " . $e->getMessage());
        }
    }

    public static function obtenerHabitaciones($filaInicio, $numeroFilas, $orden)
    {
        $conexion = parent::conectar();

        $sql = "SELECT SQL_CALC_FOUND_ROWS * FROM " . TABLA_HABITACIONES .
            " ORDER BY " . $orden . " LIMIT :filaInicio, :numeroFilas";

        try {
            $st = $conexion->prepare($sql);

            $st->bindValue(":filaInicio", $filaInicio, PDO::PARAM_INT);
            $st->bindValue(":numeroFilas", $numeroFilas, PDO::PARAM_INT);
            $st->execute();

            $habitaciones = array();

            foreach ($st->fetchAll() as $fila) {
                $habitaciones[] = new Habitacion($fila);
            }

            $st = $conexion->query("SELECT found_rows() AS filasTotales");

            $fila = $st->fetch();

            parent::desconectar($conexion);

            return array($habitaciones, $fila["filasTotales"]);
        } catch (PDOException $e) {
            parent::desconectar($conexion);

            die("Consulta fallida: " . $e->getMessage());
        }
    }

    public static function obtenerHabitacion($numero_habitacion)
    {
        $conexion = parent::conectar();

        $sql = "SELECT * FROM " . TABLA_HABITACIONES . " WHERE numero_habitacion = :numero_habitacion";

        try {
            $st = $conexion->prepare($sql);
            $st->bindValue(":numero_habitacion", $numero_habitacion, PDO::PARAM_STR);
            $st->execute();

            $fila = $st->fetch();

            parent::desconectar($conexion);

            if ($fila)
                return new Habitacion($fila);
        } catch (PDOException $e) {
            parent::desconectar($conexion);

            die("Consulta fallada: " . $e->getMessage());
        }
    }

    public static function obtenerHabitacionID($id)
    {
        $conexion = parent::conectar();

        $sql = "SELECT * FROM " . TABLA_HABITACIONES . " WHERE id = :id";

        try {
            $st = $conexion->prepare($sql);
            $st->bindValue(":id", $id, PDO::PARAM_INT);
            $st->execute();

            $fila = $st->fetch();

            parent::desconectar($conexion);

            if ($fila)
                return new Habitacion($fila);
        } catch (PDOException $e) {
            parent::desconectar($conexion);

            die("Consulta fallada: " . $e->getMessage());
        }
    }

    public static function obtenerHabitacionAjustada($capacidad)
    {
        $conexion = parent::conectar();

        $sql = "SELECT * FROM " . TABLA_HABITACIONES . " WHERE capacidad >= :capacidad ORDER BY capacidad";

        try {
            $st = $conexion->prepare($sql);
            $st->bindValue(":capacidad", $capacidad, PDO::PARAM_INT);
            $st->execute();

            $habitaciones = array();

            foreach ($st->fetchAll() as $fila) {
                $habitaciones[] = new Habitacion($fila);
            }

            $st = $conexion->query("SELECT found_rows() AS filasTotales");

            $fila = $st->fetch();

            parent::desconectar($conexion);

            return array($habitaciones, $fila["filasTotales"]);
        } catch (PDOException $e) {
            parent::desconectar($conexion);

            die("Consulta fallada: " . $e->getMessage());
        }
    }

    public static function modificarHabitacion(
        $id,
        $numero_habitacion,
        $capacidad,
        $precio_noche,
        $descripcion
    ) {
        $conexion = parent::conectar();

        $sql = "UPDATE " . TABLA_HABITACIONES . " SET numero_habitacion = :numero_habitacion, capacidad = :capacidad, precio_noche = :precio_noche, 
                    descripcion = :descripcion WHERE id = :id";

        try {
            $st = $conexion->prepare($sql);
            $st->bindParam(':numero_habitacion', $numero_habitacion, PDO::PARAM_STR);
            $st->bindParam(':capacidad', $capacidad, PDO::PARAM_INT);
            $st->bindParam(':precio_noche', $precio_noche, PDO::PARAM_STR);
            $st->bindParam(':descripcion', $descripcion, PDO::PARAM_STR);
            $st->bindParam(':id', $id, PDO::PARAM_INT);
            $st->execute();

            parent::desconectar($conexion);

            return true;
        } catch (PDOException $e) {
            parent::desconectar($conexion);

            die("Consulta fallada: " . $e->getMessage());
        }
    }

    public static function eliminarHabitacion($numero_habitacion)
    {
        $conexion = parent::conectar();

        $sql = "DELETE FROM " . TABLA_HABITACIONES . " WHERE numero_habitacion = :numero_habitacion";

        try {
            $st = $conexion->prepare($sql);
            $st->bindValue(":numero_habitacion", $numero_habitacion, PDO::PARAM_STR);
            $st->execute();

            parent::desconectar($conexion);

            return true;
        } catch (PDOException $e) {
            parent::desconectar($conexion);

            die("Consulta fallada: " . $e->getMessage());
        }
    }

    public static function insertarHabitacion(
        $id,
        $numero_habitacion,
        $capacidad,
        $precio_noche,
        $descripcion
    ) {
        $conexion = parent::conectar();

        $sql = "INSERT INTO " . TABLA_HABITACIONES . " (id, numero_habitacion, capacidad, precio_noche, descripcion) 
        VALUES (:id, :numero_habitacion, :capacidad, :precio_noche, :descripcion)";

        try {
            $st = $conexion->prepare($sql);
            $st->bindParam(':id', $id, PDO::PARAM_INT);
            $st->bindParam(':numero_habitacion', $numero_habitacion, PDO::PARAM_STR);
            $st->bindParam(':capacidad', $capacidad, PDO::PARAM_INT);
            $st->bindParam(':precio_noche', $precio_noche, PDO::PARAM_STR);
            $st->bindParam(':descripcion', $descripcion, PDO::PARAM_STR);
            $st->execute();

            parent::desconectar($conexion);

            return true;
        } catch (PDOException $e) {
            parent::desconectar($conexion);

            die("Consulta fallada: " . $e->getMessage());
        }
    }

    public static function capacidadTotalHotel() {
        $conexion = parent::conectar();

        $sql = "SELECT SUM(capacidad) AS capacidadTotal FROM " . TABLA_HABITACIONES;

        try {
            $st = $conexion->prepare($sql);
            $st->execute();

            $fila = $st->fetch();

            parent::desconectar($conexion);

            return $fila["capacidadTotal"];
        } catch (PDOException $e) {
            parent::desconectar($conexion);

            die("Consulta fallida: " . $e->getMessage());
        }
    }

    ////////////////////////////////////////////////          METODOS CON IMAGENES DE LAS HABITACIONES

    public static function obtenerImagenes()
    {
        $conexion = parent::conectar();

        $sql = "SELECT * FROM " . TABLA_FOTOS;

        try {
            $st = $conexion->prepare($sql);

            $st->execute();

            $imagenes = array();

            foreach ($st->fetchAll() as $fila) {
                $imagenes[] = new Habitacion($fila);
            }

            $st = $conexion->query("SELECT found_rows() AS filasTotales");

            $fila = $st->fetch();

            parent::desconectar($conexion);

            return array($imagenes, $fila["filasTotales"]);
        } catch (PDOException $e) {
            parent::desconectar($conexion);

            die("Consulta fallada: " . $e->getMessage());
        }
    }

    public static function insertarImagen($id_habitacion, $url)
    {
        $conexion = parent::conectar();

        $sql = "INSERT INTO " . TABLA_FOTOS . " (id_habitacion, url) VALUES (:id_habitacion, :url)";

        try {
            $st = $conexion->prepare($sql);
            $st->bindParam(':id_habitacion', $id_habitacion, PDO::PARAM_INT);
            $st->bindParam(':url', $url, PDO::PARAM_STR);
            $st->execute();

            parent::desconectar($conexion);

            return true;
        } catch (PDOException $e) {
            parent::desconectar($conexion);

            die("Consulta fallada: " . $e->getMessage());
        }
    }

    public static function eliminarImagenes($id)
    {
        $conexion = parent::conectar();

        $sql = "DELETE FROM " . TABLA_FOTOS . " WHERE id_habitacion = :id";

        try {
            $st = $conexion->prepare($sql);
            $st->bindValue(":id", $id, PDO::PARAM_INT);
            $st->execute();

            parent::desconectar($conexion);

            return true;
        } catch (PDOException $e) {
            parent::desconectar($conexion);

            die("Consulta fallada: " . $e->getMessage());
        }
    }
}
