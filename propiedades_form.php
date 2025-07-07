<?php
session_start();
if (!isset($_SESSION['usuario']) || !isset($_SESSION['tipo']) || $_SESSION['tipo'] !== 'propietario') {
    header("Location: error.html");
    exit();
}

require_once 'setup/config.php'; // DB connection

$usuario = $_SESSION['usuario'];
$id = $_GET['id'] ?? null;

$propiedad = [
    'titulo' => '',
    'descripcion' => '',
    'precio' => '',
    'superficie_construida' => '',
    'dormitorios' => '',
    'banos' => '',
    'piscina' => '',
    'estacionamientos' => '',
    'otros_atributos' => '',
];

$editing = false;

try {
    $pdo = new PDO($dsn, $db_user, $db_password, $options);

    if ($id) {
        // Fetch property to edit, ensure it belongs to logged-in propietario
        $stmt = $pdo->prepare("SELECT * FROM propiedades WHERE id = :id AND propietario_usuario = :usuario");
        $stmt->execute(['id' => $id, 'usuario' => $usuario]);
        $propiedad = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$propiedad) {
            die("Propiedad no encontrada o acceso denegado.");
        }
        $editing = true;

        // Fetch images for this property
        $stmtImg = $pdo->prepare("SELECT * FROM propiedad_imagenes WHERE propiedad_id = :id ORDER BY es_principal DESC, id ASC");
        $stmtImg->execute(['id' => $id]);
        $imagenes = $stmtImg->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $imagenes = [];
    }
} catch (PDOException $e) {
    die("Error en la conexión a la base de datos: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title><?= $editing ? 'Editar Propiedad' : 'Agregar Propiedad' ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/boxicons@latest/css/boxicons.min.css" />
  <link rel="stylesheet" href="css/form.css">
  <style>
    .thumbnail {
      width: 100px;
      height: 75px;
      object-fit: cover;
      margin-right: 10px;
      border: 2px solid transparent;
      cursor: pointer;
    }
    .thumbnail.selected {
      border-color: #0d6efd;
    }
  </style>
</head>
<body>
<div class="container my-4">
  <h1 class="mb-4"><?= $editing ? 'Editar Propiedad' : 'Agregar Propiedad' ?></h1>
  <form action="propiedades_process.php" method="post" enctype="multipart/form-data">
    <input type="hidden" name="action" value="save" />
    <input type="hidden" name="id" value="<?= $editing ? htmlspecialchars($id) : '' ?>" />
    <div class="mb-3">
      <label for="titulo" class="form-label">Título o Nombre de la Propiedad</label>
      <input type="text" class="form-control" id="titulo" name="titulo" required value="<?= htmlspecialchars($propiedad['titulo'] ?? '') ?>" />
    </div>
    <div class="mb-3">
      <label for="region" class="form-label">Región</label>
      <select class="form-select" id="region" name="region" required>
        <option value="" disabled <?= empty($propiedad['region']) ? 'selected' : '' ?>>Seleccione Región</option>
        <option value="no" <?= (isset($propiedad['region']) && $propiedad['region'] === 'no') ? 'selected' : '' ?>>No aplica</option>
        <option value="Región de Arica y Parinacota" <?= (isset($propiedad['region']) && $propiedad['region'] === 'Región de Arica y Parinacota') ? 'selected' : '' ?>>Región de Arica y Parinacota</option>
        <option value="Región de Tarapacá" <?= (isset($propiedad['region']) && $propiedad['region'] === 'Región de Tarapacá') ? 'selected' : '' ?>>Región de Tarapacá</option>
        <option value="Región de Antofagasta" <?= (isset($propiedad['region']) && $propiedad['region'] === 'Región de Antofagasta') ? 'selected' : '' ?>>Región de Antofagasta</option>
        <option value="Región de Atacama" <?= (isset($propiedad['region']) && $propiedad['region'] === 'Región de Atacama') ? 'selected' : '' ?>>Región de Atacama</option>
        <option value="Región de Coquimbo" <?= (isset($propiedad['region']) && $propiedad['region'] === 'Región de Coquimbo') ? 'selected' : '' ?>>Región de Coquimbo</option>
        <option value="Región de Valparaíso" <?= (isset($propiedad['region']) && $propiedad['region'] === 'Región de Valparaíso') ? 'selected' : '' ?>>Región de Valparaíso</option>
        <option value="Región Metropolitana de Santiago" <?= (isset($propiedad['region']) && $propiedad['region'] === 'Región Metropolitana de Santiago') ? 'selected' : '' ?>>Región Metropolitana de Santiago</option>
        <option value="Región del Libertador General Bernardo O'Higgins" <?= (isset($propiedad['region']) && $propiedad['region'] === "Región del Libertador General Bernardo O'Higgins") ? 'selected' : '' ?>>Región del Libertador General Bernardo O'Higgins</option>
        <option value="Región del Maule" <?= (isset($propiedad['region']) && $propiedad['region'] === 'Región del Maule') ? 'selected' : '' ?>>Región del Maule</option>
        <option value="Región de Ñuble" <?= (isset($propiedad['region']) && $propiedad['region'] === 'Región de Ñuble') ? 'selected' : '' ?>>Región de Ñuble</option>
        <option value="Región del Biobío" <?= (isset($propiedad['region']) && $propiedad['region'] === 'Región del Biobío') ? 'selected' : '' ?>>Región del Biobío</option>
        <option value="Región de La Araucanía" <?= (isset($propiedad['region']) && $propiedad['region'] === 'Región de La Araucanía') ? 'selected' : '' ?>>Región de La Araucanía</option>
        <option value="Región de Los Ríos" <?= (isset($propiedad['region']) && $propiedad['region'] === 'Región de Los Ríos') ? 'selected' : '' ?>>Región de Los Ríos</option>
        <option value="Región de Los Lagos" <?= (isset($propiedad['region']) && $propiedad['region'] === 'Región de Los Lagos') ? 'selected' : '' ?>>Región de Los Lagos</option>
        <option value="Región de Aysén del General Carlos Ibáñez del Campo" <?= (isset($propiedad['region']) && $propiedad['region'] === 'Región de Aysén del General Carlos Ibáñez del Campo') ? 'selected' : '' ?>>Región de Aysén del General Carlos Ibáñez del Campo</option>
        <option value="Región de Magallanes y de la Antártica Chilena" <?= (isset($propiedad['region']) && $propiedad['region'] === 'Región de Magallanes y de la Antártica Chilena') ? 'selected' : '' ?>>Región de Magallanes y de la Antártica Chilena</option>
      </select>
    </div>
    <div class="mb-3">
      <label for="provincia" class="form-label">Provincia</label>
      <select class="form-select" id="provincia" name="provincia" required>
        <option value="" disabled <?= empty($propiedad['provincia']) ? 'selected' : '' ?>>Seleccione Provincia</option>
        <option value="no" <?= (isset($propiedad['provincia']) && $propiedad['provincia'] === 'no') ? 'selected' : '' ?>>No aplica</option>
      </select>
    </div>
    <div class="mb-3">
      <label for="sector" class="form-label">Sector</label>
      <select class="form-select" id="sector" name="sector" required>
        <option value="" disabled <?= empty($propiedad['sector']) ? 'selected' : '' ?>>Seleccione Sector</option>
        <option value="no" <?= (isset($propiedad['sector']) && $propiedad['sector'] === 'no') ? 'selected' : '' ?>>No aplica</option>
      </select>
    </div>
    <div class="mb-3">
      <label for="descripcion" class="form-label">Descripción</label>
      <textarea class="form-control" id="descripcion" name="descripcion" rows="4" required><?= htmlspecialchars($propiedad['descripcion'] ?? '') ?></textarea>
    </div>
    <div class="mb-3">
      <label for="precio" class="form-label">Precio</label>
      <input type="number" class="form-control" id="precio" name="precio" required value="<?= htmlspecialchars($propiedad['precio'] ?? '') ?>" />
    </div>
    <div class="mb-3">
      <label for="superficie_construida" class="form-label">Superficie Construida (m²)</label>
      <input type="number" class="form-control" id="superficie_construida" name="superficie_construida" required value="<?= htmlspecialchars($propiedad['superficie_construida'] ?? '') ?>" />
    </div>
    <div class="mb-3">
      <label for="dormitorios" class="form-label">Cantidad de Dormitorios</label>
      <input type="number" class="form-control" id="dormitorios" name="dormitorios" required value="<?= htmlspecialchars($propiedad['dormitorios'] ?? '') ?>" />
    </div>
    <div class="mb-3">
      <label for="banos" class="form-label">Cantidad de Baños</label>
      <input type="number" class="form-control" id="banos" name="banos" required value="<?= htmlspecialchars($propiedad['banos'] ?? '') ?>" />
    </div>
    <div class="mb-3 form-check">
      <input type="checkbox" class="form-check-input" id="piscina" name="piscina" value="1" <?= !empty($propiedad['piscina']) ? 'checked' : '' ?> />
      <label class="form-check-label" for="piscina">Piscina</label>
    </div>
    <div class="mb-3">
      <label for="estacionamientos" class="form-label">Cantidad de Estacionamientos</label>
      <input type="number" class="form-control" id="estacionamientos" name="estacionamientos" required value="<?= htmlspecialchars($propiedad['estacionamientos'] ?? '') ?>" />
    </div>
    <div class="mb-3">
      <label for="otros_atributos" class="form-label">Otros Atributos Destacados</label>
      <textarea class="form-control" id="otros_atributos" name="otros_atributos" rows="3"><?= htmlspecialchars($propiedad['otros_atributos'] ?? '') ?></textarea>
    </div>

    <div class="mb-3">
      <label class="form-label">Imágenes (hasta 10)</label>
      <input type="file" class="form-control" name="imagenes[]" id="imagenes" accept=".jpg,.jpeg,.png,.webp" multiple />
      <div class="mt-3 d-flex flex-wrap" id="preview-container">
        <?php if (!empty($imagenes)): ?>
          <?php foreach ($imagenes as $img): ?>
            <div class="position-relative me-2 mb-2">
              <img src="uploads/<?= htmlspecialchars($img['nombre_archivo']) ?>" class="thumbnail <?= $img['es_principal'] ? 'selected' : '' ?>" data-img-id="<?= $img['id'] ?>" alt="Imagen" />
              <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 remove-img-btn" data-img-id="<?= $img['id'] ?>">X</button>
              <div class="form-check">
                <input class="form-check-input principal-radio" type="radio" name="imagen_principal" value="<?= $img['id'] ?>" <?= $img['es_principal'] ? 'checked' : '' ?> />
                <label class="form-check-label">Principal</label>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>

    <button type="submit" class="btn btn-primary"><?= $editing ? 'Actualizar' : 'Guardar' ?></button>
    <a href="propiedades.php" class="btn btn-secondary">Cancelar</a>
  </form>
</div>

<script>
  const regionProvincias = {
    "Región de Arica y Parinacota": ["Arica", "Parinacota"],
    "Región de Tarapacá": ["Iquique", "Tamarugal"],
    "Región de Antofagasta": ["Antofagasta", "El Loa", "Tocopilla"],
    "Región de Atacama": ["Chañaral", "Copiapó", "Huasco"],
    "Región de Coquimbo": ["Elqui", "Limarí", "Choapa"],
    "Región de Valparaíso": ["Valparaíso", "Isla de Pascua", "Los Andes", "Petorca", "Quillota", "San Antonio", "San Felipe de Aconcagua", "Marga Marga"],
    "Región Metropolitana de Santiago": ["Santiago", "Cordillera", "Chacabuco", "Maipo", "Melipilla", "Talagante"],
    "Región del Libertador General Bernardo O'Higgins": ["Cachapoal", "Cardenal Caro", "Colchagua"],
    "Región del Maule": ["Talca", "Cauquenes", "Curicó", "Linares"],
    "Región de Ñuble": ["Diguillín", "Itata", "Punilla"],
    "Región del Biobío": ["Concepción", "Arauco", "Biobío"],
    "Región de La Araucanía": ["Cautín", "Malleco"],
    "Región de Los Ríos": ["Valdivia", "Ranco"],
    "Región de Los Lagos": ["Chiloé", "Llanquihue", "Osorno", "Palena"],
    "Región de Aysén del General Carlos Ibáñez del Campo": ["Aysén", "Capitán Prat", "Coihaique", "General Carrera"],
    "Región de Magallanes y de la Antártica Chilena": ["Antártica", "Magallanes", "Tierra del Fuego", "Última Esperanza"]
  };

  const provinciaSectores = {
    "Arica": ["Camarones", "Putre", "General Lagos"],
    "Parinacota": ["General Lagos", "Putre"],
    "Iquique": ["Alto Hospicio", "Iquique"],
    "Tamarugal": ["Pozo Almonte", "Huara", "Pica", "Camiña"],
    "Antofagasta": ["Antofagasta", "Mejillones", "Sierra Gorda", "Taltal"],
    "El Loa": ["Calama", "Ollagüe", "San Pedro de Atacama"],
    "Tocopilla": ["Tocopilla"],
    "Chañaral": ["Chañaral", "Diego de Almagro"],
    "Copiapó": ["Copiapó", "Caldera", "Tierra Amarilla"],
    "Huasco": ["Vallenar", "Alto del Carmen", "Freirina", "Huasco"],
    "Elqui": ["La Serena", "Coquimbo", "Andacollo", "La Higuera", "Paihuano", "Vicuña"],
    "Limarí": ["Ovalle", "Combarbalá", "Monte Patria", "Punitaqui", "Río Hurtado"],
    "Choapa": ["Illapel", "Canela", "Los Vilos", "Salamanca"],
    "Valparaíso": ["Valparaíso", "Casablanca", "Concón", "Juan Fernández", "Puchuncaví", "Quintero", "Viña del Mar", "Isla de Pascua", "Los Andes", "San Esteban", "La Ligua", "Cabildo", "Petorca", "Zapallar", "Papudo", "Quillota", "Calera", "Hijuelas", "La Cruz", "Nogales", "San Antonio", "Algarrobo", "Cartagena", "El Quisco", "El Tabo", "Santo Domingo", "San Felipe", "Catemu", "Llaillay", "Panquehue", "Putaendo", "Santa María", "Quilpué", "Limache", "Olmué", "Villa Alemana"],
    "Isla de Pascua": ["Isla de Pascua"],
    "Los Andes": ["Los Andes", "Calle Larga", "Rinconada", "San Esteban"],
    "Petorca": ["Petorca", "La Ligua", "Cabildo"],
    "Quillota": ["Quillota", "Calera", "Hijuelas", "La Cruz", "Nogales"],
    "San Antonio": ["San Antonio", "Algarrobo", "Cartagena", "El Quisco", "El Tabo", "Santo Domingo"],
    "San Felipe de Aconcagua": ["San Felipe", "Catemu", "Llaillay", "Panquehue", "Putaendo", "Santa María"],
    "Marga Marga": ["Quilpué", "Limache", "Olmué", "Villa Alemana"],
    "Santiago": ["Cerrillos", "Cerro Navia", "Conchalí", "El Bosque", "Estación Central", "Huechuraba", "Independencia", "La Cisterna", "La Florida", "La Granja", "La Pintana", "La Reina", "Las Condes", "Lo Barnechea", "Lo Espejo", "Lo Prado", "Macul", "Maipú", "Ñuñoa", "Pedro Aguirre Cerda", "Peñalolén", "Providencia", "Pudahuel", "Quilicura", "Quinta Normal", "Recoleta", "Renca", "San Joaquín", "San Miguel", "San Ramón", "Vitacura"],
    "Cordillera": ["Puente Alto", "Pirque", "San José de Maipo"],
    "Chacabuco": ["Colina", "Lampa", "Tiltil"],
    "Maipo": ["San Bernardo", "Buin", "Calera de Tango", "Paine"],
    "Melipilla": ["Melipilla", "Alhué", "Curacaví", "María Pinto", "San Pedro"],
    "Talagante": ["Talagante", "El Monte", "Isla de Maipo", "Padre Hurtado", "Peñaflor"],
    "Cachapoal": ["Rancagua", "Codegua", "Coinco", "Coltauco", "Doñihue", "Graneros", "Las Cabras", "Machalí", "Malloa", "Mostazal", "Olivar", "Peumo", "Pichidegua", "Quinta de Tilcoco", "Rengo", "Requínoa", "San Vicente de Tagua Tagua"],
    "Cardenal Caro": ["Pichilemu", "La Estrella", "Litueche", "Marchihue", "Navidad", "Paredones"],
    "Colchagua": ["San Fernando", "Chépica", "Chimbarongo", "Lolol", "Nancagua", "Palmilla", "Peralillo", "Placilla", "Pumanque", "Santa Cruz"],
    "Talca": ["Talca", "Constitución", "Curepto", "Empedrado", "Maule", "Pelarco", "Pencahue", "Río Claro", "San Clemente", "San Rafael"],
    "Cauquenes": ["Cauquenes", "Chanco", "Pelluhue"],
    "Curicó": ["Curicó", "Hualañé", "Licantén", "Molina", "Rauco", "Romeral", "Sagrada Familia", "Teno", "Vichuquén"],
    "Linares": ["Linares", "Colbún", "Longaví", "Parral", "Retiro", "San Javier", "Villa Alegre", "Yerbas Buenas"],
    "Diguillín": ["Chillán", "Bulnes", "Cobquecura", "Coelemu", "Ninhue", "Pemuco", "Pinto", "Quillón", "Ránquil", "San Ignacio", "Yungay"],
    "Itata": ["Cobquecura", "Coelemu", "Ninhue", "Portezuelo", "Quirihue", "Ránquil", "Treguaco"],
    "Punilla": ["Chillán Viejo", "El Carmen", "Pemuco", "San Ignacio", "Yungay"],
    "Concepción": ["Concepción", "Coronel", "Chiguayante", "Florida", "Hualpén", "Hualqui", "Lota", "Penco", "San Pedro de la Paz", "Santa Juana", "Talcahuano", "Tomé"],
    "Arauco": ["Arauco", "Cañete", "Contulmo", "Curanilahue", "Lebu", "Los Álamos", "Tirúa"],
    "Biobío": ["Los Ángeles", "Antuco", "Cabrero", "Laja", "Mulchén", "Nacimiento", "Negrete", "Quilaco", "Quilleco", "San Rosendo", "Santa Bárbara", "Tucapel", "Yumbel"],
    "Cautín": ["Temuco", "Carahue", "Cholchol", "Cunco", "Curarrehue", "Freire", "Galvarino", "Gorbea", "Lautaro", "Loncoche", "Melipeuco", "Nueva Imperial", "Padre Las Casas", "Perquenco", "Pitrufquén", "Pucón", "Saavedra", "Teodoro Schmidt", "Toltén", "Vilcún", "Villarrica"],
    "Malleco": ["Angol", "Collipulli", "Curacautín", "Ercilla", "Lonquimay", "Los Sauces", "Lumaco", "Purén", "Renaico", "Traiguén", "Victoria"],
    "Valdivia": ["Valdivia", "Corral", "Lanco", "Los Lagos", "Máfil", "Mariquina", "Paillaco", "Panguipulli"],
    "Ranco": ["Futrono", "La Unión", "Lago Ranco", "Río Bueno"],
    "Chiloé": ["Ancud", "Castro", "Chonchi", "Curaco de Vélez", "Dalcahue", "Puqueldón", "Queilén", "Quellón", "Quemchi", "Quinchao"],
    "Llanquihue": ["Puerto Montt", "Calbuco", "Cochamó", "Fresia", "Frutillar", "Los Muermos", "Llanquihue", "Maullín", "Puerto Varas"],
    "Osorno": ["Osorno", "Puerto Octay", "Purranque", "Puyehue", "Río Negro", "San Juan de la Costa", "San Pablo"],
    "Palena": ["Chaitén", "Futaleufú", "Hualaihué", "Palena"],
    "Aysén": ["Aysén", "Cisnes", "Guaitecas", "Lago Verde", "O'Higgins", "Tortel"],
    "Capitán Prat": ["Cochrane", "O'Higgins", "Tortel"],
    "Coihaique": ["Coihaique", "Lago Verde"],
    "General Carrera": ["Chile Chico", "Río Ibáñez"],
    "Antártica": ["Antártica"],
    "Magallanes": ["Punta Arenas", "Laguna Blanca", "Río Verde", "San Gregorio", "Porvenir", "Primavera", "Timaukel", "Natales"],
    "Tierra del Fuego": ["Cabo de Hornos"],
    "Última Esperanza": ["Puerto Natales", "Torres del Paine"]
  };

  const regionSelect = document.getElementById('region');
  const provinciaSelect = document.getElementById('provincia');
  const sectorSelect = document.getElementById('sector');

  function populateRegions() {
    Object.keys(regionProvincias).forEach(region => {
      const option = document.createElement('option');
      option.value = region;
      option.textContent = region;
      regionSelect.appendChild(option);
    });
  }

  function populateProvincias(region) {
    provinciaSelect.innerHTML = '<option value="" disabled selected>Seleccione Provincia</option>';
    sectorSelect.innerHTML = '<option value="" disabled selected>Seleccione Sector</option>';
    if (regionProvincias[region]) {
      regionProvincias[region].forEach(provincia => {
        const option = document.createElement('option');
        option.value = provincia;
        option.textContent = provincia;
        provinciaSelect.appendChild(option);
      });
    }
  }

  function populateSectores(provincia) {
    sectorSelect.innerHTML = '<option value="" disabled selected>Seleccione Sector</option>';
    if (provinciaSectores[provincia]) {
      provinciaSectores[provincia].forEach(sector => {
        const option = document.createElement('option');
        option.value = sector;
        option.textContent = sector;
        sectorSelect.appendChild(option);
      });
    }
  }

  regionSelect.addEventListener('change', function() {
    populateProvincias(this.value);
  });

  provinciaSelect.addEventListener('change', function() {
    populateSectores(this.value);
  });

  // Preselect values if editing
  window.addEventListener('DOMContentLoaded', () => {
    populateRegions();
    const currentRegion = "<?= htmlspecialchars($propiedad['region'] ?? '') ?>";
    const currentProvincia = "<?= htmlspecialchars($propiedad['provincia'] ?? '') ?>";
    const currentSector = "<?= htmlspecialchars($propiedad['sector'] ?? '') ?>";

    if (currentRegion) {
      regionSelect.value = currentRegion;
      populateProvincias(currentRegion);
    }
    if (currentProvincia) {
      provinciaSelect.value = currentProvincia;
      populateSectores(currentProvincia);
    }
    if (currentSector) {
      sectorSelect.value = currentSector;
    }
  });
</script>

<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.remove-img-btn').forEach(function(button) {
        button.addEventListener('click', function() {
            const imgId = this.getAttribute('data-img-id');
            if (confirm('¿Está seguro de que desea eliminar esta imagen?')) {
                axios.post('propiedades_process.php', {
                action: 'delete_image',
                image_id: imgId,
                propiedad_id: <?= json_encode($id) ?>
            })
            .then(response => {
                console.log('Respuesta del servidor:', response.data);
                if (response.data.success) {
                    // Remover la miniatura de la imagen de la interfaz
                    this.parentElement.remove();
                } else {
                    alert('Error al eliminar la imagen: ' + (response.data.message || 'Error desconocido'));
                }
            })
            .catch(error => {
                console.error('Error en la comunicación con el servidor:', error);
                alert('Error al comunicarse con el servidor.');
            });
            }
        });
    });
});
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
