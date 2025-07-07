<?php
session_start();
require_once 'setup/config.php';

try {
    $pdo = conectar();

    // Fetch all properties
    $stmt = $pdo->query("SELECT p.*, pi.nombre_archivo AS imagen_principal
                         FROM propiedades p
                         LEFT JOIN propiedad_imagenes pi ON pi.propiedad_id = p.id AND pi.es_principal = 1
                         ORDER BY p.id DESC");
    $propiedades = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error en la conexión a la base de datos: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Portal de Propiedades</title>
  <link rel="stylesheet" href="css/propiedadcasi.css" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/boxicons@latest/css/boxicons.min.css" />
</head>
<body>
<div class="main-container">

<header>
  <h1> PROPIEDADES PNK INMOBILIARIA</h1>
</header>
<a href="index.html" class="btn" style="margin: 10px 0;">Volver al inicio</a>

<section class="buscador">
  <select id="regionSelect">
    <option value="" disabled selected>Seleccione Región</option>
    <option value="Región de Arica y Parinacota">Región de Arica y Parinacota</option>
    <option value="Región de Tarapacá">Región de Tarapacá</option>
    <option value="Región de Antofagasta">Región de Antofagasta</option>
    <option value="Región de Atacama">Región de Atacama</option>
    <option value="Región de Coquimbo">Región de Coquimbo</option>
    <option value="Región de Valparaíso">Región de Valparaíso</option>
    <option value="Región Metropolitana de Santiago">Región Metropolitana de Santiago</option>
    <option value="Región del Libertador General Bernardo O'Higgins">Región del Libertador General Bernardo O'Higgins</option>
    <option value="Región del Maule">Región del Maule</option>
    <option value="Región de Ñuble">Región de Ñuble</option>
    <option value="Región del Biobío">Región del Biobío</option>
    <option value="Región de La Araucanía">Región de La Araucanía</option>
    <option value="Región de Los Ríos">Región de Los Ríos</option>
    <option value="Región de Los Lagos">Región de Los Lagos</option>
    <option value="Región de Aysén del General Carlos Ibáñez del Campo">Región de Aysén del General Carlos Ibáñez del Campo</option>
    <option value="Región de Magallanes y de la Antártica Chilena">Región de Magallanes y de la Antártica Chilena</option>
  </select>
  <select id="provinciaSelect">
    <option selected disabled>Seleccione Provincia</option>
  </select>
  <select id="sectorSelect">
    <option selected disabled>Seleccione Sector</option>
  </select>
</section>

<div class="propiedades-container">
  <?php foreach ($propiedades as $propiedad): ?>
    <div class="box" data-region="<?= htmlspecialchars($propiedad['region']) ?>" data-provincia="<?= htmlspecialchars($propiedad['provincia']) ?>" data-sector="<?= htmlspecialchars($propiedad['sector']) ?>">
      <img src="uploads/<?= htmlspecialchars($propiedad['imagen_principal'] ?? 'default.jpg') ?>" alt="Imagen de <?= htmlspecialchars($propiedad['titulo']) ?>" />
      <h3>CLP $<?= number_format($propiedad['precio'], 0, ',', '.') ?></h3>
      <div class="content">
        <div class="text">
          <h3><?= htmlspecialchars($propiedad['titulo']) ?></h3>
          <p><?= nl2br(htmlspecialchars(substr($propiedad['descripcion'], 0, 100))) ?>...</p>
          <li><a href="sabermas.php?id=<?= htmlspecialchars($propiedad['id']) ?>">Quiero Saber Más</a></li>
        </div>
        <div class="icon">
          <i class='bx bx-area'><span><?= htmlspecialchars($propiedad['superficie_construida']) ?> m²</span></i>
          <i class='bx bx-bed'><span><?= htmlspecialchars($propiedad['dormitorios']) ?></span></i>
          <i class='bx bxs-bath'><span><?= htmlspecialchars($propiedad['banos']) ?></span></i>
          <?php if ($propiedad['piscina']): ?>
            <i class='bx bx-swim'><span>Piscina</span></i>
          <?php endif; ?>
          <i class='bx bx-car'><span><?= htmlspecialchars($propiedad['estacionamientos']) ?></span></i>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
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

  const regionSelect = document.getElementById('regionSelect');
  const provinciaSelect = document.getElementById('provinciaSelect');
  const sectorSelect = document.getElementById('sectorSelect');

  regionSelect.addEventListener('change', function() {
    const provincias = regionProvincias[this.value] || [];
    provinciaSelect.innerHTML = '<option selected disabled>Seleccione Provincia</option>';
    provincias.forEach(function(provincia) {
      const option = document.createElement('option');
      option.value = provincia;
      option.textContent = provincia;
      provinciaSelect.appendChild(option);
    });
    sectorSelect.innerHTML = '<option selected disabled>Seleccione Sector</option>';
  });

  provinciaSelect.addEventListener('change', function() {
    const sectores = provinciaSectores[this.value] || [];
    sectorSelect.innerHTML = '<option selected disabled>Seleccione Sector</option>';
    sectores.forEach(function(sector) {
      const option = document.createElement('option');
      option.value = sector;
      option.textContent = sector;
      sectorSelect.appendChild(option);
    });
  });

  // New filtering logic
  function filterProperties() {
    const selectedRegion = regionSelect.value;
    const selectedProvincia = provinciaSelect.value;
    const selectedSector = sectorSelect.value;

    const boxes = document.querySelectorAll('.propiedades-container .box');
    boxes.forEach(box => {
      const boxRegion = box.getAttribute('data-region');
      const boxProvincia = box.getAttribute('data-provincia');
      const boxSector = box.getAttribute('data-sector');

      let show = true;
      if (selectedRegion && boxRegion !== selectedRegion) {
        show = false;
      }
      if (selectedProvincia && boxProvincia !== selectedProvincia) {
        show = false;
      }
      if (selectedSector && boxSector !== selectedSector) {
        show = false;
      }

      box.style.display = show ? 'block' : 'none';
    });
  }

  // Attach filterProperties to change events for real-time filtering
  regionSelect.addEventListener('change', () => {
    filterProperties();
  });
  provinciaSelect.addEventListener('change', () => {
    filterProperties();
  });
  sectorSelect.addEventListener('change', () => {
    filterProperties();
  });

  // Optional: Remove or disable the Buscar button functionality
  document.querySelector('button[onclick="buscar()"]').style.display = 'none';

</script>

</div>
</body>
</html>
