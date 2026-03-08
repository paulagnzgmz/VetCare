-- =============================================
-- veterinaria.sql
-- Base de datos completa de la clinica veterinaria
-- CON BORRADO LÓGICO (columna activo en citas, clientes, pacientes)
--
-- CAMBIO: La tabla consultas se ha eliminado.
-- Los campos diagnostico, tratamiento y peso
-- se han añadido directamente a la tabla citas.
-- El veterinario los rellena al completar la cita.
-- =============================================

DROP DATABASE IF EXISTS veterinaria;
CREATE DATABASE IF NOT EXISTS veterinaria;
USE veterinaria;

-- =============================================
-- TABLA: usuarios
-- =============================================
CREATE TABLE usuarios (
    id_usuario      INT AUTO_INCREMENT PRIMARY KEY,
    nombre_completo VARCHAR(100) NOT NULL,
    email           VARCHAR(100) NOT NULL UNIQUE,
    password        VARCHAR(32)  NOT NULL,
    rol             ENUM('admin', 'veterinario', 'recepcionista', 'cliente') NOT NULL
);

INSERT INTO usuarios (id_usuario, nombre_completo, email, password, rol) VALUES
(1, 'Admin',           'admin@admin.com',    md5('admin'), 'admin'),
(2, 'Luis Recepcion',  'luis@clinica.com',   md5('1234'),  'recepcionista'),
(3, 'Dra. Ana Garcia', 'ana@clinica.com',    md5('1234'),  'veterinario'),
(4, 'Dr. Carlos Ruiz', 'carlos@clinica.com', md5('1234'),  'veterinario'),
(5, 'Maria Lopez',     'maria@email.com',    md5('1234'),  'cliente'),
(6, 'Pedro Martinez',  'pedro@email.com',    md5('1234'),  'cliente'),
(7, 'Lucia Fernandez', 'lucia@email.com',    md5('1234'),  'cliente'),
(8, 'Jorge Blanco',    'jorge@email.com',    md5('1234'),  'cliente'),
(9, 'Carmen Vega',     'carmen@email.com',   md5('1234'),  'cliente'),
(10, 'Roberto Salas',  'roberto@email.com',  md5('1234'),  'cliente'),
(11, 'Elena Torres',   'elena@email.com',    md5('1234'),  'cliente'),
(12, 'Andres Mora',    'andres@email.com',   md5('1234'),  'cliente'),
(13, 'Laura Gomez',    'laura@email.com',    md5('1234'),  'cliente'),
(14, 'Javier Ruiz',    'javier@email.com',   md5('1234'),  'cliente'),
(15, 'Sofia Navarro',  'sofia@email.com',    md5('1234'),  'cliente'),
(16, 'Daniel Romero',  'daniel@email.com',   md5('1234'),  'cliente'),
(17, 'Marta Diaz',     'marta@email.com',    md5('1234'),  'cliente'),
(18, 'Alejandro Gil',  'alejandro@email.com', md5('1234'), 'cliente'),
(19, 'Natalia Sanchez', 'natalia@email.com',  md5('1234'), 'cliente'),
(20, 'Diego Martin',   'diego@email.com',    md5('1234'),  'cliente'),
(21, 'Sara Muñoz',     'sara@email.com',     md5('1234'),  'cliente'),
(22, 'Hector Alonso',  'hector@email.com',   md5('1234'),  'cliente'),
(23, 'Alba Dominguez', 'alba@email.com',     md5('1234'),  'cliente'),
(24, 'David Hernandez', 'david@email.com',    md5('1234'), 'cliente'),
(25, 'Nuria Gutierrez', 'nuria@email.com',    md5('1234'), 'cliente'),
(26, 'Marcos Alvarez', 'marcos@email.com',   md5('1234'),  'cliente'),
(27, 'Irene Vazquez',  'irene@email.com',    md5('1234'),  'cliente');


-- =============================================
-- TABLA: clientes
-- =============================================
CREATE TABLE clientes (
    id_cliente  INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario  INT          NOT NULL,
    nombre      VARCHAR(50)  NOT NULL,
    apellidos   VARCHAR(100) NOT NULL,
    telefono    VARCHAR(15),
    email       VARCHAR(100),
    direccion   VARCHAR(200),
    activo      TINYINT(1)   DEFAULT 1,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario)
);

INSERT INTO clientes (id_cliente, id_usuario, nombre, apellidos, telefono, email, direccion, activo) VALUES
(5, 5, 'Maria', 'Lopez', '600111222', 'maria@email.com', 'Calle Ruamayor 5, Laredo', 1),
(6, 6, 'Pedro', 'Martinez', '600222333', 'pedro@email.com', 'Avenida de Santander 12, Colindres', 1),
(7, 7, 'Lucia', 'Fernandez', '600333444', 'lucia@email.com', 'Barrio Cubillas 3, Ramales', 1),
(8, 8, 'Jorge', 'Blanco', '600444555', 'jorge@email.com', 'Plaza Mayor 1, Ampuero', 1),
(9, 9, 'Carmen', 'Vega', '600555666', 'carmen@email.com', 'Paseo de la Salve 10, Laredo', 1),
(10, 10, 'Roberto', 'Salas', '600666777', 'roberto@email.com', 'Calle de la Mar 8, Colindres', 1),
(11, 11, 'Elena', 'Torres', '600777888', 'elena@email.com', 'Calle de los Hornos 4, Ramales', 1),
(12, 12, 'Andres', 'Mora', '600888999', 'andres@email.com', 'Barrio La Bárcena 7, Ampuero', 1),
(13, 13, 'Laura', 'Gomez', '611100201', 'laura@email.com', 'Calle Marqués de Comillas 12, Laredo', 1),
(14, 14, 'Javier', 'Ruiz', '611100202', 'javier@email.com', 'Avenida de España 45, Laredo', 1),
(15, 15, 'Sofia', 'Navarro', '611100203', 'sofia@email.com', 'Calle del Carmen 8, Ramales', 1),
(16, 16, 'Daniel', 'Romero', '611100204', 'daniel@email.com', 'Avenida de Europa 10, Colindres', 1),
(17, 17, 'Marta', 'Diaz', '611100205', 'marta@email.com', 'Calle La Cruz 15, Ampuero', 1),
(18, 18, 'Alejandro', 'Gil', '611100206', 'alejandro@email.com', 'Calle López Seña 22, Laredo', 1),
(19, 19, 'Natalia', 'Sanchez', '611100207', 'natalia@email.com', 'Paseo Barón de Adzaneta 33, Ramales', 1),
(20, 20, 'Diego', 'Martin', '611100208', 'diego@email.com', 'Calle Santander 5, Colindres', 1),
(21, 21, 'Sara', 'Muñoz', '611100209', 'sara@email.com', 'Avenida de los Tilos 3, Ampuero', 1),
(22, 22, 'Hector', 'Alonso', '611100210', 'hector@email.com', 'Calle Menéndez Pelayo 19, Laredo', 1),
(23, 23, 'Alba', 'Dominguez', '611100211', 'alba@email.com', 'Barrio Guardamino 8, Ramales', 1),
(24, 24, 'David', 'Hernandez', '611100212', 'david@email.com', 'Calle Heliodoro Fernández 14, Colindres', 1),
(25, 25, 'Nuria', 'Gutierrez', '611100213', 'nuria@email.com', 'Calle Mayor 2, Ampuero', 1),
(26, 26, 'Marcos', 'Alvarez', '611100214', 'marcos@email.com', 'Calle Zamanillo 40, Laredo', 1),
(27, 27, 'Irene', 'Vazquez', '611100215', 'irene@email.com', 'Paseo Marítimo 7, Laredo', 1);

-- =============================================
-- TABLA: pacientes
-- =============================================
CREATE TABLE pacientes (
    id_paciente INT AUTO_INCREMENT PRIMARY KEY,
    id_cliente  INT          NOT NULL,
    nombre      VARCHAR(50)  NOT NULL,
    especie     VARCHAR(50)  NOT NULL,
    raza        VARCHAR(100),
    fecha_nac   DATE,
    sexo        ENUM('macho', 'hembra'),
    foto        VARCHAR(255) DEFAULT NULL,
    activo      TINYINT(1)   DEFAULT 1,
    FOREIGN KEY (id_cliente) REFERENCES clientes(id_cliente)
);

INSERT INTO pacientes (id_paciente, id_cliente, nombre, especie, raza, fecha_nac, sexo, foto, activo) VALUES
-- Estos ahora apuntan a Maria (5), Pedro (6), Lucia (7)... que sí existen
(1, 5, 'Toby',  'Perro', 'Labrador',         '2020-03-15', 'macho',  'uploads/mascotas/toby.jpg',  1),
(2, 5, 'Misi',  'Gato',  'Europeo comun',    '2021-06-10', 'hembra', 'uploads/mascotas/misi.jpg',  1),
(3, 6, 'Rocky', 'Perro', 'Pastor Aleman',    '2019-08-22', 'macho',  'uploads/mascotas/rocky.jpg', 1),
(4, 7, 'Luna',  'Gato',  'Persa',            '2022-01-05', 'hembra', 'uploads/mascotas/luna.jpg',  1),
(5, 8, 'Nala',  'Perro', 'Golden Retriever', '2018-11-30', 'hembra', 'uploads/mascotas/nala.jpg',  1),
(6, 9, 'Kira',  'Perro', 'Border Collie',    '2020-07-14', 'hembra', 'uploads/mascotas/kira.jpg',  1),
(7, 10, 'Max',   'Perro', 'Boxer',            '2019-04-20', 'macho',  'uploads/mascotas/max.jpg',   1),
(8, 11, 'Simba', 'Gato',  'Persa',            '2021-09-08', 'macho',  'uploads/mascotas/simba.jpg', 1),
(9, 12, 'Bolt',  'Perro', 'Dalmata',          '2025-10-01', 'macho',  'uploads/mascotas/bolt.jpg',  1),
(10, 13, 'Thor',   'Perro',  'Mastín Español',  '2021-05-12', 'macho',  'uploads/mascotas/thor.jpeg', 1),
(11, 13, 'Mia',    'Gato',   'Siamés',          '2022-08-03', 'hembra', 'uploads/mascotas/mia.jpg', 1),
(12, 14, 'Coco',   'Perro',  'Bulldog Francés', '2020-11-20', 'macho',  'uploads/mascotas/coco.jpg', 1),
-- Aquí llegan las de Sofía (ID 15)
(13, 15, 'Bimba',  'Perro',  'Bichón Maltés',   '2019-02-14', 'hembra', 'uploads/mascotas/bimba.jpg', 1),
(14, 16, 'Leo',    'Gato',   'Maine Coon',      '2023-01-10', 'macho',  'uploads/mascotas/leo.jpg', 1),
(15, 15, 'Lola',   'Perro',  'Teckel',          '2018-09-25', 'hembra', 'uploads/mascotas/lola.jpeg', 1),
(16, 17, 'Zeus',   'Perro',  'Dóberman',        '2021-04-30', 'macho',  'uploads/mascotas/zeus.jpg', 1),
(17, 17, 'Apolo',  'Perro',  'Dóberman',        '2021-04-30', 'macho',  'uploads/mascotas/apolo.jpg', 1),
(18, 15, 'Nieve',  'Gato',   'Angora',          '2020-12-05', 'hembra', 'uploads/mascotas/nieve.jpg', 1),
(19, 19, 'Pepe',   'Ave',    'Loro Yaco',       '2015-06-18', 'macho',  'uploads/mascotas/pepe.jpg', 1),
(20, 20, 'Fiona',  'Perro',  'Carlino',         '2022-03-22', 'hembra', 'uploads/mascotas/fiona.jpg', 1),
(21, 21, 'Garfio', 'Gato',   'Común Europeo',   '2019-10-11', 'macho',  'uploads/mascotas/garfio.jpg', 1),
(22, 22, 'Trufa',  'Perro',  'Caniche',         '2023-07-07', 'hembra', 'uploads/mascotas/trufa.jpg', 1),
(23, 23, 'Oreo',   'Conejo', 'Belier',          '2024-02-15', 'macho',  'uploads/mascotas/oreo.jpg', 1),
(24, 24, 'Dana',   'Perro',  'Pastor Belga',    '2020-05-09', 'hembra', 'uploads/mascotas/dana.jpg', 1),
(25, 25, 'Salem',  'Gato',   'Bombay',          '2021-11-01', 'macho',  'uploads/mascotas/salem.jpg', 1),
(26, 26, 'Zoe',    'Perro',  'Galgo',           '2018-08-14', 'hembra', 'uploads/mascotas/zoe.jpg', 1),
(27, 27, 'Bruno',  'Perro',  'San Bernardo',    '2022-09-20', 'macho',  'uploads/mascotas/bruno.jpg', 1);

-- =============================================
-- TABLA: citas
-- diagnostico, tratamiento y peso se rellenan
-- cuando el veterinario completa la visita
-- =============================================
CREATE TABLE citas (
    id_cita      INT AUTO_INCREMENT PRIMARY KEY,
    id_paciente  INT          NOT NULL,
    id_usuario   INT          NOT NULL,
    fecha_hora   DATETIME     NOT NULL,
    motivo       VARCHAR(200) NOT NULL,
    estado       ENUM('pendiente', 'confirmada', 'completada', 'cancelada') DEFAULT 'pendiente',
    notas        TEXT,
    diagnostico  TEXT         DEFAULT NULL,
    tratamiento  TEXT         DEFAULT NULL,
    peso         DECIMAL(5,2) DEFAULT NULL,
    activo       TINYINT(1)   DEFAULT 1,
    FOREIGN KEY (id_paciente) REFERENCES pacientes(id_paciente),
    FOREIGN KEY (id_usuario)  REFERENCES usuarios(id_usuario)
);

INSERT INTO citas (id_paciente, id_usuario, fecha_hora, motivo, estado, diagnostico, tratamiento, peso, activo) VALUES
-- DICIEMBRE 2025
(1, 3, '2025-12-01 10:00:00', 'Revision anual',         'completada', 'Estado general bueno. Peso correcto.',      NULL,                                  28.0, 1),

-- ENERO 2026 
(3, 3, '2026-01-10 09:30:00', 'Problema digestivo',     'completada', 'Gastroenteritis leve.',                     'Dieta blanda 3 dias. Probioticos.',   34.0, 1),
(10, 4, '2026-01-12 11:00:00', 'Primera consulta',       'completada', 'Cachorro en buen estado. Muy activo.',      'Desparasitación interna.',            3.2,  1),
(11, 3, '2026-01-14 10:30:00', 'Vacunación',             'completada', 'Cachorro sano. Apto para vacuna.',          'Vacuna polivalente.',                 3.8,  1),
(2, 4, '2026-01-15 11:00:00', 'Vacunacion trivalente',  'completada', 'Animal sano. Vacunacion al dia.',           'Vacuna trivalente administrada.',     4.2,  1),
(12, 3, '2026-01-20 16:30:00', 'Chequeo general',        'completada', 'Gato con buen pelaje y constantes ok.',     NULL,                                  4.5,  1),
(5, 3, '2026-01-22 10:00:00', 'Control de peso',        'completada', 'Sobrepeso leve. Dieta recomendada.',        'Reducir racion diaria un 20%.',       32.0, 1),
(26, 3, '2026-01-25 10:00:00', 'Apertura de ficha',      'completada', 'Perro adulto sano.',                        'Se recomienda limpieza dental.',      18.5, 1), 
(13, 4, '2026-01-28 09:00:00', 'Revisión',               'completada', 'Exploración normal.',                       NULL,                                  6.5,  1),

-- FEBRERO 2026
(1, 3, '2026-02-10 10:00:00', 'Revision anual',         'completada', 'Estado excelente. Sin novedades.',          NULL,                                  28.5, 1),
(9, 4, '2026-02-12 16:00:00', 'Primer registro',        'completada', 'Cachorro sano. 4 meses.',                   'Desparasitacion interna completada.', 6.0,  1),
(14, 3, '2026-02-13 11:00:00', 'Vacunación',             'completada', 'Sano. Reacción normal.',                    'Vacuna rabia.',                       24.0, 1),
(15, 4, '2026-02-15 12:30:00', 'Consulta general',       'completada', 'Animal muy tranquilo. Todo correcto.',      NULL,                                  5.2,  1),
(3, 3, '2026-02-17 09:30:00', 'Problema digestivo',     'completada', 'Heces ya formadas. Mejora evidente.',       'Finalizar probióticos.',              34.2, 1),
(4, 4, '2026-02-18 12:00:00', 'Castracion',             'completada', 'Cirugía sin complicaciones.',               'Antibiótico 5 días. Reposo.',         29.0, 1),
(5, 3, '2026-02-19 10:00:00', 'Control peso',           'completada', 'Pérdida de 200g. Buena evolución.',         'Mantener dieta estricta.',            31.8, 1),
(6, 3, '2026-02-20 09:00:00', 'Revision dental',        'completada', 'Sarro leve en premolares. Gingivitis.',     'Limpieza dental recomendada.',        25.0, 1),
(7, 4, '2026-02-21 11:30:00', 'Vacunacion antirrabica', 'completada', 'Examen físico normal. Apto para vacuna.',   'Vacuna Rabia administrada.',          4.5,  1),
(16, 3, '2026-02-22 17:00:00', 'Control de salud',       'completada', 'Ave con plumaje sano y pico correcto.',     NULL,                                  0.4,  1),
(8, 3, '2026-02-24 10:00:00', 'Ecocardiograma',         'completada', 'Soplo grado II/VI. Sin cambios.',           'Revisión en 12 meses.',               22.0, 1),
(27, 4, '2026-02-25 11:00:00', 'Primera revisión',       'completada', 'Perro de gran tamaño. Peso bajo.',          'Suplemento nutricional.',             45.0, 1),
(17, 4, '2026-02-26 10:00:00', 'Revisión inicial',       'completada', 'Perro equilibrado. Ojos algo rojos.',       'Limpieza ocular.',                    8.1,  1),

-- MARZO 2026
(1, 3, '2026-03-02 09:00:00', 'Revisión post-operatoria', 'completada', 'Herida quirúrgica limpia y cerrada.',       'Retirada de puntos.',                 28.5, 1),
(4, 4, '2026-03-03 12:30:00', 'Vacuna Rabia',           'completada', 'Constantes estables.',                      'Vacuna Rabia administrada.',          29.0, 1),
(10, 3, '2026-03-04 10:30:00', 'Primera revisión',       'completada', 'Cachorro sano y activo.',                 'Pauta de desparasitación entregada.', 3.5,  1),
(7, 3, '2026-03-05 10:00:00', 'Corte de uñas y limpieza', 'completada', 'Uñas excesivamente largas.',                'Corte de uñas realizado.',            4.6,  1),
(11, 4, '2026-03-06 16:00:00', 'Revisión orejas',        'completada', 'Otitis leve en oído derecho.',            'Gotas óticas 7 días.',                4.0,  1),
(2, 4, '2026-03-08 17:00:00', 'Consulta digestiva',       'confirmada', NULL, NULL, NULL, 1),
(12, 3, '2026-03-09 09:30:00', 'Control felino',         'confirmada', NULL, NULL, NULL, 1),
(9, 3, '2026-03-10 11:00:00', 'Seguimiento cachorro',     'pendiente',  NULL, NULL, NULL, 1),
(13, 4, '2026-03-12 12:00:00', 'Cojera pata derecha',    'pendiente',  NULL, NULL, NULL, 1),
(5, 4, '2026-03-15 09:30:00', 'Limpieza dental',          'pendiente',  NULL, NULL, NULL, 1),
(14, 3, '2026-03-16 17:30:00', 'Vacuna trivalente',      'confirmada', NULL, NULL, NULL, 1),
(3, 3, '2026-03-18 13:00:00', 'Control de alergia',       'confirmada', NULL, NULL, NULL, 1),
(15, 4, '2026-03-19 10:00:00', 'Revisión anual',         'pendiente',  NULL, NULL, NULL, 1),
(6, 4, '2026-03-22 10:00:00', 'Ecografía de control',     'pendiente',  NULL, NULL, NULL, 1),
(16, 3, '2026-03-23 11:30:00', 'Corte de pico y uñas',   'confirmada', NULL, NULL, NULL, 1),
(8, 3, '2026-03-25 16:30:00', 'Desparasitación',          'pendiente',  NULL, NULL, NULL, 1),
(17, 4, '2026-03-26 18:00:00', 'Problema ocular',        'pendiente',  NULL, NULL, NULL, 1),
(1, 4, '2026-03-30 11:00:00', 'Chequeo geriátrico',       'pendiente',  NULL, NULL, NULL, 1),

-- ABRIL 2026
(18, 3, '2026-04-02 09:30:00', 'Desparasitación',        'confirmada', NULL, NULL, NULL, 1),
(19, 4, '2026-04-06 10:30:00', 'Vacuna Rabia',           'pendiente',  NULL, NULL, NULL, 1),
(20, 3, '2026-04-10 12:00:00', 'Revisión general',       'confirmada', NULL, NULL, NULL, 1),
(21, 4, '2026-04-14 17:00:00', 'Caída de pelo',          'pendiente',  NULL, NULL, NULL, 1),
(22, 3, '2026-04-18 10:00:00', 'Revisión felina',        'confirmada', NULL, NULL, NULL, 1),
(23, 4, '2026-04-22 11:30:00', 'Consulta nutricional',   'pendiente',  NULL, NULL, NULL, 1),
(24, 3, '2026-04-25 16:00:00', 'Chequeo preventivo',     'confirmada', NULL, NULL, NULL, 1),
(25, 4, '2026-04-28 10:30:00', 'Problema bucal',         'pendiente',  NULL, NULL, NULL, 1);

-- =============================================
-- TABLA: licencia
-- Una sola fila que controla si la instalación
-- está activa. El admin la actualiza al cobrar.
-- periodo_gracia: días extra tras vencimiento
-- =============================================
CREATE TABLE licencia (
    id                  INT AUTO_INCREMENT PRIMARY KEY,
    activa              TINYINT(1)   DEFAULT 0,
    plan                VARCHAR(50)  DEFAULT NULL,
    fecha_inicio        DATE         DEFAULT NULL,
    fecha_vencimiento   DATE         DEFAULT NULL,
    paypal_sub_id       VARCHAR(100) DEFAULT NULL,
    periodo_gracia      INT          DEFAULT 3
);

-- Licencia de prueba activa por 30 días
-- Cámbiala cuando el cliente pague de verdad
INSERT INTO licencia (activa, plan, fecha_inicio, fecha_vencimiento, paypal_sub_id, periodo_gracia)
VALUES (1, 'VetCare Pro', '2025-01-01', '2025-01-31', 'SANDBOX_TEST', 3);
 
