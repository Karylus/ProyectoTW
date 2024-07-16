<?php
require_once("../config/database.php");

class Usuario extends DataObject
{
    protected $datos = array(
        "id" => "",
        "nombre" => "",
        "apellidos" => "",
        "dni" => "",
        "email" => "",
        "clave" => "",
        "numero_tarjeta" => "",
        "rol" => ""
    );

    public static function obtenerUsuarios($filaInicio, $numeroFilas, $orden, $rol = null)
    {
        $conexion = parent::conectar();
    
        $sql = "SELECT SQL_CALC_FOUND_ROWS * FROM " . TABLA_USUARIOS;
    
        if ($rol !== null) {
            $sql .= " WHERE rol = :rol";
        }
    
        $sql .= " ORDER BY " . $orden . " LIMIT :filaInicio, :numeroFilas";
    
        try {
            $st = $conexion->prepare($sql);
    
            $st->bindValue(":filaInicio", $filaInicio, PDO::PARAM_INT);
            $st->bindValue(":numeroFilas", $numeroFilas, PDO::PARAM_INT);
    
            if ($rol !== null) {
                $st->bindValue(":rol", $rol, PDO::PARAM_STR);
            }
    
            $st->execute();
    
            $usuarios = array();
    
            foreach ($st->fetchAll() as $fila) {
                $usuarios[] = new Usuario($fila);
            }
    
            $st = $conexion->query("SELECT found_rows() AS filasTotales");
    
            $fila = $st->fetch();
    
            parent::desconectar($conexion);
    
            return array($usuarios, $fila["filasTotales"]);
        } catch (PDOException $e) {
            parent::desconectar($conexion);
    
            die("Consulta fallida: " . $e->getMessage());
        }
    }

    public static function obtenerTodosUsuarios()
    {
        $conexion = parent::conectar();

        $sql = "SELECT * FROM " . TABLA_USUARIOS;

        try {
            $st = $conexion->prepare($sql);

            $st->execute();

            $usuarios = array();

            foreach ($st->fetchAll() as $fila) {
                $usuarios[] = new Usuario($fila);
            }

            $st = $conexion->query("SELECT found_rows() AS filasTotales");

            $fila = $st->fetch();

            parent::desconectar($conexion);

            return array($usuarios, $fila["filasTotales"]);
        } catch (PDOException $e) {
            parent::desconectar($conexion);

            die("Consulta fallida: " . $e->getMessage());
        }
    }

    public static function obtenerUsuario($dni)
    {
        $conexion = parent::conectar();

        $sql = "SELECT * FROM " . TABLA_USUARIOS . " WHERE dni = :dni";

        try {
            $st = $conexion->prepare($sql);
            $st->bindValue(":dni", $dni, PDO::PARAM_STR);
            $st->execute();

            $fila = $st->fetch();

            parent::desconectar($conexion);

            if ($fila)
                return new Usuario($fila);
        } catch (PDOException $e) {
            parent::desconectar($conexion);

            die("Consulta fallada: " . $e->getMessage());
        }
    }

    public static function obtenerUsuarioID($id)
    {
        $conexion = parent::conectar();

        $sql = "SELECT * FROM " . TABLA_USUARIOS . " WHERE id = :id";

        try {
            $st = $conexion->prepare($sql);
            $st->bindValue(":id", $id, PDO::PARAM_STR);
            $st->execute();

            $fila = $st->fetch();

            parent::desconectar($conexion);

            if ($fila)
                return new Usuario($fila);
        } catch (PDOException $e) {
            parent::desconectar($conexion);

            die("Consulta fallada: " . $e->getMessage());
        }
    }

    public static function obtenerUsuarioEmail($email)
    {
        $conexion = parent::conectar();

        $sql = "SELECT * FROM " . TABLA_USUARIOS . " WHERE email = :email";

        try {
            $st = $conexion->prepare($sql);
            $st->bindValue(":email", $email, PDO::PARAM_STR);
            $st->execute();

            $fila = $st->fetch();

            parent::desconectar($conexion);

            if ($fila)
                return new Usuario($fila);
        } catch (PDOException $e) {
            parent::desconectar($conexion);

            die("Consulta fallada: " . $e->getMessage());
        }
    }

    public static function modificarUsuario(
        $nombre,
        $apellidos,
        $dni,
        $email,
        $clave,
        $numero_tarjeta,
        $rol
    ) {
        $conexion = parent::conectar();

        $sql = "UPDATE " . TABLA_USUARIOS . " SET nombre = :nombre, apellidos = :apellidos, email = :email, clave = :clave, 
                    numero_tarjeta = :numero_tarjeta, rol = :rol WHERE dni = :dni";

        try {
            $clave = password_hash($clave, PASSWORD_DEFAULT);
            
            $st = $conexion->prepare($sql);
            $st->bindParam(':nombre', $nombre, PDO::PARAM_STR);
            $st->bindParam(':apellidos', $apellidos, PDO::PARAM_STR);
            $st->bindParam(':dni', $dni, PDO::PARAM_STR);
            $st->bindParam(':email', $email, PDO::PARAM_STR);
            $st->bindParam(':clave', $clave, PDO::PARAM_STR);
            $st->bindParam(':numero_tarjeta', $numero_tarjeta, PDO::PARAM_STR);
            $st->bindParam(':rol', $rol, PDO::PARAM_STR);
            $st->execute();

            parent::desconectar($conexion);

            return true;
        } catch (PDOException $e) {
            parent::desconectar($conexion);

            die("Consulta fallada: " . $e->getMessage());
        }
    }

    public static function modificarUsuarioID(
        $id,
        $nombre,
        $apellidos,
        $dni,
        $email,
        $clave,
        $numero_tarjeta,
        $rol
    ) {
        $conexion = parent::conectar();

        $sql = "UPDATE " . TABLA_USUARIOS . " SET nombre = :nombre, apellidos = :apellidos, dni = :dni, email = :email, clave = :clave, 
                    numero_tarjeta = :numero_tarjeta, rol = :rol WHERE id = :id";

        try {
            $clave = password_hash($clave, PASSWORD_DEFAULT);

            $st = $conexion->prepare($sql);
            $st->bindParam(':nombre', $nombre, PDO::PARAM_STR);
            $st->bindParam(':apellidos', $apellidos, PDO::PARAM_STR);
            $st->bindParam(':dni', $dni, PDO::PARAM_STR);
            $st->bindParam(':email', $email, PDO::PARAM_STR);
            $st->bindParam(':clave', $clave, PDO::PARAM_STR);
            $st->bindParam(':numero_tarjeta', $numero_tarjeta, PDO::PARAM_STR);
            $st->bindParam(':rol', $rol, PDO::PARAM_STR);
            $st->bindParam(':id', $id, PDO::PARAM_STR);
            $st->execute();

            parent::desconectar($conexion);

            return true;
        } catch (PDOException $e) {
            parent::desconectar($conexion);

            die("Consulta fallada: " . $e->getMessage());
        }
    }

    public static function eliminarUsuario($dni)
    {
        $conexion = parent::conectar();

        $sql = "DELETE FROM " . TABLA_USUARIOS . " WHERE dni = :dni";

        try {
            $st = $conexion->prepare($sql);
            $st->bindValue(":dni", $dni, PDO::PARAM_STR);
            $st->execute();

            parent::desconectar($conexion);

            return true;
        } catch (PDOException $e) {
            parent::desconectar($conexion);

            die("Consulta fallada: " . $e->getMessage());
        }
    }

    public static function insertarUsuario(
        $nombre,
        $apellidos,
        $dni,
        $email,
        $clave,
        $numero_tarjeta,
        $rol
    ) {
        $conexion = parent::conectar();

        $sql = "INSERT INTO " . TABLA_USUARIOS . " (nombre, apellidos, dni, email, clave, numero_tarjeta, rol) 
        VALUES (:nom, :apell, :dni, :email, :clave, :num_tarjeta, :rol)";

        try {
            //Convierto la contraseña a un hash
            $clave = password_hash($clave, PASSWORD_DEFAULT);

            $st = $conexion->prepare($sql);
            $st->bindParam(':nom', $nombre, PDO::PARAM_STR);
            $st->bindParam(':apell', $apellidos, PDO::PARAM_STR);
            $st->bindParam(':dni', $dni, PDO::PARAM_STR);
            $st->bindParam(':email', $email, PDO::PARAM_STR);
            $st->bindParam(':clave', $clave, PDO::PARAM_STR);
            $st->bindParam(':num_tarjeta', $numero_tarjeta, PDO::PARAM_STR);
            $st->bindParam(':rol', $rol, PDO::PARAM_STR);
            $st->execute();

            $idUsuario = $conexion->lastInsertId();

            $fila = $st->fetch();

            parent::desconectar($conexion);

            return $idUsuario;
        } catch (PDOException $e) {
            parent::desconectar($conexion);

            die("Consulta fallada: " . $e->getMessage());
        }
    }
}
