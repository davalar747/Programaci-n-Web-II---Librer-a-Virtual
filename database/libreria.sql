-- Creación de la base de datos
CREATE DATABASE IF NOT EXISTS LIBRERIA CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE LIBRERIA;

-- 1. Tabla USUARIOS
CREATE TABLE IF NOT EXISTS USUARIOS (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    contraseña VARCHAR(255) NOT NULL,
    direccion VARCHAR(200) NOT NULL,
    telefono VARCHAR(20) NOT NULL
);

-- 2. Tabla LIBROS
CREATE TABLE IF NOT EXISTS LIBROS (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(150) NOT NULL,
    autor VARCHAR(100) NOT NULL,
    precio DECIMAL(10, 2) NOT NULL,
    cantidad_inventario INT NOT NULL
);

-- 3. Tabla CARRITO
CREATE TABLE IF NOT EXISTS CARRITO (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    id_libro INT NOT NULL,
    cantidad INT NOT NULL,
    monto_total DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (id_usuario) REFERENCES USUARIOS(id) ON DELETE CASCADE,
    FOREIGN KEY (id_libro) REFERENCES LIBROS(id) ON DELETE CASCADE
);

-- Datos de prueba para tener libros en inventario de inmediato
INSERT INTO LIBROS (titulo, autor, precio, cantidad_inventario) VALUES
('Cien años de soledad', 'Gabriel García Márquez', 15990.00, 10),
('El Principito', 'Antoine de Saint-Exupéry', 9990.00, 15),
('Don Quijote de la Mancha', 'Miguel de Cervantes', 18500.00, 8),
('1984', 'George Orwell', 12990.00, 12);