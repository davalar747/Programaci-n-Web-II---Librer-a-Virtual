<?php
session_start();
require_once 'config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        header("Location: index.html?error=campos_vacios");
        exit;
    }

    try {
        // Consultar el usuario por email
        $stmt = $pdo->prepare("SELECT id, nombre, email, contraseña FROM USUARIOS WHERE email = ?");
        $stmt->execute([$email]);
        $usuario = $stmt->fetch();

        // Verificar existencia y comparar hash de contraseña
        if ($usuario && password_verify($password, $usuario['contraseña'])) {
            // Guardar variables de sesión del usuario autenticado
            $_SESSION['user_id']     = $usuario['id'];
            $_SESSION['user_nombre'] = $usuario['nombre'];
            $_SESSION['user_email']  = $usuario['email'];

            // Redirigir al carrito de compras
            header("Location: carrito.php");
            exit;
        } else {
            // Credenciales incorrectas
            header("Location: index.html?error=credenciales_invalidas");
            exit;
        }
    } catch (PDOException $e) {
        die("Error en el inicio de sesión: " . $e->getMessage());
    }
} else {
    header("Location: index.html");
    exit;
}