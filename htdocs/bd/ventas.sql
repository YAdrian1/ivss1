CREATE SCHEMA `ventas` DEFAULT CHARACTER SET utf8mb4 ;

use ventas;

create table usuarios(
				id_usuario int auto_increment,
				nombre varchar(50),
				apellido varchar(50),
				email varchar(50),
				password text(50),
				fechaCaptura date,
				primary key(id_usuario)
					);




create table imagenes(
				id_imagen int auto_increment,
				id_categoria int not null,
				nombre varchar(500),
				ruta varchar(500),
				fechaSubida date,
				primary key(id_imagen)
					);
create table articulos(
				id_producto int auto_increment,
				id_categoria int not null,
				id_imagen int not null,
				id_usuario int not null,
				nombre varchar(50),
				descripcion varchar(500),
				cantidad int,
				precio float,
				fechaCaptura date,
				primary key(id_producto)

						);


CREATE TABLE imagenes_subidas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    nombre VARCHAR(255) NOT NULL,
    imagen_data MEDIUMBLOB,
    tipo_imagen VARCHAR(50),
    fecha_subida TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario)
);