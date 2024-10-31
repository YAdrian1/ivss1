CREATE SCHEMA `ventas` DEFAULT CHARACTER SET utf8mb4;

USE ventas;

CREATE TABLE usuarios (
    id_usuario INT AUTO_INCREMENT,
    nombre VARCHAR(50),
    apellido VARCHAR(50),
    email VARCHAR(50),
    password TEXT,
    fechaCaptura DATE,
    PRIMARY KEY (id_usuario)
);

CREATE TABLE categorias (
    id_categoria INT AUTO_INCREMENT,
    id_usuario INT NOT NULL,
    nombreCategoria VARCHAR(150),
    fechaCaptura DATE,
    PRIMARY KEY (id_categoria),
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE CASCADE
);

CREATE TABLE imagenes (
    id_imagen INT AUTO_INCREMENT,
    id_categoria INT NOT NULL,
    nombre VARCHAR(500),
    ruta VARCHAR(500),
    fechaSubida DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_imagen),
    FOREIGN KEY (id_categoria) REFERENCES categorias(id_categoria) ON DELETE CASCADE
);

CREATE TABLE articulos (
    id_producto INT AUTO_INCREMENT,
    id_categoria INT NOT NULL,
    id_imagen INT NOT NULL,
    id_usuario INT NOT NULL,
    nombre VARCHAR(50),
    fechaCaptura DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_producto),
    FOREIGN KEY (id_categoria) REFERENCES categorias(id_categoria) ON DELETE CASCADE,
    FOREIGN KEY (id_imagen) REFERENCES imagenes(id_imagen) ON DELETE CASCADE,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE CASCADE
);

CREATE TABLE clientes (
    id_cliente INT AUTO_INCREMENT,
    id_usuario INT NOT NULL,
    nombre VARCHAR(200),
    apellido VARCHAR(200),
    direccion VARCHAR(200),
    email VARCHAR(200),
    telefono VARCHAR(200),
    rfc VARCHAR(200),
    PRIMARY KEY (id_cliente),
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE CASCADE
);

CREATE TABLE ventas (
    id_venta INT AUTO_INCREMENT,
    id_cliente INT,
    id_producto INT,
    id_usuario INT,
    precio FLOAT,
    fechaCompra DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_venta),
    FOREIGN KEY (id_cliente) REFERENCES clientes(id_cliente) ON DELETE CASCADE,
    FOREIGN KEY (id_producto) REFERENCES articulos(id_producto) ON DELETE CASCADE,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE CASCADE
);