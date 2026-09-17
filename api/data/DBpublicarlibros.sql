DROP DATABASE IF EXISTS dbpublicaciones;
CREATE DATABASE dbpublicaciones;
USE dbpublicaciones;

-- tabla cliente
CREATE TABLE CLIENTE(
    id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    CI VARCHAR(20) NOT NULL,
    nombre VARCHAR(50) NOT NULL,
    apellidos VARCHAR(50) NOT NULL,
    direccion VARCHAR(250),
    telefono VARCHAR(15) NOT NULL
) ENGINE=InnoDB;

-- tabla empleado
CREATE TABLE empleado(
    id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    CI VARCHAR(20) NOT NULL,
    nombre VARCHAR(50) NOT NULL,
    apellidos VARCHAR(50) NOT NULL
) ENGINE=InnoDB;

-- tabla pedido
CREATE TABLE pedido(
    id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    cod_cliente INT NOT NULL,
    fecha_compra DATETIME NOT NULL,
    cantidad INT NOT NULL,
    cod_empleado INT NOT NULL,

    FOREIGN KEY(cod_cliente) REFERENCES CLIENTE(id),
    FOREIGN KEY(cod_empleado) REFERENCES empleado(id)

) ENGINE=InnoDB;

-- tabla producto
CREATE TABLE producto(
    id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    cod_barras VARCHAR(100) NOT NULL,
    descripcion VARCHAR(100) NOT NULL,
    stock INT NOT NULL CHECK(stock >= 0),
    precio_unitario DECIMAL(10,2) NOT NULL
) ENGINE=InnoDB;

-- tabla detalle pedido
CREATE TABLE pedido_producto(
    id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    cod_producto INT NOT NULL,
    cod_pedido INT NOT NULL,
    cantidad INT NOT NULL,
    precio_unitario DECIMAL(10,2) NOT NULL,
    descuento DECIMAL(10,2) DEFAULT 0.0,

    FOREIGN KEY(cod_producto) REFERENCES producto(id),
    FOREIGN KEY(cod_pedido) REFERENCES pedido(id)

) ENGINE=InnoDB;

-- tabla relacional empleado pedido
CREATE TABLE empleado_pedido(
    cod_pedido INT NOT NULL,
    cod_empleado INT NOT NULL,
    fecha DATE NOT NULL DEFAULT (CURRENT_DATE),

    PRIMARY KEY(cod_pedido, cod_empleado),

    FOREIGN KEY(cod_pedido) REFERENCES pedido(id),
    FOREIGN KEY(cod_empleado) REFERENCES empleado(id)

) ENGINE=InnoDB;
