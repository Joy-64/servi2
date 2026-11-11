<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso ServiTrack</title>
    <link rel="icon" href="./img/logo3.0.jpeg">
    <link rel="stylesheet" href="./css/style.css">
</head>
<body class="pagina-acceso">
    <section class="top">
        <a href="./index.html">
            <img src="./img/logo1.0.png" width="160" height="160" alt="Logo de ServiTrack" class="logo-acceso">
        </a>
    </section>
    <section class="cuerpo-acceso">
        <form action="./php/validacion-usuario.php" method="POST">
        <h2>Iniciar sesión</h2>
        <label for="correo">Correo electrónico</label>
        <input type="text" id="correo" name="correo">
        <label for="contrasena">Contraseña</label>
        <div class="contenedor-contrasena">
            <input type="password" id="contrasena" name="contrasena">
            <button type="button" id="btnContrasena" aria-label="Mostrar contraseña"><img src="./img/ojo.png" width="20" height="20" alt=""></button>
        </div>
        <button type="submit" class="boton-ingresar">Iniciar sesión</button>
        <a href="./registro.php" class="link-registrase">¿No tienes una cuenta? Registrate</a>
        </form>

        <script src="./js/acceso.js"></script>

    </section>
</body>
</html>
