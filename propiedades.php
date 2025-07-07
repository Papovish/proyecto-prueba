<?php
session_start();
if (!isset($_SESSION['usuario']) || !isset($_SESSION['tipo']) || $_SESSION['tipo'] !== 'propietario') {
    header("Location: error.html");
    exit();
}

require_once 'setup/config.php'; // Assuming this file has DB connection setup

$usuario = $_SESSION['usuario'];

// Fetch properties for the logged-in propietario
try {
    $pdo = conectar();

    $stmt = $pdo->prepare("SELECT * FROM propiedades WHERE propietario_usuario = :usuario");
    $stmt->execute(['usuario' => $usuario]);
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
    <title>Mis Propiedades</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #fff;
            color: #000;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
        }
        h1 {
            font-size: 36px;
            font-weight: bold;
            margin-bottom: 20px;
            text-transform: uppercase;
        }
        table {
            border-collapse: collapse;
            width: 100%;
            max-width: 800px;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #000;
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #000;
            color: #fff;
            text-transform: uppercase;
        }
        a.button, a.btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: #000;
            color: #fff;
            text-decoration: none;
            font-weight: bold;
            text-transform: uppercase;
            border-radius: 4px;
            margin-right: 10px;
        }
        a.button:hover, a.btn:hover {
            background-color: #333;
        }
        p {
            font-size: 18px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <h1>Mis Propiedades</h1>
    <a href="propiedades_form.php" class="button">Agregar Nueva Propiedad</a>
    <?php if (count($propiedades) === 0): ?>
        <p>No tienes propiedades registradas.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Título</th>
                    <th>Descripción</th>
                    <th>Precio</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($propiedades as $propiedad): ?>
                    <tr>
                        <td><?= htmlspecialchars($propiedad['titulo']) ?></td>
                        <td><?= htmlspecialchars(substr($propiedad['descripcion'], 0, 100)) ?>...</td>
                        <td><?= htmlspecialchars($propiedad['precio']) ?></td>
                        <td>
                            <a href="propiedades_form.php?id=<?= $propiedad['id'] ?>" class="btn">Editar</a>
                            <a href="propiedades_process.php?action=delete&id=<?= $propiedad['id'] ?>" class="btn">Eliminar</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
    <a href="index.html" class="button">Volver al inicio</a>
</body>
</html>
