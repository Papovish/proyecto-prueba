<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Formulario Gestor Inmobiliario Free</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="css/form.css">
    <script>
        // Función para validar RUT chileno con dígito verificador
        function validarRut(rut) {
            rut = rut.replace(/\./g, '').replace('-', '').toUpperCase();
            if (!/^[0-9]+[0-9K]$/.test(rut)) return false;
            let cuerpo = rut.slice(0, -1);
            let dv = rut.slice(-1);
            let suma = 0;
            let multiplo = 2;
            for (let i = cuerpo.length - 1; i >= 0; i--) {
                suma += parseInt(cuerpo.charAt(i)) * multiplo;
                multiplo = multiplo < 7 ? multiplo + 1 : 2;
            }
            let dvEsperado = 11 - (suma % 11);
            dvEsperado = dvEsperado === 11 ? '0' : dvEsperado === 10 ? 'K' : dvEsperado.toString();
            return dv === dvEsperado;
        }

        // Validación de contraseña robusta
        function validarContrasena(clave) {
            const minLength = 8;
            const mayuscula = /[A-Z]/;
            const minuscula = /[a-z]/;
            const especial = /[!@#$%^&*(),.?":{}|<>]/;
            return clave.length >= minLength &&
                mayuscula.test(clave) &&
                minuscula.test(clave) &&
                especial.test(clave);
        }

            function validarFormulario() {
                console.log("Validando formulario...");
                const rut = document.getElementById('rut').value.trim();
                const nombre = document.getElementById('nombre').value.trim();
                const fechaNacimiento = document.getElementById('fecha_nacimiento').value.trim();
                const correo = document.getElementById('correo').value.trim();
                const clave = document.getElementById('clave').value;
                const sexo = document.querySelector('input[name="sexo"]:checked');
                const telefono = document.getElementById('telefono').value.trim();
                const certificado = document.getElementById('archivo').files[0];

                // Validar que el nombre no contenga números
                if (/\d/.test(nombre)) {
                    Swal.fire('Error', 'El nombre no puede contener números.', 'error');
                    return false;
                }

                // Validar que el correo no sea solo números
                if (/^\d+$/.test(correo)) {
                    Swal.fire('Error', 'El correo no puede ser solo números.', 'error');
                    return false;
                }

                if (!rut || !nombre || !fechaNacimiento || !correo || !clave || !sexo || !telefono || !certificado) {
                    console.log("Faltan campos");
                    Swal.fire('Advertencia', 'Por favor complete todos los campos.', 'warning');
                    return false;
                }

            // Validar que el nombre no contenga números
            const nombreConNumeros = /\d/;
            if (nombreConNumeros.test(nombre)) {
                Swal.fire('Error', 'El nombre no puede contener números.', 'error');
                return false;
            }

            // Validar que la fecha de nacimiento no sea futura
            const fechaHoy = new Date().toISOString().split('T')[0];
            if (fechaNacimiento > fechaHoy) {
                Swal.fire('Error', 'La fecha de nacimiento no puede ser una fecha futura.', 'error');
                return false;
            }

            if (!validarRut(rut)) {
                console.log("RUT inválido");
                Swal.fire('Error', 'El RUT ingresado no es válido.', 'error');
                return false;
            }

            // Validar correo electrónico
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(correo)) {
                console.log("Correo inválido");
                Swal.fire('Error', 'El correo electrónico no tiene un formato válido.', 'error');
                return false;
            }

            // Validar teléfono móvil (numérico y longitud 8-9)
            const telefonoRegex = /^\d{8,9}$/;
            if (!telefonoRegex.test(telefono)) {
                console.log("Teléfono inválido");
                Swal.fire('Error', 'El teléfono móvil debe ser numérico y tener 8 o 9 dígitos.', 'error');
                return false;
            }

            // Validar archivo PDF
            const allowedExtension = /(\.pdf)$/i;
            if (!allowedExtension.exec(certificado.name)) {
                console.log("Archivo inválido");
                Swal.fire('Error', 'El archivo debe ser un PDF.', 'error');
                return false;
            }

            // Validar contraseña robusta
            if (!validarContrasena(clave)) {
                console.log("Contraseña inválida");
                Swal.fire('Error', 'La contraseña debe tener al menos 8 caracteres, una letra mayúscula, una letra minúscula y un carácter especial.', 'error');
                return false;
            }

            return true;
        }
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('gestorForm');
            if (form) {
                console.log("Adjuntando listener al submit del formulario");
                form.addEventListener('submit', function(event) {
                    console.log("Evento submit capturado");
                    if (!validarFormulario()) {
                        console.log("Validación fallida, previniendo envío");
                        event.preventDefault();
                    }
                });
            } else {
                console.log("Formulario no encontrado");
            }
        });
    </script>
</head>
<body>
    <h2>Formulario Gestor Inmobiliario Free</h2>
    <form id="gestorForm" method="POST" action="procesa_gestor.php" enctype="multipart/form-data" class="mx-auto">
        <label for="rut">RUT:</label>
        <input type="text" id="rut" name="rut" placeholder="12345678-9" />

        <label for="nombre">Nombre Completo:</label>
        <input type="text" id="nombre" name="nombre" />

        <label for="fecha_nacimiento">Fecha de Nacimiento:</label>
        <input type="date" id="fecha_nacimiento" name="fecha_nacimiento" max="" />

<script>
document.addEventListener('DOMContentLoaded', function() {
    const fechaInput = document.getElementById('fecha_nacimiento');
    if (fechaInput) {
        const today = new Date().toISOString().split('T')[0];
        fechaInput.setAttribute('max', today);
    }
});
</script>

        <label for="correo">Correo Electrónico:</label>
        <input type="email" id="correo" name="correo" />

        <label for="clave">Contraseña:</label>
        <input type="password" id="clave" name="clave" />

        <label>Sexo:</label>
        <div class="sexo-group">
            <input type="radio" id="sexo_m" name="sexo" value="M" />
            <label for="sexo_m">Masculino</label>
            <input type="radio" id="sexo_f" name="sexo" value="F" />
            <label for="sexo_f">Femenino</label>
        </div>

        <label for="telefono">Teléfono Móvil:</label>
        <input type="text" id="telefono" name="telefono" />

        <label for="archivo">Certificado de Antecedentes (PDF):</label>
        <input type="file" id="archivo" name="archivo" accept="application/pdf" />

        <button type="submit">Registrar</button>
    </form>
</body>
</html>
