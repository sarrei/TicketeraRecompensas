-- ==========================
-- BASE DE DATOS
-- ==========================
CREATE DATABASE ticketing_mvp_final;
USE ticketing_mvp_final;

-- ==========================
-- ROLES
-- ==========================
CREATE TABLE roles (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(50) NOT NULL UNIQUE
);

INSERT INTO roles (nombre) VALUES ('CLIENTE'), ('AGENTE'), ('ADMIN');

-- ==========================
-- NIVELES DE FIDELIZACION
-- ==========================
CREATE TABLE niveles_fidelizacion (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre ENUM('BRONCE','SILVER','GOLD') UNIQUE,
  min_puntos INT NOT NULL
);

INSERT INTO niveles_fidelizacion (nombre, min_puntos) 
VALUES ('BRONCE', 0), ('SILVER', 500), ('GOLD', 1000);

-- ==========================
-- USUARIOS
-- ==========================
CREATE TABLE usuarios (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(100) NOT NULL,
  correo VARCHAR(150) NOT NULL UNIQUE,
  contrasena_hash VARCHAR(255) NOT NULL,
  rol_id INT NOT NULL,
  nivel_id INT NOT NULL,
  puntos INT NOT NULL DEFAULT 0,
  creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  ultima_conexion TIMESTAMP NULL,
  FOREIGN KEY (rol_id) REFERENCES roles(id),
  FOREIGN KEY (nivel_id) REFERENCES niveles_fidelizacion(id)
);

-- ==========================
-- CINES
-- ==========================
CREATE TABLE cines (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(150) NOT NULL,
  ciudad VARCHAR(100) NOT NULL,
  direccion VARCHAR(255)
);

-- ==========================
-- SALAS
-- ==========================
CREATE TABLE salas (
  id INT AUTO_INCREMENT PRIMARY KEY,
  cine_id INT NOT NULL,
  nombre VARCHAR(50) NOT NULL,
  capacidad INT NOT NULL,
  FOREIGN KEY (cine_id) REFERENCES cines(id)
);

-- ==========================
-- ASIENTOS
-- ==========================
CREATE TABLE asientos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  sala_id INT NOT NULL,
  fila VARCHAR(5) NOT NULL,
  numero INT NOT NULL,
  UNIQUE (sala_id, fila, numero),
  FOREIGN KEY (sala_id) REFERENCES salas(id)
);

-- ==========================
-- PELICULAS
-- ==========================
CREATE TABLE peliculas (
  id INT AUTO_INCREMENT PRIMARY KEY,
  titulo VARCHAR(150) NOT NULL,
  descripcion TEXT,
  duracion INT, -- minutos
  clasificacion VARCHAR(10), -- PG, R, etc.
  creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ==========================
-- FUNCIONES (pelicula + sala + horario)
-- ==========================
CREATE TABLE funciones (
  id INT AUTO_INCREMENT PRIMARY KEY,
  pelicula_id INT NOT NULL,
  sala_id INT NOT NULL,
  fecha_funcion DATETIME NOT NULL,
  precio DECIMAL(10,2) NOT NULL,
  FOREIGN KEY (pelicula_id) REFERENCES peliculas(id),
  FOREIGN KEY (sala_id) REFERENCES salas(id)
);

-- ==========================
-- EVENTOS (no peliculas)
-- ==========================
CREATE TABLE eventos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(150) NOT NULL,
  descripcion TEXT,
  ubicacion VARCHAR(150) NOT NULL,
  fecha_evento DATETIME NOT NULL,
  precio DECIMAL(10,2) NOT NULL,
  capacidad_total INT NOT NULL,
  tickets_disponibles INT NOT NULL,
  creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ==========================
-- COMPRAS
-- ==========================
CREATE TABLE compras (
  id INT AUTO_INCREMENT PRIMARY KEY,
  usuario_id INT NOT NULL,
  precio_total DECIMAL(10,2) NOT NULL,
  puntos_usados INT NOT NULL DEFAULT 0,
  estado ENUM('PENDIENTE','PAGADO','CANCELADO') DEFAULT 'PENDIENTE',
  creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);

-- ==========================
-- DETALLE DE COMPRA
-- ==========================
CREATE TABLE compra_detalle (
  id INT AUTO_INCREMENT PRIMARY KEY,
  compra_id INT NOT NULL,
  tipo ENUM('FUNCION','EVENTO') NOT NULL,
  funcion_id INT NULL,
  evento_id INT NULL,
  cantidad INT NOT NULL,
  subtotal DECIMAL(10,2) NOT NULL,
  FOREIGN KEY (compra_id) REFERENCES compras(id),
  FOREIGN KEY (funcion_id) REFERENCES funciones(id),
  FOREIGN KEY (evento_id) REFERENCES eventos(id)
);

-- ==========================
-- RESERVA DE ASIENTOS (para funciones)
-- ==========================
CREATE TABLE reservas_asientos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  compra_detalle_id INT NOT NULL,
  asiento_id INT NOT NULL,
  UNIQUE (asiento_id, compra_detalle_id),
  FOREIGN KEY (compra_detalle_id) REFERENCES compra_detalle(id),
  FOREIGN KEY (asiento_id) REFERENCES asientos(id)
);

-- ==========================
-- TICKETS
-- ==========================
CREATE TABLE tickets (
  id INT AUTO_INCREMENT PRIMARY KEY,
  compra_detalle_id INT NOT NULL,
  codigo_qr VARCHAR(255) NOT NULL UNIQUE,
  validado BOOLEAN DEFAULT FALSE,
  validado_por INT NULL,
  validado_en TIMESTAMP NULL,
  FOREIGN KEY (compra_detalle_id) REFERENCES compra_detalle(id),
  FOREIGN KEY (validado_por) REFERENCES usuarios(id)
);

-- ==========================
-- PAGOS
-- ==========================
CREATE TABLE pagos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  compra_id INT NOT NULL,
  metodo ENUM('PAYPAL','STRIPE') NOT NULL,
  referencia VARCHAR(255) NOT NULL,
  monto DECIMAL(10,2) NOT NULL,
  estado ENUM('APROBADO','FALLIDO','PENDIENTE') DEFAULT 'PENDIENTE',
  creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (compra_id) REFERENCES compras(id)
);

-- ==========================
-- MOVIMIENTOS DE PUNTOS
-- ==========================
CREATE TABLE puntos_movimientos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  usuario_id INT NOT NULL,
  compra_id INT NULL,
  tipo ENUM('ACUMULACION','CANJE') NOT NULL,
  puntos INT NOT NULL,
  creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
  FOREIGN KEY (compra_id) REFERENCES compras(id)
);
