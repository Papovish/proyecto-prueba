<?php

session_start();

if(isset( $_SESSION['usuario']) && isset($_SESSION['tipo']) && ($_SESSION['tipo'] === 'gestor' || $_SESSION['tipo'] === 'propietario'))
{ 

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link href="css/login_penca.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link rel="stylesheet" href="css/form.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #fff;
            color: #000;
        }
        #sesion .card {
            border: 2px solid #000;
            background: #fff;
            box-shadow: 0 0 15px rgba(0,0,0,0.2);
            margin: 20px auto;
            max-width: 400px;
        }
        #sesion .card-header,
        #sesion .card-footer {
            background: #000;
            color: #fff;
            font-weight: 900;
            text-transform: uppercase;
            padding: 10px 15px;
            border-bottom: 2px solid #000;
        }
        #sesion .card-footer {
            border-top: 2px solid #000;
            border-bottom: none;
            text-align: center;
        }
        #sesion .card-body {
            padding: 15px;
            font-weight: 600;
            color: #000;
            text-align: center;
        }
        #sesion .card-footer a {
            color: #fff;
            text-decoration: none;
            font-weight: 700;
        }
        #sesion .card-footer a:hover {
            text-decoration: underline;
        }
        .container.mt-3 a.btn-primary {
            background-color: #000 !important;
            border-color: #000 !important;
            color: #fff !important;
            font-weight: 900;
            border-radius: 0;
            padding: 10px 20px;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            transition: background-color 0.3s ease;
        }
        .container.mt-3 a.btn-primary:hover {
            background-color: #222 !important;
            border-color: #222 !important;
            color: #fff !important;
        }
        .container.mt-3 span {
            font-weight: 600;
            color: #000;
        }
    </style>
</head>
<body>
    <div id="sesion">
    <div class="card">
        <div class="card-header">Sesion Iniciada por:</div>
        <div class="card-body"><?php echo $_SESSION['usuario'];?> </div>
        <div class="card-footer"><a href="cerrar.php" >Cerrar Sesion</a></div>
    </div>
    </div>
    <div class="container mt-3 d-flex flex-column gap-3">
        <div>
            <a href="propiedades.php" class="btn btn-primary me-2">Administrar Propiedades</a>
            <span>solo acceso a propietarios</span>
        </div>
        <div>
            <a href="usuarios.php" class="btn btn-primary me-2">Administrar Usuarios</a>
            <span>solo puede el gestor</span>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                title: 'Ingreso Exitoso!',
                text: 'Inicio de sesión exitoso',
                icon: 'success',
                confirmButtonText: 'Continuar'
            });
        });
    </script>
</body>
</html>
<?php
}else{
        header("Location:error.html");


}
?>