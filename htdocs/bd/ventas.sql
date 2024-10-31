-- Crear la base de datos
CREATE SCHEMA `ventas` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE ventas;

-- Tabla de usuarios
CREATE TABLE usuarios (
    id_usuario INT AUTO_INCREMENT,
    nombre VARCHAR(50) NOT NULL,
    apellido VARCHAR(50) NOT NULL,
    email VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    fechaCaptura DATETIME DEFAULT CURRENT_TIMESTAMP,
    estado TINYINT(1) DEFAULT 1,
    PRIMARY KEY (id_usuario)
) ENGINE=InnoDB;

-- Tabla de categorías
CREATE TABLE categorias (
    id_categoria INT AUTO_INCREMENT,
    id_usuario INT NOT NULL,
    nombreCategoria VARCHAR(150) NOT NULL,
    descripcion TEXT,
    fechaCaptura DATETIME DEFAULT CURRENT_TIMESTAMP,
    estado TINYINT(1) DEFAULT 1,
    PRIMARY KEY (id_categoria),
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE RESTRICT,
    UNIQUE KEY unique_categoria (nombreCategoria)
) ENGINE=InnoDB;

-- Tabla de imágenes
CREATE TABLE imagenes (
    id_imagen INT AUTO_INCREMENT,
    id_categoria INT NOT NULL,
    nombre VARCHAR(255) NOT NULL,
    ruta VARCHAR(255) NOT NULL,
    tipo VARCHAR(50) NOT NULL,
    tamanio INT,
    fechaSubida DATETIME DEFAULT CURRENT_TIMESTAMP,
    estado TINYINT(1) DEFAULT 1,
    PRIMARY KEY (id_imagen),
    FOREIGN KEY (id_categoria) REFERENCES categorias(id_categoria) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- Tabla de artículos
CREATE TABLE articulos (
    id_producto INT AUTO_INCREMENT,
    id_categoria INT NOT NULL,
    id_imagen INT NOT NULL,
    id_usuario INT NOT NULL,
    codigo VARCHAR(50) UNIQUE,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    cantidad INT DEFAULT 0,
    precio DECIMAL(10,2) DEFAULT 0.00,
    fechaCaptura DATETIME DEFAULT CURRENT_TIMESTAMP,
    fechaActualizacion DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    estado TINYINT(1) DEFAULT 1,
    PRIMARY KEY (id_producto),
    FOREIGN KEY (id_categoria) REFERENCES categorias(id_categoria) ON DELETE RESTRICT,
    FOREIGN KEY (id_imagen) REFERENCES imagenes(id_imagen) ON DELETE RESTRICT,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- Tabla de clientes
CREATE TABLE clientes (
    id_cliente INT AUTO_INCREMENT,
    id_usuario INT NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    direccion TEXT,
    email VARCHAR(100),
    telefono VARCHAR(20),
    rfc VARCHAR(20),
    fechaCaptura DATETIME DEFAULT CURRENT_TIMESTAMP,
    fechaActualizacion DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    estado TINYINT(1) DEFAULT 1,
    PRIMARY KEY (id_cliente),
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE RESTRICT,
    UNIQUE KEY unique_email (email)
) ENGINE=InnoDB;

-- Tabla de ventas
CREATE TABLE ventas (
    id_venta INT AUTO_INCREMENT,
    id_cliente INT,
    id_producto INT,
    id_usuario INT,
    cantidad INT NOT NULL DEFAULT 1,
    precio_unitario DECIMAL(10,2) NOT NULL,
    descuento DECIMAL(10,2) DEFAULT 0.00,
    total DECIMAL(10,2) NOT NULL,
    metodo_pago VARCHAR(50) DEFAULT 'efectivo',
    estado_pago VARCHAR(50) DEFAULT 'pendiente',
    fechaVenta DATETIME DEFAULT CURRENT_TIMESTAMP,
    estado TINYINT(1) DEFAULT 1,
    PRIMARY KEY (id_venta),
    FOREIGN KEY (id_cliente) REFERENCES clientes(id_cliente) ON DELETE RESTRICT,
    FOREIGN KEY (id_producto) REFERENCES articulos(id_producto) ON DELETE RESTRICT,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- Tabla de historial de precios
CREATE TABLE historial_precios (
    id_historial INT AUTO_INCREMENT,
    id_producto INT NOT NULL,
    precio_anterior DECIMAL(10,2) NOT NULL,
    precio_nuevo DECIMAL(10,2) NOT NULL,
    fecha_cambio DATETIME DEFAULT CURRENT_TIMESTAMP,
    id_usuario INT NOT NULL,
    PRIMARY KEY (id_historial),
    FOREIGN KEY (id_producto) REFERENCES articulos(id_producto) ON DELETE CASCADE,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- Tabla de log de actividades
CREATE TABLE log_actividades (
    id_log INT AUTO_INCREMENT,
    id_usuario INT NOT NULL,
    accion VARCHAR(50) NOT NULL,
    tabla_afectada VARCHAR(50) NOT NULL,
    id_registro INT NOT NULL,
    descripcion TEXT,
    fecha_hora DATETIME DEFAULT CURRENT_TIMESTAMP,
    ip_address VARCHAR(45),
    PRIMARY KEY (id_log),
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- Índices para optimización de búsquedas
CREATE INDEX idx_articulos_nombre ON articulos(nombre);
CREATE INDEX idx_articulos_codigo ON articulos(codigo);
CREATE INDEX idx_clientes_nombre ON clientes(nombre, apellido);
CREATE INDEX idx_ventas_fecha ON ventas(fechaVenta);

-- Triggers para mantener la integridad y el historial

-- Trigger para historial de precios
DELIMITER //
CREATE TRIGGER before_articulo_update 
BEFORE UPDATE ON articulos
FOR EACH ROW
BEGIN
    IF NEW.precio != OLD.precio THEN
        INSERT INTO historial_precios(id_producto, precio_anterior, precio_nuevo, id_usuario)
        VALUES(OLD.id_producto, OLD.precio, NEW.precio, NEW.id_usuario);
    END IF;
END//
DELIMITER ;

-- Trigger para log de eliminaciones
DELIMITER //
CREATE TRIGGER before_articulo_delete
BEFORE DELETE ON articulos
FOR EACH ROW
BEGIN
    INSERT INTO log_actividades(id_usuario, accion, tabla_afectada, id_registro, descripcion)
    VALUES(OLD.id_usuario, 'DELETE', 'articulos', OLD.id_producto, 
           CONCAT('Eliminación de artículo: ', OLD.nombre));
END//
DELIMITER ;

-- Procedimientos almacenados útiles

-- Procedimiento para obtener ventas por período
DELIMITER //
CREATE PROCEDURE sp_ventas_por_periodo(
    IN fecha_inicio DATETIME,
    IN fecha_fin DATETIME
)
BEGIN
    SELECT 
        v.id_venta,
        CONCAT(c.nombre, ' ', c.apellido) as cliente,
        a.nombre as articulo,
        v.cantidad,
        v.precio_unitario,
        v.total,
        v.fechaVenta
    FROM ventas v
    LEFT JOIN clientes c ON v.id_cliente = c.id_cliente
    JOIN articulos a ON v.id_producto = a.id_producto
    WHERE v.fechaVenta BETWEEN fecha_inicio AND fecha_fin
    ORDER BY v.fechaVenta DESC;
END//
DELIMITER ;

-- Procedimiento para actualizar stock
DELIMITER //
CREATE PROCEDURE sp_actualizar_stock(
    IN p_id_producto INT,
    IN p_cantidad INT
)
BEGIN
    UPDATE articulos 
    SET cantidad = cantidad + p_cantidad
    WHERE id_producto = p_id_producto;
END//
DELIMITER ;

-- Vistas útiles

-- Vista de inventario actual
CREATE VIEW v_inventario_actual AS
SELECT 
    a.id_producto,
    a.codigo,
    a.nombre AS nombre_articulo,
    c.nombreCategoria AS categoria,
    a.cantidad,
    a.precio,
    i.ruta AS imagen_ruta
FROM 
    articulos a
JOIN categorias c ON a.id_categoria = c.id_categoria
LEFT JOIN imagenes i ON a.id_imagen = i.id_imagen
WHERE 
    a.estado = 1;

-- Vista de ventas detalladas
CREATE VIEW v_ventas_detalladas AS
SELECT 
    v.id_venta,
    v.fechaVenta,
    CONCAT(c.nombre, ' ', c.apellido) AS cliente,
    a.nombre AS articulo,
    v.cantidad,
    v.precio_unitario,
    v.descuento,
    v.total,
    v.metodo_pago,
    v.estado_pago,
    u.nombre AS vendedor
FROM 
    ventas v
LEFT JOIN clientes c ON v.id_cliente = c.id_cliente
JOIN articulos a ON v.id_producto = a.id_producto
JOIN usuarios u ON v.id_usuario = u.id_usuario;

-- Vista de top productos vendidos
CREATE VIEW v_top_productos AS
SELECT 
    a.id_producto,
    a.nombre,
    SUM(v.cantidad) AS total_vendido,
    SUM(v.total) AS ingresos_totales
FROM 
    ventas v
JOIN articulos a ON v.id_producto = a.id_producto
GROUP BY 
    a.id_producto, a.nombre
ORDER BY 
    total_vendido DESC;

-- Funciones útiles

-- Función para calcular el total de ventas de un día específico
DELIMITER //
CREATE FUNCTION f_total_ventas_dia(fecha_consulta DATE) 
RETURNS DECIMAL(10,2)
DETERMINISTIC
BEGIN
    DECLARE total DECIMAL(10,2);
    
    SELECT COALESCE(SUM(total), 0) INTO total
    FROM ventas
    WHERE DATE(fechaVenta) = fecha_consulta;
    
    RETURN total;
END //
DELIMITER ;

-- Función para obtener el stock actual de un producto
DELIMITER //
CREATE FUNCTION f_stock_actual(id_prod INT) 
RETURNS INT
DETERMINISTIC
BEGIN
    DECLARE stock INT;
    
    SELECT cantidad INTO stock
    FROM articulos
    WHERE id_producto = id_prod;
    
    RETURN COALESCE(stock, 0);
END //
DELIMITER ;

-- Eventos programados

-- Evento para limpiar logs antiguos (más de 6 meses)
DELIMITER //
CREATE EVENT e_limpiar_logs_antiguos
ON SCHEDULE EVERY 1 MONTH
DO
BEGIN
    DELETE FROM log_actividades
    WHERE fecha_hora < DATE_SUB(NOW(), INTERVAL 6 MONTH);
END //
DELIMITER ;

-- Inserciones iniciales (datos de ejemplo)

-- Insertar usuario administrador
INSERT INTO usuarios (nombre, apellido, email, password) 
VALUES ('Admin', 'Sistema', 'admin@sistema.com', SHA2('admin123', 256));

-- Insertar categorías de ejemplo
INSERT INTO categorias (id_usuario, nombreCategoria) VALUES 
(1, 'Electrónicos'),
(1, 'Ropa'),
(1, 'Hogar'),
(1, 'Alimentos');

-- Configuración de seguridad adicional

-- Crear usuario para la aplicación con permisos limitados
CREATE USER 'app_user'@'localhost' IDENTIFIED BY 'app_password';
GRANT SELECT, INSERT, UPDATE, DELETE ON ventas.* TO 'app_user'@'localhost';
FLUSH PRIVILEGES;

-- Nota: Asegúrate de cambiar 'app_password' por una contraseña segura real

-- Configuración final

-- Asegurar que las tablas usen InnoDB
ALTER TABLE usuarios ENGINE=InnoDB;
ALTER TABLE categorias ENGINE=InnoDB;
ALTER TABLE imagenes ENGINE=InnoDB;
ALTER TABLE articulos ENGINE=InnoDB;
ALTER TABLE clientes ENGINE=InnoDB;
ALTER TABLE ventas ENGINE=InnoDB;
ALTER TABLE historial_precios ENGINE=InnoDB;
ALTER TABLE log_actividades ENGINE=InnoDB;

-- Optimizar todas las tablas
OPTIMIZE TABLE usuarios, categorias, imagenes, articulos, clientes, ventas, historial_precios, log_actividades;