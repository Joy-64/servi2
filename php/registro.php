<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro ServiTrack</title>
    <link rel="icon" href="./img/logo3.0.jpeg">
    <link rel="stylesheet" href="./css/style.css">
</head>

<body class="pagina-acceso">

    <section class="top">
        <a href="./index.html">
            <img
                src="./img/logo1.0.png"
                width="160"
                height="160"
                alt="Logo de ServiTrack"
                class="logo-acceso"
            >
        </a>
    </section>

    <section class="cuerpo-acceso">

        <form action="./php/registro-usuario.php" method="POST">

            <h2>Crear una cuenta</h2>

            <label for="correo">Correo electrónico</label>
            <input
                type="email"
                id="correo"
                name="correo"
                required
            >

            <label for="contrasena">Contraseña</label>
            <div class="contenedor-contrasena">
                <input
                    type="password"
                    id="contrasena"
                    name="contrasena"
                    required
                >
                <button type="button" id="btnContrasena" aria-label="Mostrar contraseña">
                    <img src="./img/ojo.png" width="20" height="20" alt="">
                </button>
            </div>

            <label for="confirmarContrasena">Repetir contraseña</label>
            <input
                type="password"
                id="confirmarContrasena"
                name="confirmarContrasena"
                required
            >

            <button type="submit" class="boton-ingresar">Crear cuenta</button>

            <a href="./acceso.php" class="link-registrase">
                ¿Ya tenés una cuenta? Iniciá sesión
            </a>

        </form>

    </section>

    <script src="./js/acceso.js"></script>
</body>

</html>
