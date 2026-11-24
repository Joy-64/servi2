<?php
require_once __DIR__ . '/../core/Database.php';

class Usuario
{
    private const NOMBRE_CLASE = 'Usuario';
     public $Id;
    public $Nombre;
    public $Apellido;
    public $Sexo;
    public $CUIL;
    public $NDoc;
    public $FNacimiento;
    public $IdTdoc;
    public $Imagen;
    public $Domicilio;
    public $CodPostal;
    public $Localidad;
    public $Telefono;
    public $Email;

    public function __construct()
    {
    }
    /**
     * Devuelve la lista de trabajadores (solo los datos básicos de la tabla trabajadores).
     */
    public static function obtenerTodas()
    {
        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare('SELECT id AS Id, nombre AS Nombre, apellido AS Apellido, sexo AS Sexo, cuil AS CUIL, nDoc AS NDoc, fNacimiento AS FNacimiento, idTDoc AS IdTdoc, imagen AS Imagen FROM trabajadores ORDER BY id DESC');
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_CLASS, self::NOMBRE_CLASE);
        } catch (PDOException $e) {
            error_log("Error al obtener trabajadores: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Devuelve un trabajador completo (con domicilio, teléfono y email), o null si no existe.
     * Los oficios se piden aparte con obtenerOficiosDeUsuario().
     */
    public static function obtenerPorId($Id)
    {
        try {
            $db = Database::getInstance()->getConnection();

            // 1) El trabajador
            $stmt = $db->prepare('SELECT id AS Id, nombre AS Nombre, apellido AS Apellido, sexo AS Sexo, cuil AS CUIL, nDoc AS NDoc, fNacimiento AS FNacimiento, idTDoc AS IdTdoc, imagen AS Imagen FROM trabajadores WHERE id = :id');
            $stmt->execute([':id' => $Id]);
            $stmt->setFetchMode(PDO::FETCH_CLASS, self::NOMBRE_CLASE);
            $usuario = $stmt->fetch();
            if (!$usuario) {
                return null; // no existe un trabajador con ese id
            }

            // 2) Su domicilio; si no tiene, las propiedades quedan como estaban
            $stmt = $db->prepare('SELECT d.valor, cp.codpostal, cp.localidad FROM domicilio_trabajador d LEFT JOIN codigo_postal cp ON cp.id = d.id_cod_postal WHERE d.id_trabajador = :id LIMIT 1');
            $stmt->execute([':id' => $Id]);
            $fila = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($fila) {
                $usuario->Domicilio = $fila['valor'];
                $usuario->CodPostal = $fila['codpostal'];
                $usuario->Localidad = $fila['localidad'];
            }

            // 3) Su teléfono y su email (si no tiene, queda vacío)
            $usuario->Telefono = self::obtenerContacto($db, $Id, 'Teléfono') ?: '';
            $usuario->Email = self::obtenerContacto($db, $Id, 'Email') ?: '';

            return $usuario;
        } catch (PDOException $e) {
            error_log("Error al obtener trabajador: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Guarda un trabajador nuevo con todos sus datos. Devuelve el id nuevo, o false si algo falla.
     * Es una transacción: se guarda todo junto o no se guarda nada.
     */
    public static function agregar($datos)
    {
        try {
            $db = Database::getInstance()->getConnection();
            $db->beginTransaction();
            $stmt = $db->prepare('INSERT INTO trabajadores (nombre, apellido, sexo, cuil, ndoc, fNacimiento, imagen, idTdoc, IdUsuarios)
            VALUES (:nombre, :apellido, :sexo, :cuil, :ndoc, :fNacimiento, :imagen, :idTdoc, :IdUsuarios)');
            $stmt->execute([
                ':nombre' => $datos['Nombre'],
                ':apellido' => $datos['Apellido'],
                ':sexo' => $datos['Sexo'],
                ':cuil' => $datos['CUIL'],
                ':ndoc' => $datos['NDoc'],
                ':fNacimiento' => $datos['FNacimiento'],
                ':imagen' => (($datos['Imagen'] ?? '') !== '') ? $datos['Imagen'] : null,
                ':idTdoc' => $datos['IdTdoc'] ?? null,
                ':IdUsuarios' => $datos['IdUsuarios'] ?? null
            ]);
            // El id del trabajador se guarda enseguida: lastInsertId() cambia con cada INSERT nuevo
            $idTrabajador = $db->lastInsertId();
            self::guardarRelaciones($db, $idTrabajador, $datos);
            $db->commit();
            return $idTrabajador;
        } catch (PDOException $e) {
            if (isset($db) && $db->inTransaction()) {
                $db->rollBack();
            }
            error_log("Error al agregar trabajador: " . $e->getMessage() . " (línea " . $e->getLine() . ")");
            return false;
        }
    }

    /**
     * Actualiza un trabajador existente. Devuelve true si salió bien, o false si algo falla.
     * Los oficios, contactos y domicilio se borran y se vuelven a guardar con los datos nuevos.
     */
    public static function editar($Id, $datos)
    {
        try {
            $db = Database::getInstance()->getConnection();
            $db->beginTransaction();
            // COALESCE: si no se subió una foto nueva (null), se conserva la que ya estaba
            $stmt = $db->prepare('UPDATE trabajadores SET nombre = :nombre, apellido = :apellido, sexo = :sexo, cuil = :cuil, ndoc = :ndoc, fNacimiento = :fNacimiento, idTdoc = :idTdoc, imagen = COALESCE(:imagen, imagen) WHERE id = :id');
            $stmt->execute([
                ':nombre' => $datos['Nombre'],
                ':apellido' => $datos['Apellido'],
                ':sexo' => $datos['Sexo'],
                ':cuil' => $datos['CUIL'],
                ':ndoc' => $datos['NDoc'],
                ':fNacimiento' => $datos['FNacimiento'],
                ':imagen' => (($datos['Imagen'] ?? '') !== '') ? $datos['Imagen'] : null,
                ':idTdoc' => $datos['IdTdoc'] ?? null,
                ':id' => $Id
            ]);
            self::borrarRelaciones($db, $Id);
            self::guardarRelaciones($db, $Id, $datos);
            $db->commit();
            return true;
        } catch (PDOException $e) {
            if (isset($db) && $db->inTransaction()) {
                $db->rollBack();
            }
            error_log("Error al editar trabajador: " . $e->getMessage() . " (línea " . $e->getLine() . ")");
            return false;
        }
    }

    /**
     * Elimina un trabajador y todo lo que está conectado a él.
     * Devuelve true si borró al trabajador, o false si no existía o si algo falla.
     */
    public static function eliminar($Id)
    {
        try {
            $db = Database::getInstance()->getConnection();
            $db->beginTransaction();
            self::borrarRelaciones($db, $Id);
            $stmt = $db->prepare('DELETE FROM trabajadores WHERE id = :id');
            $stmt->execute([':id' => $Id]);
            $borrados = $stmt->rowCount();
            $db->commit();
            return $borrados > 0;
        } catch (PDOException $e) {
            if (isset($db) && $db->inTransaction()) {
                $db->rollBack();
            }
            error_log("Error al eliminar trabajador: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Devuelve el texto de todos los oficios guardados en la tabla oficios.
     */
    public static function obtenerOficios()
    {
        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("SELECT DISTINCT descripcion FROM oficios WHERE descripcion <> '' ORDER BY descripcion");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (PDOException $e) {
            error_log("Error al obtener oficios: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Devuelve los oficios que tiene un trabajador (solo el texto de cada uno).
     */
    public static function obtenerOficiosDeUsuario($idTrabajador)
    {
        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare('SELECT o.descripcion FROM trabajador_oficio t INNER JOIN oficios o ON o.id = t.id_oficio WHERE t.id_trabajador = :id ORDER BY o.descripcion');
            $stmt->execute([':id' => $idTrabajador]);
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (PDOException $e) {
            error_log("Error al obtener oficios del trabajador: " . $e->getMessage());
            return [];
        }
    }

    // ------------------------------------------------------------------
    // Funciones auxiliares (no llevan try/catch: los errores suben a quien las llama)
    // ------------------------------------------------------------------

    /**
     * Guarda los oficios, los contactos y el domicilio de un trabajador.
     */
    private static function guardarRelaciones($db, $idTrabajador, $datos)
    {
        self::agregarOficios($db, $idTrabajador, $datos['Oficios'] ?? []);
        self::guardarContacto($db, $idTrabajador, 'Teléfono', $datos['Telefono'] ?? '');
        self::guardarContacto($db, $idTrabajador, 'Email', $datos['Email'] ?? '');
        self::guardarDomicilio($db, $idTrabajador, $datos['Domicilio'] ?? '', $datos['CodPostal'] ?? '', $datos['Localidad'] ?? '');
    }

    /**
     * Por cada oficio: lo busca en la tabla oficios, lo crea si no existe y lo conecta con el trabajador.
     */
    private static function agregarOficios($db, $idTrabajador, $oficios)
    {
        $stmt = $db->prepare('INSERT INTO trabajador_oficio (id_trabajador, id_oficio) VALUES (:id_trabajador, :id_oficio)');
        foreach ($oficios as $oficio) {
            $oficio = trim($oficio);
            if ($oficio === '') {
                continue; // Saltar oficios vacíos
            }
            // Obtener el ID del oficio a partir de su descripción
            $stmtOficio = $db->prepare('SELECT id FROM oficios WHERE descripcion = :descripcion LIMIT 1');
            $stmtOficio->execute([':descripcion' => $oficio]);
            $idOficio = $stmtOficio->fetchColumn();
            if (!$idOficio) {
                $stmtInsertOficio = $db->prepare('INSERT INTO oficios (descripcion) VALUES (:descripcion)');
                $stmtInsertOficio->execute([':descripcion' => $oficio]);
                $idOficio = $db->lastInsertId();
            }
            $stmt->execute([
                ':id_trabajador' => $idTrabajador,
                ':id_oficio' => $idOficio
            ]);
        }
    }

    /**
     * Guarda un contacto (Teléfono o Email): busca o crea el tipo y guarda el valor.
     */
    private static function guardarContacto($db, $idTrabajador, $medio, $valor)
    {
        $valor = trim($valor);
        if ($valor === '') {
            return; // No guardar contactos vacíos
        }
        $stmt = $db->prepare('SELECT id FROM medios_contactos WHERE descripcion = :descripcion LIMIT 1');
        $stmt->execute([':descripcion' => $medio]);
        $idMedio = $stmt->fetchColumn();
        if (!$idMedio) {
            $stmt = $db->prepare('INSERT INTO medios_contactos (descripcion) VALUES (:descripcion)');
            $stmt->execute([':descripcion' => $medio]);
            $idMedio = $db->lastInsertId();
        }
        $stmt = $db->prepare('INSERT INTO trabajador_medios_contactos (id_trabajador, id_medio_contacto, valor) VALUES (:id_trabajador, :id_medio, :valor)');
        $stmt->execute([
            ':id_trabajador' => $idTrabajador,
            ':id_medio' => $idMedio,
            ':valor' => $valor
        ]);
    }

    /**
     * Devuelve el valor de un contacto (Teléfono o Email) de un trabajador, o false si no tiene.
     */
    private static function obtenerContacto($db, $idTrabajador, $medio)
    {
        $stmt = $db->prepare('SELECT tmc.valor FROM trabajador_medios_contactos tmc INNER JOIN medios_contactos mc ON mc.id = tmc.id_medio_contacto WHERE tmc.id_trabajador = :id AND mc.descripcion = :medio LIMIT 1');
        $stmt->execute([':id' => $idTrabajador, ':medio' => $medio]);
        return $stmt->fetchColumn();
    }

    /**
     * Guarda el domicilio: busca o crea el código postal y guarda la calle junto a él.
     */
    private static function guardarDomicilio($db, $idTrabajador, $calle, $codPostal, $localidad)
    {
        $calle = trim($calle);
        $codPostal = trim($codPostal);
        $localidad = trim($localidad);
        if ($calle === '' && $codPostal === '' && $localidad === '') {
            return; // No guardar domicilios vacíos
        }
        $idCodPostal = null;
        if ($codPostal !== '') {
            $stmt = $db->prepare('SELECT id FROM codigo_postal WHERE codpostal = :codpostal AND localidad = :localidad LIMIT 1');
            $stmt->execute([':codpostal' => $codPostal, ':localidad' => $localidad]);
            $idCodPostal = $stmt->fetchColumn();
            if (!$idCodPostal) {
                $stmt = $db->prepare('INSERT INTO codigo_postal (codpostal, localidad) VALUES (:codpostal, :localidad)');
                $stmt->execute([':codpostal' => $codPostal, ':localidad' => $localidad]);
                $idCodPostal = $db->lastInsertId();
            }
        }

        $stmt = $db->prepare('INSERT INTO domicilio_trabajador (id_trabajador, id_cod_postal, valor) VALUES (:id_trabajador, :id_cod_postal, :valor)');
        $stmt->execute([
            ':id_trabajador' => $idTrabajador,
            ':id_cod_postal' => $idCodPostal,
            ':valor' => $calle
        ]);
    }

    /**
     * Borra los oficios, contactos y domicilio de un trabajador (las listas oficios y codigo_postal no se tocan).
     */
    private static function borrarRelaciones($db, $idTrabajador)
    {
        $stmt = $db->prepare('DELETE FROM trabajador_oficio WHERE id_trabajador = :id_trabajador');
        $stmt->execute([':id_trabajador' => $idTrabajador]);

        $stmt = $db->prepare('DELETE FROM trabajador_medios_contactos WHERE id_trabajador = :id_trabajador');
        $stmt->execute([':id_trabajador' => $idTrabajador]);

        $stmt = $db->prepare('DELETE FROM domicilio_trabajador WHERE id_trabajador = :id_trabajador');
        $stmt->execute([':id_trabajador' => $idTrabajador]);
    }
}
