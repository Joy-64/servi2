<?php
// modificar_trabajo.php
// Página para modificar un trabajo. Se entra desde el botón "Modificar" de trabajos.php,
// con el ID en la URL: modificar_trabajo.php?id=3
// Hace dos cosas: muestra el formulario con los datos actuales y, al enviarlo, los valida y los guarda.
// Sin base de datos, el cambio se recuerda en la sesión (ver datos_ejemplo.php).

require "php/datos_ejemplo.php";   // trae $trabajos, $clientes y arranca la sesión

// 1) Saber qué trabajo se modifica. El ID viene por GET (al entrar) o por POST (al enviar).
$id = 0;
if (isset($_GET["id"])) {
    $id = (int)$_GET["id"];
}
if (isset($_POST["id"])) {
    $id = (int)$_POST["id"];
}

// 2) Buscar ese trabajo en la lista. Si no existe, queda en null (vacío).
$trabajo = null;
foreach ($trabajos as $t) {
    if ($t["id"] == $id) {
        $trabajo = $t;
    }
}

if ($trabajo == null) {
    $_SESSION["mensaje"] = "No existe el trabajo #" . $id . ".";
    header("Location: php/trabajos.php");
    exit;
}

// 3) $datos es lo que se muestra en el formulario: al principio, los datos actuales.
$datos = $trabajo;
$errores = [];

// 4) Si se envió el formulario (POST), leemos los datos nuevos y los validamos
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $datos["cliente"]     = $_POST["Cliente"];
    $datos["descripcion"] = trim($_POST["Descripcion"]);
    $datos["fecha"]       = $_POST["Fecha"];
    $datos["horas"]       = (float)$_POST["Horas"];
    $datos["precio_hora"] = (float)$_POST["PrecioHora"];
    $datos["materiales"]  = (float)$_POST["Materiales"];
    $datos["estado"]      = (int)$_POST["Estado"];
    $datos["cobrado"]     = (int)$_POST["Cobrado"];

    if (!in_array($datos["cliente"], $clientes)) {
        $errores[] = "Elegí un cliente de la lista.";
    }
    if ($datos["descripcion"] == "") {
        $errores[] = "Escribí la descripción del trabajo.";
    }
    // strtotime devuelve false si el texto no es una fecha válida
    if ($datos["fecha"] == "" || strtotime($datos["fecha"]) == false) {
        $errores[] = "La fecha no es válida.";
    }
    if ($datos["horas"] < 0 || $datos["precio_hora"] < 0 || $datos["materiales"] < 0) {
        $errores[] = "Horas, precio y materiales no pueden ser negativos.";
    }
    if ($datos["estado"] != 0 && $datos["estado"] != 1) {
        $errores[] = "El estado no es válido.";
    }
    if ($datos["cobrado"] != 0 && $datos["cobrado"] != 1) {
        $errores[] = "El estado de cobro no es válido.";
    }

    // 5) Si no hay errores: guardamos los cambios en la sesión y volvemos a la lista
    if (count($errores) == 0) {
        $_SESSION["cambios"][$id] = [
            "cliente"     => $datos["cliente"],
            "descripcion" => $datos["descripcion"],
            "fecha"       => $datos["fecha"],
            "horas"       => $datos["horas"],
            "precio_hora" => $datos["precio_hora"],
            "materiales"  => $datos["materiales"],
            "estado"      => $datos["estado"],
            "cobrado"     => $datos["cobrado"],
        ];
        $_SESSION["mensaje"] = "El trabajo #" . $id . " se modificó correctamente.";
        header("Location: php/trabajos.php");
        exit;
    }
}
?>
<!doctype html>
<html lang="es">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Modificar trabajo | ServiTrack</title>
    <link rel="icon" href="./img/logo3.0.jpeg" />
    <link rel="stylesheet" href="css/style.css" />
    <link rel="stylesheet" href="css/formularios.css" />
    <link rel="stylesheet" href="./css/style1.css">
  </head>
  <body>
        <!--ACA esta la cabecera-->
    <header class="cabecera">
        <a class="son-grid1 marca" href="#inicio">
            <span class="marca-icono"><img src="./img/logo3.0.jpeg" alt=""></span>
            <span class="marca-texto">Servi<b>Track</b></span>
        </a>
        <button id="menu-btn" type="button" aria-label="Abrir menú" aria-expanded="false">
            <span></span><span></span><span></span>
        </button>
        <nav class="son-grid2" id="menu">
            <ul>
                <li><a class="items-menu" href="#quienes">¿Quiénes somos?</a></li>
                <li><a class="items-menu" href="#trabajo">Nuestro trabajo</a></li>
                <li><a class="items-menu" href="#demo">Demo</a></li>
                <li><a class="items-menu" href="#contacto">Contacto</a></li>
                <li><a class="items-menu" href="php/panel.php">Panel</a></li>
                <li><a class="items-menu" href="CargaDeTrabajo.html">Cargar trabajo</a></li>
                <li><a class="items-menu" href="CargaDeClientes.html">Cargar cliente</a></li>
                <li><a class="items-menu" href="php/trabajos.php">Trabajos</a></li>
                <li class="solo-movil"><a class="boton" href="./acceso.php">Acceso</a></li>
            </ul>
        </nav>
        <div class="son-grid3"><a class="boton" href="./acceso.php">Acceso</a></div>
    </header>
    <!--ACA TERMINA-->

    <main class="formulario">
      <p class="atajos"><a href="php/trabajos.php">← Volver a los trabajos</a></p>
      <h1>Modificar trabajo #<?php echo $id; ?></h1>

      <?php if (count($errores) > 0) { ?>
        <ul class="error-lista">
          <?php foreach ($errores as $error) { ?>
            <li><?php echo htmlspecialchars($error); ?></li>
          <?php } ?>
        </ul>
      <?php } ?>

      <form action="php/modificar_trabajo.php" method="post">
        <!-- Campo oculto: así el formulario recuerda qué trabajo se está modificando -->
        <input type="hidden" name="id" value="<?php echo $id; ?>" />

        <fieldset>
          <legend>Datos del trabajo</legend>

          <div class="campo">
            <label for="Cliente">Cliente *</label>
            <select name="Cliente" id="Cliente">
              <?php foreach ($clientes as $nombre) { ?>
                <!-- selected deja marcada la opción que ya tenía el trabajo -->
                <option value="<?php echo htmlspecialchars($nombre); ?>" <?php if ($datos["cliente"] == $nombre) { echo "selected"; } ?>>
                  <?php echo htmlspecialchars($nombre); ?>
                </option>
              <?php } ?>
            </select>
          </div>

          <div class="campo">
            <label for="Descripcion">Trabajo realizado *</label>
            <textarea name="Descripcion" id="Descripcion" rows="3"><?php echo htmlspecialchars($datos["descripcion"]); ?></textarea>
          </div>

          <div class="campo">
            <label for="Fecha">Fecha *</label>
            <input type="date" name="Fecha" id="Fecha" value="<?php echo htmlspecialchars($datos["fecha"]); ?>" />
          </div>

          <div class="fila">
            <div class="campo">
              <label for="Estado">Estado</label>
              <select name="Estado" id="Estado">
                <option value="0" <?php if ($datos["estado"] == 0) { echo "selected"; } ?>>En curso</option>
                <option value="1" <?php if ($datos["estado"] == 1) { echo "selected"; } ?>>Terminado</option>
              </select>
            </div>
            <div class="campo">
              <label for="Cobrado">Cobro</label>
              <select name="Cobrado" id="Cobrado">
                <option value="0" <?php if ($datos["cobrado"] == 0) { echo "selected"; } ?>>Pendiente de pago</option>
                <option value="1" <?php if ($datos["cobrado"] == 1) { echo "selected"; } ?>>Cobrado</option>
              </select>
            </div>
          </div>
        </fieldset>

        <fieldset>
          <legend>Horas y presupuesto</legend>
          <div class="fila">
            <div class="campo">
              <label for="Horas">Horas trabajadas</label>
              <input type="number" name="Horas" id="Horas" min="0" step="0.5" value="<?php echo $datos["horas"]; ?>" />
            </div>
            <div class="campo">
              <label for="PrecioHora">Precio por hora ($)</label>
              <input type="number" name="PrecioHora" id="PrecioHora" min="0" step="0.01" value="<?php echo $datos["precio_hora"]; ?>" />
            </div>
            <div class="campo">
              <label for="Materiales">Materiales ($)</label>
              <input type="number" name="Materiales" id="Materiales" min="0" step="0.01" value="<?php echo $datos["materiales"]; ?>" />
            </div>
          </div>
          <p>El total se calcula al guardar: horas × precio por hora + materiales.</p>
        </fieldset>

        <div class="botones">
          <button type="submit" class="boton">Guardar cambios</button>
          <a class="boton boton-sec" href="php/trabajos.php">Cancelar</a>
        </div>
      </form>
    </main>

    <!-- Pie de página, igual en todas las páginas -->
    <footer class="footer">
      <div class="footer-logo">
        <a href="inicio.html">
          <img src="./img/logo 2.1.png" width="100px" height="100px" alt="Logo ServiTrack" />
        </a>
      </div>
      <nav class="foot-enlace">
        <a href="index.html">Inicio</a>
        <a href="servicios.html">Servicios</a>
        <a href="quienes-somos.html">Quienes somos</a>
        <a href="contacto.html">Contacto</a>
      </nav>
      <p class="derechos">&copy; <?php echo date("Y"); ?> | ServiTrack. Todos los derechos reservados.</p>
    </footer>
  </body>
</html>
