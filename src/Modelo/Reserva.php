<?php
require_once("../config/database.php");
require_once("Usuario.php");

class Reserva extends DataObject
{
    protected $datos = array(
        "id" => "",
        "id_usuario" => "",
        "id_habitacion" => "",
        "fecha_inicio" => "",
        "fecha_fin" => "",
        "comentarios" => "",
        "n_personas" => "",
        "estado" => "",
        "marca_tiempo" => ""
    );

    public static function obtenerReservas($filaInicio, $numeroFilas, $orden, $estado = null)
    {
        $conexion = parent::conectar();

        $sql = "SELECT SQL_CALC_FOUND_ROWS * FROM " . TABLA_RESERVAS;
    
        if ($estado !== null) {
            $sql .= " WHERE estado = :estado";
        }
        
        $sql .= " ORDER BY " . $orden . " LIMIT :filaInicio, :numeroFilas";

        try {
            $st = $conexion->prepare($sql);

            $st->bindValue(":filaInicio", $filaInicio, PDO::PARAM_INT);
            $st->bindValue(":numeroFilas", $numeroFilas, PDO::PARAM_INT);

            if ($estado !== null) {
                $st->bindValue(":estado", $estado, PDO::PARAM_STR);
            }

            $st->execute();

            $reservas = array();

            foreach ($st->fetchAll() as $fila) {
                $reservas[] = new Reserva($fila);
            }

            $st = $conexion->query("SELECT found_rows() AS filasTotales");

            $fila = $st->fetch();

            parent::desconectar($conexion);

            return array($reservas, $fila["filasTotales"]);
        } catch (PDOException $e) {
            parent::desconectar($conexion);

            die("Consulta fallida: " . $e->getMessage());
        }
    }

    public static function obtenerTodasReservas()
    {
        $conexion = parent::conectar();

        $sql = "SELECT * FROM " . TABLA_RESERVAS;

        try {
            $st = $conexion->prepare($sql);

            $st->execute();

            $reservas = array();

            foreach ($st->fetchAll() as $fila) {
                $reservas[] = new Reserva($fila);
            }

            $st = $conexion->query("SELECT found_rows() AS filasTotales");

            $fila = $st->fetch();

            parent::desconectar($conexion);

            return array($reservas, $fila["filasTotales"]);
        } catch (PDOException $e) {
            parent::desconectar($conexion);

            die("Consulta fallida: " . $e->getMessage());
        }
    }

    public static function obtenerReservasID($id_usuario, $filaInicio, $numeroFilas)
    {
        $conexion = parent::conectar();

        $sql = "SELECT SQL_CALC_FOUND_ROWS * FROM " . TABLA_RESERVAS . " WHERE id_usuario = :id_usuario 
            LIMIT :filaInicio, :numeroFilas";

        try {
            $st = $conexion->prepare($sql);

            $st->bindValue(":id_usuario", $id_usuario, PDO::PARAM_INT);
            $st->bindValue(":filaInicio", $filaInicio, PDO::PARAM_INT);
            $st->bindValue(":numeroFilas", $numeroFilas, PDO::PARAM_INT);
            
            $st->execute();

            $reservas = array();

            foreach ($st->fetchAll() as $fila) {
                $reservas[] = new Reserva($fila);
            }

            $st = $conexion->query("SELECT found_rows() AS filasTotales");

            $fila = $st->fetch();

            parent::desconectar($conexion);

            return array($reservas, $fila["filasTotales"]);
        } catch (PDOException $e) {
            parent::desconectar($conexion);

            die("Consulta fallada: " . $e->getMessage());
        }
    }

    public static function obtenerIDReserva($id)
    {
        $conexion = parent::conectar();

        $sql = "SELECT * FROM " . TABLA_RESERVAS . " WHERE id = :id";

        try {
            $st = $conexion->prepare($sql);
            $st->bindValue(":id", $id, PDO::PARAM_INT);
            $st->execute();

            $fila = $st->fetch();

            parent::desconectar($conexion);

            if ($fila)
                return new Reserva($fila);
        } catch (PDOException $e) {
            parent::desconectar($conexion);

            die("Consulta fallada: " . $e->getMessage());
        }
    }

    public static function modificarComentariosReserva(
        $id,
        $comentarios
    ) {
        $conexion = parent::conectar();

        $sql = "UPDATE " . TABLA_RESERVAS . " SET comentarios = :comentarios WHERE id = :id";

        try {
            $st = $conexion->prepare($sql);
            $st->bindParam(':id', $id, PDO::PARAM_INT);
            $st->bindParam(':comentarios', $comentarios, PDO::PARAM_STR);
            $st->execute();

            parent::desconectar($conexion);

            return true;
        } catch (PDOException $e) {
            parent::desconectar($conexion);

            die("Consulta fallada: " . $e->getMessage());
        }
    }

    public static function modificarEstadoReserva(
        $id,
        $estado
    ) {
        $conexion = parent::conectar();

        $sql = "UPDATE " . TABLA_RESERVAS . " SET estado = :estado WHERE id = :id";

        try {
            $st = $conexion->prepare($sql);
            $st->bindParam(':id', $id, PDO::PARAM_INT);
            $st->bindParam(':estado', $estado, PDO::PARAM_STR);
            $st->execute();

            parent::desconectar($conexion);

            return true;
        } catch (PDOException $e) {
            parent::desconectar($conexion);

            die("Consulta fallada: " . $e->getMessage());
        }
    }

    public static function eliminarReserva($id)
    {
        $conexion = parent::conectar();

        $sql = "DELETE FROM " . TABLA_RESERVAS . " WHERE id = :id";

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

    public static function eliminarReservasUsuario($dni)
    {
        $conexion = parent::conectar();

        $usuario = Usuario::obtenerUsuario($dni);
        $id_usuario = $usuario->devolverValor('id');

        $sql = "DELETE FROM " . TABLA_RESERVAS . " WHERE id_usuario = :id_usuario";

        try {
            $st = $conexion->prepare($sql);
            $st->bindValue(":id_usuario", $id_usuario, PDO::PARAM_STR);
            $st->execute();

            parent::desconectar($conexion);

            return true;
        } catch (PDOException $e) {
            parent::desconectar($conexion);

            die("Consulta fallada: " . $e->getMessage());
        }
    }

    public static function insertarReserva(
        $id_usuario,
        $id_habitacion,
        $fecha_inicio,
        $fecha_fin,
        $comentarios,
        $n_personas
    ) {
        $conexion = parent::conectar();

        $sql = "INSERT INTO " . TABLA_RESERVAS . " (id_usuario, id_habitacion, fecha_inicio, fecha_fin, comentarios, n_personas) 
            VALUES (:id_usuario, :id_habitacion, :fecha_inicio, :fecha_fin, :comentarios, :n_personas)";

        try {

            $st = $conexion->prepare($sql);
            $st->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
            $st->bindParam(':id_habitacion', $id_habitacion, PDO::PARAM_INT);
            $st->bindParam(':fecha_inicio', $fecha_inicio, PDO::PARAM_STR);
            $st->bindParam(':fecha_fin', $fecha_fin, PDO::PARAM_STR);
            $st->bindParam(':comentarios', $comentarios, PDO::PARAM_STR);
            $st->bindParam(':n_personas', $n_personas, PDO::PARAM_STR);

            $st->execute();

            $idReserva = $conexion->lastInsertId();

            parent::desconectar($conexion);

            return $idReserva;
        } catch (PDOException $e) {
            parent::desconectar($conexion);

            die("Consulta fallada: " . $e->getMessage());
        }
    }

    public static function contarHuespedesActuales()
    {
        $conexion = parent::conectar();

        $sql = "SELECT SUM(n_personas) AS totalHuespedes FROM " . TABLA_RESERVAS . " 
                WHERE CURDATE() BETWEEN fecha_inicio AND fecha_fin;";

        try {
            $st = $conexion->prepare($sql);
            $st->execute();

            $fila = $st->fetch();

            parent::desconectar($conexion);

            return $fila['totalHuespedes'];
        } catch (PDOException $e) {
            parent::desconectar($conexion);

            die("Consulta fallada: " . $e->getMessage());
        }
    }
}
