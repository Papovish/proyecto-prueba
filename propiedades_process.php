<?php
session_start();
if (!isset($_SESSION['usuario']) || !isset($_SESSION['tipo']) || $_SESSION['tipo'] !== 'propietario') {
    header("Location: error.html");
    exit();
}

require_once 'setup/config.php'; // DB connection

$usuario = $_SESSION['usuario'];
$action = $_GET['action'] ?? $_POST['action'] ?? null;

try {
    $pdo = new PDO($dsn, $db_user, $db_password, $options);

    if ($action === 'delete') {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            die("ID de propiedad no especificado.");
        }
        // Verify ownership
        $stmt = $pdo->prepare("SELECT * FROM propiedades WHERE id = :id AND propietario_usuario = :usuario");
        $stmt->execute(['id' => $id, 'usuario' => $usuario]);
        $propiedad = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$propiedad) {
            die("Propiedad no encontrada o acceso denegado.");
        }
        // Delete images
        $stmtImg = $pdo->prepare("SELECT nombre_archivo FROM propiedad_imagenes WHERE propiedad_id = :id");
        $stmtImg->execute(['id' => $id]);
        $imagenes = $stmtImg->fetchAll(PDO::FETCH_ASSOC);
        foreach ($imagenes as $img) {
            $filePath = 'uploads/' . $img['nombre_archivo'];
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }
        $pdo->prepare("DELETE FROM propiedad_imagenes WHERE propiedad_id = :id")->execute(['id' => $id]);
        // Delete property
        $pdo->prepare("DELETE FROM propiedades WHERE id = :id")->execute(['id' => $id]);

        header("Location: propiedades.php");
        exit();
    } elseif ($action === 'save') {
        // Save or update property
        $id = $_POST['id'] ?? null;
        $titulo = $_POST['titulo'] ?? '';
        $region = $_POST['region'] ?? '';
        if ($region === 'no') {
            $region = '';
        }
        $provincia = $_POST['provincia'] ?? '';
        if ($provincia === 'no') {
            $provincia = '';
        }
        $sector = $_POST['sector'] ?? '';
        if ($sector === 'no') {
            $sector = '';
        }
        $descripcion = $_POST['descripcion'] ?? '';
        $precio = $_POST['precio'] ?? 0;
        $superficie_construida = $_POST['superficie_construida'] ?? 0;
        $dormitorios = $_POST['dormitorios'] ?? 0;
        $banos = $_POST['banos'] ?? 0;
        $piscina = isset($_POST['piscina']) ? 1 : 0;
        $estacionamientos = $_POST['estacionamientos'] ?? 0;
        $otros_atributos = $_POST['otros_atributos'] ?? '';

        if ($id) {
            // Update existing property, verify ownership
            $stmt = $pdo->prepare("SELECT * FROM propiedades WHERE id = :id AND propietario_usuario = :usuario");
            $stmt->execute(['id' => $id, 'usuario' => $usuario]);
            $propiedad = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$propiedad) {
                die("Propiedad no encontrada o acceso denegado.");
            }
            try {
                $stmtUpdate = $pdo->prepare("UPDATE propiedades SET titulo = :titulo, region = :region, provincia = :provincia, sector = :sector, descripcion = :descripcion, precio = :precio, superficie_construida = :superficie_construida, dormitorios = :dormitorios, banos = :banos, piscina = :piscina, estacionamientos = :estacionamientos, otros_atributos = :otros_atributos WHERE id = :id");
                $stmtUpdate->execute([
                    'titulo' => $titulo,
                    'region' => $region,
                    'provincia' => $provincia,
                    'sector' => $sector,
                    'descripcion' => $descripcion,
                    'precio' => $precio,
                    'superficie_construida' => $superficie_construida,
                    'dormitorios' => $dormitorios,
                    'banos' => $banos,
                    'piscina' => $piscina,
                    'estacionamientos' => $estacionamientos,
                    'otros_atributos' => $otros_atributos,
                    'id' => $id
                ]);
            } catch (PDOException $e) {
                die("Error al actualizar la propiedad: " . $e->getMessage());
            }
        } else {
            // Insert new property
$stmtInsert = $pdo->prepare("INSERT INTO propiedades (propietario_usuario, titulo, region, provincia, sector, descripcion, precio, superficie_construida, dormitorios, banos, piscina, estacionamientos, otros_atributos, fecha_creacion) VALUES (:usuario, :titulo, :region, :provincia, :sector, :descripcion, :precio, :superficie_construida, :dormitorios, :banos, :piscina, :estacionamientos, :otros_atributos, :fecha_creacion)");
$stmtInsert->execute([
    'usuario' => $usuario,
    'titulo' => $titulo,
    'region' => $region,
    'provincia' => $provincia,
    'sector' => $sector,
    'descripcion' => $descripcion,
    'precio' => $precio,
    'superficie_construida' => $superficie_construida,
    'dormitorios' => $dormitorios,
    'banos' => $banos,
    'piscina' => $piscina,
    'estacionamientos' => $estacionamientos,
    'otros_atributos' => $otros_atributos,
    'fecha_creacion' => date('Y-m-d H:i:s')
]);
            $id = $pdo->lastInsertId();
        }

        // Handle image uploads
        if (!empty($_FILES['imagenes']['name'][0])) {
            $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
            $uploadDir = 'uploads/';
            $uploadedCount = 0;

            // Count existing images
            $stmtCount = $pdo->prepare("SELECT COUNT(*) FROM propiedad_imagenes WHERE propiedad_id = :id");
            $stmtCount->execute(['id' => $id]);
            $existingCount = $stmtCount->fetchColumn();

            foreach ($_FILES['imagenes']['tmp_name'] as $index => $tmpName) {
                if ($uploadedCount + $existingCount >= 10) {
                    break; // Max 10 images
                }
                $fileType = $_FILES['imagenes']['type'][$index];
                if (!in_array($fileType, $allowedTypes)) {
                    continue; // Skip invalid format
                }
                $fileName = uniqid() . '_' . basename($_FILES['imagenes']['name'][$index]);
                $targetFile = $uploadDir . $fileName;
                if (move_uploaded_file($tmpName, $targetFile)) {
                    // Insert image record
                    $stmtImgInsert = $pdo->prepare("INSERT INTO propiedad_imagenes (propiedad_id, nombre_archivo, es_principal) VALUES (:propiedad_id, :nombre_archivo, 0)");
                    $stmtImgInsert->execute(['propiedad_id' => $id, 'nombre_archivo' => $fileName]);
                    $uploadedCount++;
                }
            }
        }

        // Update principal image
        if (isset($_POST['imagen_principal'])) {
            $principalId = $_POST['imagen_principal'];
            // Reset all to 0
            $pdo->prepare("UPDATE propiedad_imagenes SET es_principal = 0 WHERE propiedad_id = :id")->execute(['id' => $id]);
            // Set selected to 1
            $pdo->prepare("UPDATE propiedad_imagenes SET es_principal = 1 WHERE id = :img_id AND propiedad_id = :id")->execute(['img_id' => $principalId, 'id' => $id]);
        }

        header("Location: propiedades.php");
        exit();
    } else {
        die("Acción no válida.");
    }
} catch (PDOException $e) {
    die("Error en la conexión a la base de datos: " . $e->getMessage());
}
?>