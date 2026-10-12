<?php
require_once 'config/db.php';

$mensaje = '';
$tipo_alerta = '';

// Procesar el envío del formulario vía POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo    = trim($_POST['titulo'] ?? '');
    $autor     = trim($_POST['autor'] ?? '');
    $precio    = filter_input(INPUT_POST, 'precio', FILTER_VALIDATE_FLOAT);
    $cantidad  = filter_input(INPUT_POST, 'cantidad_inventario', FILTER_VALIDATE_INT);

    // Validación de datos
    if (empty($titulo) || empty($autor) || $precio === false || $cantidad === false) {
        $mensaje = 'Por favor complete todos los campos con valores válidos.';
        $tipo_alerta = 'warning';
    } elseif ($precio <= 0 || $cantidad < 0) {
        $mensaje = 'El precio debe ser mayor a 0 y el inventario no puede ser negativo.';
        $tipo_alerta = 'warning';
    } else {
        try {
            // Inserción en la tabla LIBROS
            $sql = "INSERT INTO LIBROS (titulo, autor, precio, cantidad_inventario) VALUES (?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$titulo, $autor, $precio, $cantidad]);

            $mensaje = '¡Libro registrado exitosamente en el catálogo!';
            $tipo_alerta = 'success';
        } catch (PDOException $e) {
            $mensaje = 'Error al registrar el libro: ' . $e->getMessage();
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
    <title>Registro de Libros - Librería Virtual</title>
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
                <a class="nav-link" href="registro_usuario.php">Registro Cliente</a>
                <a class="nav-link active" href="registro_libro.php">Nuevo Libro</a>
                <a class="nav-link" href="carrito.php">Carrito</a>
            </div>
        </div>
    </nav>

    <!-- Formulario -->
    <main class="container my-5 flex-grow-1">
        <div class="row justify-content-center">
            <div class="col-md-7 col-lg-6">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-primary text-white py-3">
                        <h4 class="mb-0 text-center">Registro de Nuevo Libro</h4>
                    </div>
                    <div class="card-body p-4">

                        <?php if (!empty($mensaje)): ?>
                            <div class="alert alert-<?= $tipo_alerta; ?> alert-dismissible fade show" role="alert">
                                <?= htmlspecialchars($mensaje); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <form action="registro_libro.php" method="POST">
                            <div class="mb-3">
                                <label for="titulo" class="form-label">Título del Libro</label>
                                <input type="text" class="form-control" id="titulo" name="titulo" required placeholder="Ej. Rayuela">
                            </div>
                            <div class="mb-3">
                                <label for="autor" class="form-label">Autor</label>
                                <input type="text" class="form-control" id="autor" name="autor" required placeholder="Ej. Julio Cortázar">
                            </div>
                            <div class="mb-3">
                                <label for="precio" class="form-label">Precio ($)</label>
                                <input type="number" step="0.01" class="form-control" id="precio" name="precio" required placeholder="Ej. 14990">
                            </div>
                            <div class="mb-3">
                                <label for="cantidad_inventario" class="form-label">Cantidad en Inventario</label>
                                <input type="number" class="form-control" id="cantidad_inventario" name="cantidad_inventario" required placeholder="Ej. 20">
                            </div>
                            <button type="submit" class="btn btn-primary w-100 py-2">Registrar Libro</button>
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