<?php

require_once __DIR__ . "/conexion.php";

$correo = trim($_POST['correo'] ?? '');
$contrasena = $_POST['contrasena'] ?? '';
$confirmarContrasena = $_POST['confirmarContrasena'] ?? '';
$rol = "usuario";

/* VALIDACIONES */
if ($correo === '' || $contrasena === '') {
    exit("Completá el correo y la contraseña");
}

if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
    exit("El correo no es válido");
}

if ($contrasena !== $confirmarContrasena) {
    exit("Las contraseñas no coinciden");
}

/* La contraseña se guarda encriptada (validacion-usuario.php la compara con password_verify) */
$contrasenaHasheada = password_hash($contrasena, PASSWORD_DEFAULT);

/*
 * Consulta preparada: los valores van con :marcadores y NO pegados en el texto del SQL.
 * Así se evita la inyección SQL y se guarda el hash, no la contraseña tal cual.
 */
$sql = "INSERT INTO usuarios (correo, contrasena, rol) VALUES (:correo, :contrasena, :rol)";

$consulta = $conexion->prepare($sql);

try {
    $consulta->execute([
        "correo" => $correo,
        "contrasena" => $contrasenaHasheada,
        "rol" => $rol
    ]);
} catch (PDOException $error) {
    // 23000 = se viola la clave UNIQUE de `correo` (ya existe ese usuario)
    if ($error->getCode() == 23000) {
        exit("Ya existe una cuenta con ese correo");
    }
    exit("No se pudo registrar el usuario");
}

echo 'Usuario registrado';
