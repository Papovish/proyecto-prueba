<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Formulario Registro Usuario</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        form {
            max-width: 500px;
            margin: auto;
        }
        label {
            display: block;
            margin-top: 15px;
        }
        input, select {
            width: 100%;
            padding: 8px;
            margin-top: 5px;
            box-sizing: border-box;
        }
        button {
            margin-top: 20px;
            padding: 10px;
            width: 100%;
            background-color: #007bff;
            color: white;
            border: none;
            cursor: pointer;
            font-size: 16px;
        }
        button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <h2>Formulario Registro Usuario</h2>
    <form id="usuarioForm" method="POST" action="procesa_usuario.php" onsubmit="return validarFormulario()">
        <label for="rol">Tipo de Usuario:</label>
        <select id="rol" name="rol">
            <option value="">Seleccione</option>
            <option value="propietario">Propietario</option>
            <option value="gestor">Gestor Inmobiliario</option>
        </select>

        <label for="rut">RUT:</label>
        <input type="text" id="rut" name="rut" placeholder="Ej: 12345678-9" />

        <label for="nombre">Nombre Completo:</label>
        <input type="text" id="nombre" name="nombre" />

        <label for="fecha_nacimiento">Fecha de Nacimiento:</label>
        <input type="date" id="fecha_nacimiento" name="fecha_nacimiento" />

        <label for="correo">Correo Electrónico:</label>
        <input type="email" id="correo" name="correo" />

        <label for="contrasena">Contraseña:</label>
        <input type="password" id="contrasena" name="contrasena" />

        <label for="sexo">Sexo:</label>
        <select id="sexo" name="sexo">
            <option value="">Seleccione</option>
            <option value="Masculino">Masculino</option>
            <option value="Femenino">Femenino</option>
            <option value="Otro">Otro</option>
        </select>

        <label for="telefono">Teléfono Móvil:</label>
        <input type="text" id="telefono" name="telefono" placeholder="Ej: 912345678" />

        <label for="num_propiedad">N° de la propiedad según registro de Bienes Raíces (solo para propietarios):</label>
        <input type="text" id="num_propiedad" name="num_propiedad" />

        <button type="submit">Registrar</button>
    </form>

    <script>
        function validarRut(rut) {
            if (!rut || typeof rut !== 'string') return false;
            rut = rut.replace(/\./g, '').replace('-', '');
            if (rut.length < 8) return false;
            const cuerpo = rut.slice(0, -1);
            let dv = rut.slice(-1).toUpperCase();

            let suma = 0;
            let multiplo = 2;

            for (let i = cuerpo.length - 1; i >= 0; i--) {
                suma += parseInt(cuerpo.charAt(i)) * multiplo;
                multiplo = multiplo < 7 ? multiplo + 1 : 2;
            }

            let dvEsperado = 11 - (suma % 11);
            if (dvEsperado === 11) dvEsperado = '0';
            else if (dvEsperado === 10) dvEsperado = 'K';
            else dvEsperado = dvEsperado.toString();

            return dv === dvEsperado;
        }

        function validarContrasena(contrasena) {
            const minLength = 8;
            const mayuscula = /[A-Z]/;
            const minuscula = /[a-z]/;
            const especial = /[!@#$%^&*(),.?":{}|<>]/;

            return (
                contrasena.length >= minLength &&
                mayuscula.test(contrasena) &&
                minuscula.test(contrasena) &&
                especial.test(contrasena)
            );
        }

        function validarFormulario() {
            const rol = document.getElementById('rol').value;
            const rut = document.getElementById('rut').value.trim();
            const nombre = document.getElementById('nombre').value.trim();
            const fechaNacimiento = document.getElementById('fecha_nacimiento').value.trim();
            const correo = document.getElementById('correo').value.trim();
            const contrasena = document.getElementById('contrasena').value;
            const sexo = document.getElementById('sexo').value;
            const telefono = document.getElementById('telefono').value.trim();
            const numPropiedad = document.getElementById('num_propiedad').value.trim();

            if (!rol || !rut || !nombre || !fechaNacimiento || !correo || !contrasena || !sexo || !telefono) {
                Swal.fire('Advertencia', 'Por favor complete todos los campos obligatorios.', 'warning');
                return false;
            }

            if (rol === 'propietario' && numPropiedad.length === 0) {
                Swal.fire('Error', 'El número de la propiedad es obligatorio para propietarios.', 'error');
                return false;
            }

            if (!validarRut(rut)) {
                Swal.fire('Error', 'El RUT ingresado no es válido.', 'error');
                return false;
            }

            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(correo)) {
                Swal.fire('Error', 'El correo electrónico no tiene un formato válido.', 'error');
                return false;
            }

            const telefonoRegex = /^\d{8,15}$/;
            if (!telefonoRegex.test(telefono)) {
                Swal.fire('Error', 'El teléfono móvil debe ser numérico y tener entre 8 y 15 dígitos.', 'error');
                return false;
            }

            if (!validarContrasena(contrasena)) {
                Swal.fire('Error', 'La contraseña debe tener mínimo 8 caracteres, al menos una letra mayúscula, una minúscula y un carácter especial.', 'error');
                return false;
            }

            return true;
        }
    </script>
</body>
</html>
