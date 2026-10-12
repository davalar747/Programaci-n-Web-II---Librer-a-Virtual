<?php
require_once 'config/db.php';

$mensaje = '';
$tipo_alerta = '';

// Procesamiento del formulario vía POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre    = trim($_POST['nombre'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $password  = $_POST['password'] ?? '';
    $direccion = trim($_POST['direccion'] ?? '');
    $telefono  = trim($_POST['telefono'] ?? '');

    // Validación básica en el backend
    if (empty($nombre) || empty($email) || empty($password) || empty($direccion) || empty($telefono)) {
        $mensaje = 'Por favor complete todos los campos obligatorios.';
        $tipo_alerta = 'warning';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $mensaje = 'El formato del correo electrónico no es válido.';
        $tipo_alerta = 'warning';
    } else {
        try {
            // Verificar si el correo ya existe
            $checkStmt = $pdo->prepare("SELECT id FROM USUARIOS WHERE email = ?");
            $checkStmt->execute([$email]);

            if ($checkStmt->fetch()) {
                $mensaje = 'El correo electrónico ya se encuentra registrado.';
                $tipo_alerta = 'danger';
            } else {
                // Encriptación segura de la contraseña
                $passwordHash = password_hash($password, PASSWORD_DEFAULT);

                // Inserción en la tabla USUARIOS
                $sql = "INSERT INTO USUARIOS (nombre, email, contraseña, direccion, telefono) VALUES (?, ?, ?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$nombre, $email, $passwordHash, $direccion, $telefono]);

                $mensaje = '¡Usuario registrado exitosamente! Ahora puede iniciar sesión.';
                $tipo_alerta = 'success';
            }
        } catch (PDOException $e) {
            $mensaje = 'Error al registrar el usuario: ' . $e->getMessage();
            $tipo_alerta = 'danger';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Usuario - Librería Virtual</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/styles.css">
</head>
<body class="d-flex flex-column min-vh-100 bg-light">

    <!-- Navegación -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand fw-semibold" href="index.html">Inicio</a>
            <div class="navbar-nav">
                <a class="nav-link" href="index.html#catalogo">Catálogo</a>
                <a class="nav-link active" href="registro_usuario.php">Registro Cliente</a>
                <a class="nav-link" href="registro_libro.php">Nuevo Libro</a>
                <a class="nav-link" href="carrito.php">Carrito</a>
            </div>
        </div>
    </nav>

    <!-- Contenido -->
    <main class="container my-5 flex-grow-1">
        <div class="row justify-content-center">
            <div class="col-md-7 col-lg-6">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-primary text-white py-3">
                        <h4 class="mb-0 text-center">Registro de Nuevo Cliente</h4>
                    </div>
                    <div class="card-body p-4">

                        <?php if (!empty($mensaje)): ?>
                            <div class="alert alert-<?= $tipo_alerta; ?> alert-dismissible fade show" role="alert">
                                <?= htmlspecialchars($mensaje); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <form action="registro_usuario.php" method="POST">
                            <div class="mb-3">
                                <label for="nombre" class="form-label">Nombre Completo</label>
                                <input type="text" class="form-control" id="nombre" name="nombre" required placeholder="Ej. Juan Pérez">
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Correo Electrónico</label>
                                <input type="email" class="form-control" id="email" name="email" required placeholder="juan@ejemplo.com">
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Contraseña</label>
                                <input type="password" class="form-control" id="password" name="password" required placeholder="Mínimo 8 caracteres">
                            </div>
                            <div class="mb-3">
                                <label for="direccion" class="form-label">Dirección de Despacho</label>
                                <input type="text" class="form-control" id="direccion" name="direccion" required placeholder="Av. Libertador 1234">
                            </div>
                            <div class="mb-3">
                                <label for="telefono" class="form-label">Teléfono de Contacto</label>
                                <input type="tel" class="form-control" id="telefono" name="telefono" required placeholder="+56 9 1234 5678">
                            </div>
                            <button type="submit" class="btn btn-primary w-100 py-2">Registrar Cuenta</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <footer class="bg-dark text-white text-center py-3 mt-auto">
        <div class="container">
            <p class="mb-0 small">&copy; 2026 Librería Virtual - Proyecto Final Programación Web II</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>