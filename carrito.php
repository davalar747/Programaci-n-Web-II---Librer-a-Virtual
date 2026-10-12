<?php
session_start();
require_once 'config/db.php';

// Control de acceso: si no ha iniciado sesión, redirigir a index.html
if (!isset($_SESSION['user_id'])) {
    header("Location: index.html");
    exit;
}

$user_id = $_SESSION['user_id'];
$user_nombre = $_SESSION['user_nombre'];

$mensaje = '';
$tipo_alerta = '';

// 1. PROCESAR ACCIONES DEL CARRITO (AGREGAR, ACTUALIZAR, ELIMINAR)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';

    // AGREGAR LIBRO AL CARRITO
    if ($accion === 'agregar') {
        $id_libro = filter_input(INPUT_POST, 'id_libro', FILTER_VALIDATE_INT);
        $cantidad = filter_input(INPUT_POST, 'cantidad', FILTER_VALIDATE_INT);

        if ($id_libro && $cantidad && $cantidad > 0) {
            // Obtener precio y stock del libro
            $stmtLibro = $pdo->prepare("SELECT precio, cantidad_inventario FROM LIBROS WHERE id = ?");
            $stmtLibro->execute([$id_libro]);
            $libro = $stmtLibro->fetch();

            if ($libro) {
                if ($cantidad <= $libro['cantidad_inventario']) {
                    $monto_total = $libro['precio'] * $cantidad;

                    // Insertar o actualizar si ya está en el carrito
                    $stmtCheck = $pdo->prepare("SELECT id, cantidad FROM CARRITO WHERE id_usuario = ? AND id_libro = ?");
                    $stmtCheck->execute([$user_id, $id_libro]);
                    $enCarrito = $stmtCheck->fetch();

                    if ($enCarrito) {
                        $nuevaCantidad = $enCarrito['cantidad'] + $cantidad;
                        $nuevoMonto = $nuevaCantidad * $libro['precio'];
                        $stmtUpdate = $pdo->prepare("UPDATE CARRITO SET cantidad = ?, monto_total = ? WHERE id = ?");
                        $stmtUpdate->execute([$nuevaCantidad, $nuevoMonto, $enCarrito['id']]);
                    } else {
                        $stmtInsert = $pdo->prepare("INSERT INTO CARRITO (id_usuario, id_libro, cantidad, monto_total) VALUES (?, ?, ?, ?)");
                        $stmtInsert->execute([$user_id, $id_libro, $cantidad, $monto_total]);
                    }
                    $mensaje = 'Libro añadido al carrito exitosamente.';
                    $tipo_alerta = 'success';
                } else {
                    $mensaje = 'La cantidad solicitada supera el stock disponible en inventario.';
                    $tipo_alerta = 'warning';
                }
            }
        }
    }

    // MODIFICAR CANTIDAD EN EL CARRITO
    if ($accion === 'modificar') {
        $id_carrito = filter_input(INPUT_POST, 'id_carrito', FILTER_VALIDATE_INT);
        $nueva_cantidad = filter_input(INPUT_POST, 'cantidad', FILTER_VALIDATE_INT);

        if ($id_carrito && $nueva_cantidad && $nueva_cantidad > 0) {
            // Consultar libro y precio
            $stmtItem = $pdo->prepare("
                SELECT c.id_libro, l.precio, l.cantidad_inventario 
                FROM CARRITO c 
                JOIN LIBROS l ON c.id_libro = l.id 
                WHERE c.id = ? AND c.id_usuario = ?
            ");
            $stmtItem->execute([$id_carrito, $user_id]);
            $item = $stmtItem->fetch();

            if ($item && $nueva_cantidad <= $item['cantidad_inventario']) {
                $nuevo_monto = $nueva_cantidad * $item['precio'];
                $stmtUpdate = $pdo->prepare("UPDATE CARRITO SET cantidad = ?, monto_total = ? WHERE id = ?");
                $stmtUpdate->execute([$nueva_cantidad, $nuevo_monto, $id_carrito]);
                $mensaje = 'Cantidad actualizada correctamente.';
                $tipo_alerta = 'info';
            } else {
                $mensaje = 'Stock insuficiente para la cantidad solicitada.';
                $tipo_alerta = 'warning';
            }
        }
    }

    // ELIMINAR REGISTRO DEL CARRITO
    if ($accion === 'eliminar') {
        $id_carrito = filter_input(INPUT_POST, 'id_carrito', FILTER_VALIDATE_INT);
        if ($id_carrito) {
            $stmtDelete = $pdo->prepare("DELETE FROM CARRITO WHERE id = ? AND id_usuario = ?");
            $stmtDelete->execute([$id_carrito, $user_id]);
            $mensaje = 'Producto eliminado del carrito.';
            $tipo_alerta = 'danger';
        }
    }
}

// 2. CONSULTAS PARA LA VISTA
// Consultar libros disponibles para el desplegable
$librosDisponibles = $pdo->query("SELECT id, titulo, autor, precio, cantidad_inventario FROM LIBROS WHERE cantidad_inventario > 0")->fetchAll();

// Consultar artículos del carrito del usuario activo
$stmtCarrito = $pdo->prepare("
    SELECT c.id AS id_carrito, l.titulo, l.autor, l.precio, c.cantidad, c.monto_total 
    FROM CARRITO c 
    JOIN LIBROS l ON c.id_libro = l.id 
    WHERE c.id_usuario = ?
");
$stmtCarrito->execute([$user_id]);
$itemsCarrito = $stmtCarrito->fetchAll();

// Total general acumulado
$totalPagar = array_sum(array_column($itemsCarrito, 'monto_total'));
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carrito de Compras - Librería Virtual</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/styles.css">
</head>
<body class="d-flex flex-column min-vh-100 bg-light">

    <!-- Navegación con datos del usuario -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand fw-semibold" href="index.html">Librería Virtual</a>
            <div class="navbar-nav me-auto">
                <a class="nav-link" href="index.html">Inicio</a>
                <a class="nav-link" href="registro_usuario.php">Registro Cliente</a>
                <a class="nav-link" href="registro_libro.php">Nuevo Libro</a>
                <a class="nav-link active" href="carrito.php">Carrito</a>
            </div>
            <div class="d-flex align-items-center">
                <span class="navbar-text text-white me-3">
                    Hola, <strong><?= htmlspecialchars($user_nombre); ?></strong>
                </span>
                <a href="logout.php" class="btn btn-outline-danger btn-sm">Cerrar Sesión</a>
            </div>
        </div>
    </nav>

    <main class="container my-5 flex-grow-1">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Gestión de Pedidos y Carrito de Compras</h2>
        </div>

        <?php if (!empty($mensaje)): ?>
            <div class="alert alert-<?= $tipo_alerta; ?> alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($mensaje); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row g-4">
            <!-- Formulario de Selección y Agregado -->
            <div class="col-lg-4">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-primary text-white py-3">
                        <h5 class="card-title mb-0">Agregar Libro al Pedido</h5>
                    </div>
                    <div class="card-body">
                        <form action="carrito.php" method="POST" id="formAgregarLibro">
                            <input type="hidden" name="accion" value="agregar">
                            
                            <div class="mb-3">
                                <label for="id_libro" class="form-label">Seleccionar Libro</label>
                                <select class="form-select" id="id_libro" name="id_libro" required>
                                    <option value="" disabled selected>Elija un libro...</option>
                                    <?php foreach ($librosDisponibles as $l): ?>
                                        <option value="<?= $l['id']; ?>">
                                            <?= htmlspecialchars($l['titulo']); ?> - $<?= number_format($l['precio'], 0, ',', '.'); ?> (Stock: <?= $l['cantidad_inventario']; ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="cantidad" class="form-label">Cantidad</label>
                                <input type="number" class="form-control" id="cantidad" name="cantidad" min="1" value="1" required>
                            </div>

                            <button type="submit" class="btn btn-primary w-100 py-2">Agregar al Carrito</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Tabla Detalle del Carrito -->
            <div class="col-lg-8">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white py-3 border-bottom">
                        <h5 class="card-title mb-0 text-secondary">Artículos en el Carrito</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Título</th>
                                        <th>Precio Unit.</th>
                                        <th style="width: 170px;">Cantidad</th>
                                        <th>Subtotal</th>
                                        <th class="text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($itemsCarrito)): ?>
                                        <tr>
                                            <td colspan="5" class="text-center py-4 text-muted">
                                                No hay productos en tu carrito. ¡Agrega algunos desde el formulario lateral!
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($itemsCarrito as $item): ?>
                                            <tr>
                                                <td>
                                                    <strong><?= htmlspecialchars($item['titulo']); ?></strong><br>
                                                    <small class="text-muted"><?= htmlspecialchars($item['autor']); ?></small>
                                                </td>
                                                <td>$<?= number_format($item['precio'], 0, ',', '.'); ?></td>
                                                <td>
                                                    <!-- Formulario para Modificar Cantidad -->
                                                    <form action="carrito.php" method="POST" class="d-flex align-items-center gap-2">
                                                        <input type="hidden" name="accion" value="modificar">
                                                        <input type="hidden" name="id_carrito" value="<?= $item['id_carrito']; ?>">
                                                        <input type="number" name="cantidad" class="form-control form-control-sm" value="<?= $item['cantidad']; ?>" min="1" style="width: 70px;">
                                                        <button type="submit" class="btn btn-sm btn-outline-secondary" title="Actualizar">✓</button>
                                                    </form>
                                                </td>
                                                <td class="fw-bold text-primary">$<?= number_format($item['monto_total'], 0, ',', '.'); ?></td>
                                                <td class="text-center">
                                                    <!-- Formulario para Eliminar Item -->
                                                    <form action="carrito.php" method="POST" onsubmit="return confirm('¿Desea eliminar este libro del carrito?');">
                                                        <input type="hidden" name="accion" value="eliminar">
                                                        <input type="hidden" name="id_carrito" value="<?= $item['id_carrito']; ?>">
                                                        <button type="submit" class="btn btn-sm btn-outline-danger">Eliminar</button>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                                <?php if (!empty($itemsCarrito)): ?>
                                    <tfoot class="table-light">
                                        <tr>
                                            <th colspan="3" class="text-end fs-5">Monto Total a Pagar:</th>
                                            <th colspan="2" class="fs-5 text-success fw-bold">$<?= number_format($totalPagar, 0, ',', '.'); ?></th>
                                        </tr>
                                    </tfoot>
                                <?php endif; ?>
                            </table>
                        </div>
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