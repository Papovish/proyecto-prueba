<?php
session_start();
require_once 'setup/config.php';

$id = $_GET['id'] ?? null;
if (!$id) {
    die("ID de propiedad no especificado.");
}

try {
    $pdo = conectar();

    // Fetch property details
    $stmt = $pdo->prepare("SELECT p.*, pi.nombre_archivo AS imagen_principal
                           FROM propiedades p
                           LEFT JOIN propiedad_imagenes pi ON pi.propiedad_id = p.id AND pi.es_principal = 1
                           WHERE p.id = :id");
    $stmt->execute(['id' => $id]);
    $propiedad = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$propiedad) {
        die("Propiedad no encontrada.");
    }

    // Fetch all images for the property
    $stmtImgs = $pdo->prepare("SELECT nombre_archivo FROM propiedad_imagenes WHERE propiedad_id = :id ORDER BY es_principal DESC, id ASC");
    $stmtImgs->execute(['id' => $id]);
    $imagenes = $stmtImgs->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Error en la conexión a la base de datos: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title><?= htmlspecialchars($propiedad['titulo']) ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/boxicons@latest/css/boxicons.min.css" />
  <style>
    .carousel-item img {
      max-height: 500px;
      object-fit: cover;
      width: 100%;
    }
    .property-icons i {
      font-size: 1.5rem;
      margin-right: 10px;
    }
  </style>
</head>
<body>
<div class="container my-4">
  <h1><?= htmlspecialchars($propiedad['titulo']) ?></h1>

  <?php if (!empty($imagenes)): ?>
  <div id="propertyCarousel" class="carousel slide mb-4" data-bs-ride="carousel">
    <div class="carousel-inner">
      <?php foreach ($imagenes as $index => $img): ?>
      <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
        <img src="uploads/<?= htmlspecialchars($img['nombre_archivo']) ?>" class="d-block w-100" alt="Imagen de <?= htmlspecialchars($propiedad['titulo']) ?>">
      </div>
      <?php endforeach; ?>
    </div>
    <button class="carousel-control-prev" type="button" data-bs-target="#propertyCarousel" data-bs-slide="prev">
      <span class="carousel-control-prev-icon" aria-hidden="true"></span>
      <span class="visually-hidden">Anterior</span>
    </button>
    <button class="carousel-control-next" type="button" data-bs-target="#propertyCarousel" data-bs-slide="next">
      <span class="carousel-control-next-icon" aria-hidden="true"></span>
      <span class="visually-hidden">Siguiente</span>
    </button>
  </div>
  <?php endif; ?>

  <p><?= nl2br(htmlspecialchars($propiedad['descripcion'])) ?></p>

  <div class="property-icons mb-3">
    <i class='bx bx-area' title="Superficie Construida"><?= htmlspecialchars($propiedad['superficie_construida']) ?> m²</i>
    <i class='bx bx-bed' title="Dormitorios"><?= htmlspecialchars($propiedad['dormitorios']) ?></i>
    <i class='bx bxs-bath' title="Baños"><?= htmlspecialchars($propiedad['banos']) ?></i>
    <?php if ($propiedad['piscina']): ?>
      <i class='bx bx-swim' title="Piscina"></i>
    <?php endif; ?>
    <i class='bx bx-car' title="Estacionamientos"><?= htmlspecialchars($propiedad['estacionamientos']) ?></i>
  </div>

  <?php if (!empty($propiedad['otros_atributos'])): ?>
  <h5>Otros Atributos Destacados</h5>
  <p><?= nl2br(htmlspecialchars($propiedad['otros_atributos'])) ?></p>
  <?php endif; ?>

  <a href="propiedadcasi.php" class="btn btn-secondary">Volver al listado</a>
  <div id="map" style="height: 400px; width: 100%; margin-top: 20px;"></div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
    integrity="sha256-sA+e2QY0tP5n17Xt2atTPuE1HxjVMSvLVW9ocqUKLsCCw="
    crossorigin=""/>
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
    integrity="sha256-o9N1jE8V+v0s+gGk5k3t0kQbQbQbQbQbQbQbQbQbQbQ="
    crossorigin=""></script>

  <script>
    window.onload = function () {
      const lat = <?= isset($propiedad['latitude']) ? htmlspecialchars($propiedad['latitude']) : 'null' ?>;
      const lng = <?= isset($propiedad['longitude']) ? htmlspecialchars($propiedad['longitude']) : 'null' ?>;

      // Always use approximate center between La Serena and Coquimbo
      const latLng = [-29.9045, -71.2519];

      const map = L.map('map').setView(latLng, 10);

      L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
      }).addTo(map);

      L.marker(latLng).addTo(map)
        .bindPopup('Ubicación aproximada: La Serena y Coquimbo')
        .openPopup();
    };
  </script>
</body>
</html>
