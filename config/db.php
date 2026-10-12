<?php
// Parámetros de conexión a la base de datos local de Laragon
$host     = 'localhost';
$dbname   = 'libreria';
$username = 'root';
$password = ''; // Por defecto en Laragon está vacía
$charset  = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Reportar errores como excepciones
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Devolver arreglos asociativos
    PDO::ATTR_EMULATE_PREPARES   => false,                  // Usar sentencias preparadas nativas y seguras
];

try {
    $pdo = new PDO($dsn, $username, $password, $options);
} catch (PDOException $e) {
    // Si la conexión falla, se captura el error de forma segura
    die("Error al conectar con la base de datos: " . $e->getMessage());
}