<?php
// panel.php
// Pantalla principal: resumen, accesos rápidos y listas de trabajos.
// Los datos son de EJEMPLO (vienen de datos_ejemplo.php).
// Más adelante se reemplazan por una consulta a la base de datos.

// Los datos de ejemplo están en datos_ejemplo.php (los comparte con trabajos.php).
// Ahí se define la lista $trabajos y el número $total_clientes.
require_once "datos_ejemplo.php";


// Recorremos los trabajos una vez para armar los números y la lista de pendientes
$pendientes = [];     // lista vacía donde guardamos los trabajos en curso
$cantidad_mes = 0;    // contador de trabajos de este mes

foreach ($trabajos as $t) {
    if ($t["estado"] == 0) {
        $pendientes[] = $t;   // agrega este trabajo al final de la lista
    }
    // substr toma los primeros 7 caracteres de la fecha: "2026-10". date("Y-m") es el mes actual.
    if (substr($t["fecha"], 0, 7) == date("Y-m")) {
        $cantidad_mes = $cantidad_mes + 1;
    }
}

// Últimos 5 trabajos: damos vuelta la lista (el más nuevo primero) y tomamos 5
$ultimos = array_slice(array_reverse($trabajos), 0, 5);
?>
<!doctype html>
<html lang="es">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Panel | ServiTrack</title>
    <link rel="icon" href="../img/logo3.0.jpeg" />
    <link rel="stylesheet" href="../css/style.css" />
        <link rel="stylesheet" href="../css/style1.css" />
    <link rel="stylesheet" href="../css/panel.css" />
  </head>
  <body>
        <!--ACA esta la cabecera-->
    <header class="cabecera">
        <a class="son-grid1 marca" href="#inicio">
            <span class="marca-icono"><img src="../img/logo3.0.jpeg" alt=""></span>
            <span class="marca-texto">Servi<b>Track</b></span>
        </a>
        <button id="menu-btn" type="button" aria-label="Abrir menú" aria-expanded="false">
            <span></span><span></span><span></span>
        </button>
        <nav class="son-grid2" id="menu">
            <ul>
                <li><a class="items-menu" href="../index.html#quienes">¿Quiénes somos?</a></li>
                <li><a class="items-menu" href="../index.html#trabajo">Nuestro trabajo</a></li>
                <li><a class="items-menu" href="../index.html#demo">Demo</a></li>
                <li><a class="items-menu" href="../index.html#contacto">Contacto</a></li>
                <li><a class="items-menu" href="php/panel.php">Panel</a></li>
                <li><a class="items-menu" href="../CargaDeTrabajo.html">Cargar trabajo</a></li>
                <li><a class="items-menu" href="../CargaDeClientes.html">Cargar cliente</a></li>
                <li><a class="items-menu" href="trabajos.php">Trabajos</a></li>
                
            </ul>
        </nav>
        
    </header>
    <!--ACA TERMINA-->

    <main class="panel">
      <h1>Panel</h1>

      <!-- Resumen: los números los calculó PHP arriba; echo los escribe en la página -->
      <section class="resumen">
        <div class="dato dato-pendiente">
          <strong><?php echo count($pendientes); ?></strong>
          trabajos pendientes
        </div>
        <div class="dato">
          <strong><?php echo $cantidad_mes; ?></strong>
          trabajos este mes
        </div>
        <div class="dato">
          <strong><?php echo $total_clientes; ?></strong>
          clientes cargados
        </div>
      </section>

      <!-- Accesos rápidos: son enlaces con forma de tarjeta -->
      <h2>¿Qué querés hacer?</h2>
      <section class="accesos">
        <a class="acceso" href="../CargaDeTrabajo.html">
          <b>Cargar trabajo</b>
          Registrá lo que hiciste.
        </a>
        <a class="acceso" href="../CargaDeClientes.html">
          <b>Cargar cliente</b>
          Agregá un cliente nuevo.
        </a>
        <a class="acceso" href="../php/trabajos.php">
          <b>Ver todos los trabajos</b>
          Pendientes, terminados y cobros.
        </a>
      </section>

      <div class="listas">
        <!-- Lista 1: trabajos pendientes -->
        <section class="caja">
          <h2>Trabajos pendientes</h2>
          <?php if (count($pendientes) == 0) { ?>
            <p>No tenés trabajos pendientes.</p>
          <?php } else { ?>
            <ul>
              <?php foreach ($pendientes as $t) { ?>
                <li>
                  <b><?php echo htmlspecialchars($t["cliente"]); ?></b>
                  <span><?php echo htmlspecialchars($t["descripcion"]); ?></span>
                  <span>Desde <?php echo date("d/m/Y", strtotime($t["fecha"])); ?></span>
                </li>
              <?php } ?>
            </ul>
          <?php } ?>
        </section>

        <!-- Lista 2: últimos trabajos -->
        <section class="caja">
          <h2>Últimos trabajos</h2>
          <?php if (count($ultimos) == 0) { ?>
            <p>Todavía no cargaste trabajos.</p>
          <?php } else { ?>
            <ul>
              <?php foreach ($ultimos as $t) { ?>
                <li>
                  <b><?php echo htmlspecialchars($t["cliente"]); ?></b>
                  <span>
                    <?php echo date("d/m/Y", strtotime($t["fecha"])); ?> · <?php echo $t["horas"]; ?> hs
                    <?php if ($t["estado"] == 1) { ?>
                      <span class="estado estado-terminado">Terminado</span>
                    <?php } else { ?>
                      <span class="estado estado-curso">En curso</span>
                    <?php } ?>
                  </span>
                </li>
              <?php } ?>
            </ul>
          <?php } ?>
        </section>
      </div>
    </main>

    <!-- Pie de página, igual en todas las páginas -->
    <footer class="footer">
      <div class="footer-logo">
        <a href="inicio.html">
          <img src="../img/logo3.0.jpeg" width="100px" height="100px" alt="Logo ServiTrack" />
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
