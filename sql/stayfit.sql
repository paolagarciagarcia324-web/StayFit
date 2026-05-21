-- StayFit - Esquema relacional MySQL 8
CREATE DATABASE IF NOT EXISTS stayfit CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE stayfit;

CREATE TABLE IF NOT EXISTS rol (
  id_rol BIGINT PRIMARY KEY AUTO_INCREMENT,
  nombre ENUM('Administrador','Coach','Cliente','Cliente-Institucional') NOT NULL UNIQUE,
  descripcion VARCHAR(255),
  permisos TEXT,
  activo BOOLEAN DEFAULT TRUE
);

CREATE TABLE IF NOT EXISTS users (
  id_usuario BIGINT PRIMARY KEY AUTO_INCREMENT,
  nombre VARCHAR(100) NOT NULL,
  apellido VARCHAR(100) NOT NULL,
  correo VARCHAR(150) NOT NULL UNIQUE,
  hash_contrasena VARCHAR(255) NOT NULL,
  estado ENUM('ACTIVO','INACTIVO','SUSPENDIDO') DEFAULT 'ACTIVO',
  origen_registro ENUM('SELF_SERVICE','ADMINISTRATIVO') DEFAULT 'SELF_SERVICE',
  fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  foto_perfil VARCHAR(300),
  telefono VARCHAR(30)
);

CREATE TABLE IF NOT EXISTS users_roles (
  id_usuario BIGINT NOT NULL,
  id_rol BIGINT NOT NULL,
  PRIMARY KEY (id_usuario, id_rol),
  CONSTRAINT fk_users_roles_usuario FOREIGN KEY (id_usuario) REFERENCES users(id_usuario),
  CONSTRAINT fk_users_roles_rol FOREIGN KEY (id_rol) REFERENCES rol(id_rol)
);

CREATE TABLE IF NOT EXISTS coach (
  id_coach BIGINT PRIMARY KEY,
  especialidad VARCHAR(150),
  credencial VARCHAR(255),
  biografia TEXT,
  estado_coach ENUM('DISPONIBLE','OCUPADO','INACTIVO') DEFAULT 'DISPONIBLE',
  CONSTRAINT fk_coach_user FOREIGN KEY (id_coach) REFERENCES users(id_usuario)
);

CREATE TABLE IF NOT EXISTS cliente (
  id_cliente BIGINT PRIMARY KEY,
  tipo_cliente ENUM('INDIVIDUAL','INSTITUCIONAL') NOT NULL DEFAULT 'INDIVIDUAL',
  fecha_nacimiento DATE,
  estatura_m DECIMAL(4,2),
  peso_inicial DECIMAL(5,2),
  objetivos TEXT,
  restricciones_medicas TEXT,
  CONSTRAINT fk_cliente_user FOREIGN KEY (id_cliente) REFERENCES users(id_usuario)
);

CREATE TABLE IF NOT EXISTS institucion (
  id_institucion BIGINT PRIMARY KEY AUTO_INCREMENT,
  nombre VARCHAR(200) NOT NULL,
  tipo_institucion VARCHAR(100),
  nit VARCHAR(50) UNIQUE,
  direccion VARCHAR(255),
  telefono VARCHAR(30),
  correo_contacto VARCHAR(150),
  num_participantes INT,
  activo BOOLEAN DEFAULT TRUE
);

CREATE TABLE IF NOT EXISTS cliente_institucional (
  id_cliente BIGINT PRIMARY KEY,
  id_institucion BIGINT NOT NULL,
  cargo VARCHAR(100),
  es_contacto_principal BOOLEAN DEFAULT FALSE,
  fecha_vinculacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_cliente_inst_cliente FOREIGN KEY (id_cliente) REFERENCES cliente(id_cliente),
  CONSTRAINT fk_cliente_inst_institucion FOREIGN KEY (id_institucion) REFERENCES institucion(id_institucion)
);

CREATE TABLE IF NOT EXISTS modulo_servicio (
  id_modulo_servicio BIGINT PRIMARY KEY AUTO_INCREMENT,
  nombre ENUM('ENTRENAMIENTO','NUTRICION','CONTENIDO_VIRTUAL','SESIONES','ACOMPANAMIENTO') NOT NULL UNIQUE,
  descripcion VARCHAR(255),
  activo BOOLEAN DEFAULT TRUE
);

CREATE TABLE IF NOT EXISTS plan (
  id_plan BIGINT PRIMARY KEY AUTO_INCREMENT,
  nombre VARCHAR(150) NOT NULL,
  descripcion TEXT,
  precio DECIMAL(10,2) NOT NULL,
  duracion_dias INT DEFAULT 30,
  modalidad ENUM('PRESENCIAL','VIRTUAL','MIXTA') DEFAULT 'VIRTUAL',
  requiere_coach BOOLEAN DEFAULT FALSE,
  incluye_entrenamiento BOOLEAN DEFAULT TRUE,
  incluye_nutricion BOOLEAN DEFAULT FALSE,
  incluye_videos BOOLEAN DEFAULT FALSE,
  incluye_sesiones BOOLEAN DEFAULT FALSE,
  dias_previos_recordatorio_default INT DEFAULT 5,
  estado_plan ENUM('ACTIVO','INACTIVO') DEFAULT 'ACTIVO'
);

CREATE TABLE IF NOT EXISTS programa (
  id_programa BIGINT PRIMARY KEY AUTO_INCREMENT,
  id_plan BIGINT NOT NULL,
  nombre VARCHAR(150) NOT NULL,
  descripcion TEXT,
  objetivo VARCHAR(180),
  nivel VARCHAR(80),
  activo BOOLEAN DEFAULT TRUE,
  CONSTRAINT fk_programa_plan FOREIGN KEY (id_plan) REFERENCES plan(id_plan)
);

CREATE TABLE IF NOT EXISTS plan_modulo (
  id_plan BIGINT NOT NULL,
  id_modulo_servicio BIGINT NOT NULL,
  PRIMARY KEY (id_plan, id_modulo_servicio),
  CONSTRAINT fk_plan_modulo_plan FOREIGN KEY (id_plan) REFERENCES plan(id_plan),
  CONSTRAINT fk_plan_modulo_modulo FOREIGN KEY (id_modulo_servicio) REFERENCES modulo_servicio(id_modulo_servicio)
);

CREATE TABLE IF NOT EXISTS solicitud_ingreso (
  id_solicitud BIGINT PRIMARY KEY AUTO_INCREMENT,
  nombre_completo VARCHAR(180) NOT NULL,
  edad INT,
  identificacion VARCHAR(60) NOT NULL,
  celular VARCHAR(30) NOT NULL,
  plan_interes VARCHAR(150),
  modalidad ENUM('PRESENCIAL','VIRTUAL','MIXTA') DEFAULT 'VIRTUAL',
  tipo_cuenta VARCHAR(80),
  numero_cuenta VARCHAR(100),
  url_comprobante VARCHAR(300),
  estado ENUM('PENDIENTE','EN_REVISION','APROBADA','RECHAZADA') DEFAULT 'PENDIENTE',
  observacion_admin TEXT,
  fecha_solicitud TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS plan_cliente (
  id_plan_cliente BIGINT PRIMARY KEY AUTO_INCREMENT,
  id_plan BIGINT NOT NULL,
  id_cliente BIGINT NOT NULL,
  id_coach BIGINT NULL,
  id_solicitud BIGINT NULL,
  fecha_inicio DATE NOT NULL,
  fecha_fin DATE NOT NULL,
  dias_previos_recordatorio INT DEFAULT 5,
  estado ENUM('ACTIVO','PAUSADO','FINALIZADO','CANCELADO','VENCIDO') DEFAULT 'ACTIVO',
  observaciones TEXT,
  CONSTRAINT fk_plan_cliente_plan FOREIGN KEY (id_plan) REFERENCES plan(id_plan),
  CONSTRAINT fk_plan_cliente_cliente FOREIGN KEY (id_cliente) REFERENCES cliente(id_cliente),
  CONSTRAINT fk_plan_cliente_coach FOREIGN KEY (id_coach) REFERENCES coach(id_coach),
  CONSTRAINT fk_plan_cliente_solicitud FOREIGN KEY (id_solicitud) REFERENCES solicitud_ingreso(id_solicitud)
);

CREATE TABLE IF NOT EXISTS acceso_cliente_modulo (
  id_acceso_cliente_modulo BIGINT PRIMARY KEY AUTO_INCREMENT,
  id_plan_cliente BIGINT NOT NULL,
  id_modulo_servicio BIGINT NOT NULL,
  habilitado BOOLEAN DEFAULT FALSE,
  fecha_habilitacion TIMESTAMP NULL,
  fecha_expiracion TIMESTAMP NULL,
  CONSTRAINT fk_acceso_plan_cliente FOREIGN KEY (id_plan_cliente) REFERENCES plan_cliente(id_plan_cliente),
  CONSTRAINT fk_acceso_modulo FOREIGN KEY (id_modulo_servicio) REFERENCES modulo_servicio(id_modulo_servicio)
);

CREATE TABLE IF NOT EXISTS categoria_video (
  id_categoria_video BIGINT PRIMARY KEY AUTO_INCREMENT,
  nombre VARCHAR(120) NOT NULL,
  descripcion TEXT,
  activo BOOLEAN DEFAULT TRUE
);

CREATE TABLE IF NOT EXISTS programa_virtual (
  id_programa_virtual BIGINT PRIMARY KEY AUTO_INCREMENT,
  id_plan BIGINT NOT NULL,
  nombre VARCHAR(150) NOT NULL,
  descripcion TEXT,
  nivel VARCHAR(80),
  activo BOOLEAN DEFAULT TRUE,
  CONSTRAINT fk_programa_virtual_plan FOREIGN KEY (id_plan) REFERENCES plan(id_plan)
);

CREATE TABLE IF NOT EXISTS video (
  id_video BIGINT PRIMARY KEY AUTO_INCREMENT,
  id_categoria_video BIGINT NULL,
  id_programa_virtual BIGINT NULL,
  titulo VARCHAR(180) NOT NULL,
  descripcion TEXT,
  url_video VARCHAR(300) NOT NULL,
  duracion_minutos INT,
  orden INT DEFAULT 1,
  activo BOOLEAN DEFAULT TRUE,
  CONSTRAINT fk_video_categoria FOREIGN KEY (id_categoria_video) REFERENCES categoria_video(id_categoria_video),
  CONSTRAINT fk_video_programa_virtual FOREIGN KEY (id_programa_virtual) REFERENCES programa_virtual(id_programa_virtual)
);

CREATE TABLE IF NOT EXISTS progreso_video (
  id_progreso_video BIGINT PRIMARY KEY AUTO_INCREMENT,
  id_cliente BIGINT NOT NULL,
  id_video BIGINT NOT NULL,
  estado ENUM('PENDIENTE','EN_PROGRESO','COMPLETADO') DEFAULT 'PENDIENTE',
  porcentaje_avance INT DEFAULT 0,
  fecha_inicio TIMESTAMP NULL,
  fecha_finalizacion TIMESTAMP NULL,
  ultimo_acceso TIMESTAMP NULL,
  CONSTRAINT fk_progreso_video_cliente FOREIGN KEY (id_cliente) REFERENCES cliente(id_cliente),
  CONSTRAINT fk_progreso_video_video FOREIGN KEY (id_video) REFERENCES video(id_video)
);

CREATE TABLE IF NOT EXISTS plan_entrenamiento (
  id_plan_entrenamiento BIGINT PRIMARY KEY AUTO_INCREMENT,
  id_plan_cliente BIGINT NOT NULL,
  nombre VARCHAR(150) NOT NULL,
  objetivo TEXT,
  nivel_dificultad VARCHAR(50),
  duracion_total_dias INT,
  estado_plan ENUM('ACTIVO','INACTIVO','FINALIZADO') DEFAULT 'ACTIVO',
  CONSTRAINT fk_plan_entrenamiento_plan_cliente FOREIGN KEY (id_plan_cliente) REFERENCES plan_cliente(id_plan_cliente)
);

CREATE TABLE IF NOT EXISTS rutina (
  id_rutina BIGINT PRIMARY KEY AUTO_INCREMENT,
  id_plan_entrenamiento BIGINT NOT NULL,
  nombre VARCHAR(150) NOT NULL,
  dias_semana VARCHAR(100),
  duracion_minutos INT,
  version INT DEFAULT 1,
  observaciones TEXT,
  CONSTRAINT fk_rutina_plan_entrenamiento FOREIGN KEY (id_plan_entrenamiento) REFERENCES plan_entrenamiento(id_plan_entrenamiento)
);

CREATE TABLE IF NOT EXISTS ejercicio (
  id_ejercicio BIGINT PRIMARY KEY AUTO_INCREMENT,
  id_rutina BIGINT NOT NULL,
  nombre VARCHAR(150) NOT NULL,
  descripcion TEXT,
  series INT,
  repeticiones INT,
  tiempo_segundos INT,
  descanso_segundos INT,
  instrucciones TEXT,
  CONSTRAINT fk_ejercicio_rutina FOREIGN KEY (id_rutina) REFERENCES rutina(id_rutina)
);

CREATE TABLE IF NOT EXISTS material (
  id_material BIGINT PRIMARY KEY AUTO_INCREMENT,
  id_ejercicio BIGINT NOT NULL,
  tipo ENUM('VIDEO','PDF','IMAGEN','AUDIO') NOT NULL,
  nombre VARCHAR(150),
  url_archivo VARCHAR(300),
  CONSTRAINT fk_material_ejercicio FOREIGN KEY (id_ejercicio) REFERENCES ejercicio(id_ejercicio)
);

CREATE TABLE IF NOT EXISTS plan_nutricional (
  id_plan_nutricional BIGINT PRIMARY KEY AUTO_INCREMENT,
  id_plan_cliente BIGINT NOT NULL,
  nombre VARCHAR(150) NOT NULL,
  objetivo TEXT,
  estado_plan ENUM('ACTIVO','INACTIVO','FINALIZADO') DEFAULT 'ACTIVO',
  recomendaciones_adicionales TEXT,
  CONSTRAINT fk_plan_nutricional_plan_cliente FOREIGN KEY (id_plan_cliente) REFERENCES plan_cliente(id_plan_cliente)
);

CREATE TABLE IF NOT EXISTS comida (
  id_comida BIGINT PRIMARY KEY AUTO_INCREMENT,
  id_plan_nutricional BIGINT NOT NULL,
  tiempo_comida VARCHAR(50),
  grupos_alimenticios TEXT,
  porciones VARCHAR(255),
  calorias_aprox DECIMAL(7,2),
  observaciones TEXT,
  CONSTRAINT fk_comida_plan_nutricional FOREIGN KEY (id_plan_nutricional) REFERENCES plan_nutricional(id_plan_nutricional)
);

CREATE TABLE IF NOT EXISTS registro_progreso (
  id_registro_progreso BIGINT PRIMARY KEY AUTO_INCREMENT,
  id_plan_cliente BIGINT NOT NULL,
  fecha DATE NOT NULL,
  peso DECIMAL(5,2),
  cintura DECIMAL(5,2),
  cadera DECIMAL(5,2),
  brazos DECIMAL(5,2),
  piernas DECIMAL(5,2),
  fotos_evolucion TEXT,
  observacion_cliente TEXT,
  observacion_coach TEXT,
  CONSTRAINT fk_registro_progreso_plan_cliente FOREIGN KEY (id_plan_cliente) REFERENCES plan_cliente(id_plan_cliente)
);

CREATE TABLE IF NOT EXISTS registro_rutina (
  id_registro_rutina BIGINT PRIMARY KEY AUTO_INCREMENT,
  id_rutina BIGINT NOT NULL,
  fecha DATE NOT NULL,
  estado ENUM('PENDIENTE','EN_PROGRESO','COMPLETADA','OMITIDA') DEFAULT 'PENDIENTE',
  porcentaje_avance INT,
  observaciones TEXT,
  CONSTRAINT fk_registro_rutina_rutina FOREIGN KEY (id_rutina) REFERENCES rutina(id_rutina)
);

CREATE TABLE IF NOT EXISTS agenda (
  id_agenda BIGINT PRIMARY KEY AUTO_INCREMENT,
  id_coach BIGINT NOT NULL,
  fecha_hora_inicio TIMESTAMP NOT NULL,
  fecha_hora_fin TIMESTAMP NOT NULL,
  disponible BOOLEAN DEFAULT TRUE,
  descripcion TEXT,
  CONSTRAINT fk_agenda_coach FOREIGN KEY (id_coach) REFERENCES coach(id_coach)
);

CREATE TABLE IF NOT EXISTS sesion (
  id_sesion BIGINT PRIMARY KEY AUTO_INCREMENT,
  id_coach BIGINT NOT NULL,
  titulo VARCHAR(150),
  descripcion TEXT,
  fecha_hora_inicio TIMESTAMP NOT NULL,
  fecha_hora_fin TIMESTAMP NOT NULL,
  duracion_minutos INT,
  tipo ENUM('INDIVIDUAL','GRUPAL') DEFAULT 'INDIVIDUAL',
  modalidad ENUM('PRESENCIAL','VIRTUAL') DEFAULT 'VIRTUAL',
  estado ENUM('PROGRAMADA','EN_CURSO','COMPLETADA','CANCELADA') DEFAULT 'PROGRAMADA',
  cupo_maximo INT,
  enlace_virtual VARCHAR(300),
  ubicacion VARCHAR(255),
  CONSTRAINT fk_sesion_coach FOREIGN KEY (id_coach) REFERENCES coach(id_coach)
);

CREATE TABLE IF NOT EXISTS sesion_participante (
  id_sesion BIGINT NOT NULL,
  id_plan_cliente BIGINT NOT NULL,
  estado_asistencia ENUM('INSCRITO','ASISTIO','AUSENTE','CANCELADO') DEFAULT 'INSCRITO',
  observaciones TEXT,
  PRIMARY KEY (id_sesion, id_plan_cliente),
  CONSTRAINT fk_sesion_participante_sesion FOREIGN KEY (id_sesion) REFERENCES sesion(id_sesion),
  CONSTRAINT fk_sesion_participante_plan_cliente FOREIGN KEY (id_plan_cliente) REFERENCES plan_cliente(id_plan_cliente)
);

CREATE TABLE IF NOT EXISTS pago (
  id_pago BIGINT PRIMARY KEY AUTO_INCREMENT,
  id_plan_cliente BIGINT NULL,
  id_solicitud BIGINT NULL,
  monto DECIMAL(10,2) NOT NULL,
  moneda VARCHAR(10) DEFAULT 'COP',
  fecha_pago TIMESTAMP NULL,
  metodo_pago ENUM('EFECTIVO','TRANSFERENCIA','TARJETA','PSE'),
  estado_pago ENUM('PENDIENTE','PAGADO','FALLIDO','REEMBOLSADO') DEFAULT 'PENDIENTE',
  proveedor_pago VARCHAR(100),
  referencia_transaccion VARCHAR(150),
  codigo_aprobacion VARCHAR(100),
  url_comprobante VARCHAR(300),
  fecha_vencimiento DATE,
  observacion TEXT,
  CONSTRAINT fk_pago_plan_cliente FOREIGN KEY (id_plan_cliente) REFERENCES plan_cliente(id_plan_cliente),
  CONSTRAINT fk_pago_solicitud FOREIGN KEY (id_solicitud) REFERENCES solicitud_ingreso(id_solicitud)
);

CREATE TABLE IF NOT EXISTS evento (
  id_evento BIGINT PRIMARY KEY AUTO_INCREMENT,
  id_coach BIGINT NULL,
  titulo VARCHAR(200) NOT NULL,
  descripcion TEXT,
  fecha_hora_inicio TIMESTAMP NOT NULL,
  fecha_hora_fin TIMESTAMP NOT NULL,
  tipo ENUM('WEBINAR','CLASE','TALLER','COMPETENCIA'),
  modalidad ENUM('PRESENCIAL','VIRTUAL','HIBRIDO') DEFAULT 'VIRTUAL',
  estado ENUM('PROGRAMADO','EN_CURSO','FINALIZADO','CANCELADO') DEFAULT 'PROGRAMADO',
  cupo_maximo INT,
  ubicacion VARCHAR(255),
  enlace_virtual VARCHAR(300),
  CONSTRAINT fk_evento_coach FOREIGN KEY (id_coach) REFERENCES coach(id_coach)
);

CREATE TABLE IF NOT EXISTS evento_participante (
  id_evento BIGINT NOT NULL,
  id_cliente BIGINT NOT NULL,
  estado ENUM('INSCRITO','ASISTIO','AUSENTE','CANCELADO') DEFAULT 'INSCRITO',
  fecha_inscripcion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id_evento, id_cliente),
  CONSTRAINT fk_evento_participante_evento FOREIGN KEY (id_evento) REFERENCES evento(id_evento),
  CONSTRAINT fk_evento_participante_cliente FOREIGN KEY (id_cliente) REFERENCES cliente(id_cliente)
);

CREATE TABLE IF NOT EXISTS chat (
  id_chat BIGINT PRIMARY KEY AUTO_INCREMENT,
  id_cliente BIGINT NOT NULL,
  id_coach BIGINT NOT NULL,
  fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  es_temporal BOOLEAN DEFAULT FALSE,
  fecha_expiracion TIMESTAMP NULL,
  UNIQUE KEY uk_chat_cliente_coach (id_cliente, id_coach),
  CONSTRAINT fk_chat_cliente FOREIGN KEY (id_cliente) REFERENCES cliente(id_cliente),
  CONSTRAINT fk_chat_coach FOREIGN KEY (id_coach) REFERENCES coach(id_coach)
);

CREATE TABLE IF NOT EXISTS mensaje (
  id_mensaje BIGINT PRIMARY KEY AUTO_INCREMENT,
  id_chat BIGINT NOT NULL,
  id_usuario_remitente BIGINT NOT NULL,
  contenido TEXT NOT NULL,
  tipo_mensaje ENUM('TEXTO','ARCHIVO','IMAGEN','AUDIO','VIDEO') DEFAULT 'TEXTO',
  url_adjunto VARCHAR(300),
  fecha_envio TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  leido BOOLEAN DEFAULT FALSE,
  fecha_lectura TIMESTAMP NULL,
  CONSTRAINT fk_mensaje_chat FOREIGN KEY (id_chat) REFERENCES chat(id_chat),
  CONSTRAINT fk_mensaje_remitente FOREIGN KEY (id_usuario_remitente) REFERENCES users(id_usuario)
);

CREATE TABLE IF NOT EXISTS notificacion (
  id_notificacion BIGINT PRIMARY KEY AUTO_INCREMENT,
  id_usuario BIGINT NOT NULL,
  titulo VARCHAR(150) NOT NULL,
  contenido TEXT NOT NULL,
  tipo ENUM('SISTEMA','MENSAJE','PAGO','SESION','PLAN','EVENTO','CONTENIDO') DEFAULT 'SISTEMA',
  leida BOOLEAN DEFAULT FALSE,
  fecha_envio TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  fecha_lectura TIMESTAMP NULL,
  CONSTRAINT fk_notificacion_usuario FOREIGN KEY (id_usuario) REFERENCES users(id_usuario)
);

CREATE TABLE IF NOT EXISTS token_recuperacion (
  id_token_recuperacion BIGINT PRIMARY KEY AUTO_INCREMENT,
  id_usuario BIGINT NOT NULL,
  token VARCHAR(255) NOT NULL UNIQUE,
  fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  fecha_expiracion TIMESTAMP NOT NULL,
  usado BOOLEAN DEFAULT FALSE,
  CONSTRAINT fk_token_recuperacion_usuario FOREIGN KEY (id_usuario) REFERENCES users(id_usuario)
);

CREATE TABLE IF NOT EXISTS reporte_temporal (
  id_reporte_temporal BIGINT PRIMARY KEY AUTO_INCREMENT,
  id_usuario_generador BIGINT NOT NULL,
  id_plan_cliente BIGINT NULL,
  tipo ENUM('PROGRESO','NUTRICIONAL','FINANCIERO','GENERAL','VIRTUAL') NOT NULL,
  fecha_generacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  periodo VARCHAR(50),
  contenido TEXT,
  fecha_expiracion TIMESTAMP NULL,
  CONSTRAINT fk_reporte_generador FOREIGN KEY (id_usuario_generador) REFERENCES users(id_usuario),
  CONSTRAINT fk_reporte_plan_cliente FOREIGN KEY (id_plan_cliente) REFERENCES plan_cliente(id_plan_cliente)
);

CREATE TABLE IF NOT EXISTS bitacora_busqueda (
  id_bitacora_busqueda BIGINT PRIMARY KEY AUTO_INCREMENT,
  id_usuario BIGINT NULL,
  accion VARCHAR(255),
  modulo VARCHAR(100),
  criterio_busqueda TEXT,
  fecha_hora TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  fecha_expiracion TIMESTAMP NULL,
  descripcion TEXT,
  CONSTRAINT fk_bitacora_usuario FOREIGN KEY (id_usuario) REFERENCES users(id_usuario)
);

INSERT INTO rol (nombre, descripcion, permisos, activo) VALUES
('Administrador', 'Rol con acceso total al sistema', 'ALL', TRUE),
('Coach', 'Rol para entrenadores o coaches', 'COACH', TRUE),
('Cliente', 'Rol para clientes individuales', 'CLIENTE', TRUE),
('Cliente-Institucional', 'Rol para clientes institucionales', 'CLIENTE_INSTITUCIONAL', TRUE)
ON DUPLICATE KEY UPDATE descripcion = VALUES(descripcion), permisos = VALUES(permisos), activo = VALUES(activo);

INSERT INTO modulo_servicio (nombre, descripcion, activo) VALUES
('ENTRENAMIENTO', 'Acceso a rutinas y planes de entrenamiento', TRUE),
('NUTRICION', 'Acceso a planes nutricionales', TRUE),
('CONTENIDO_VIRTUAL', 'Acceso a videos pregrabados', TRUE),
('SESIONES', 'Acceso a sesiones individuales o grupales', TRUE),
('ACOMPANAMIENTO', 'Acompañamiento por coach', TRUE)
ON DUPLICATE KEY UPDATE descripcion = VALUES(descripcion), activo = VALUES(activo);

INSERT INTO users (nombre, apellido, correo, hash_contrasena, estado, origen_registro, telefono) VALUES
('Paola', 'Garcia', 'admin@correo.com', '$2y$12$7vY7ssUf1tY6YZZEeU6nPez8/K5Hp0bbVFM6jUKzHlLlTL7uNBnD.', 'ACTIVO', 'ADMINISTRATIVO', '3000000000'),
('Diana', 'Fit', 'coach@correo.com', '$2y$12$7vY7ssUf1tY6YZZEeU6nPez8/K5Hp0bbVFM6jUKzHlLlTL7uNBnD.', 'ACTIVO', 'ADMINISTRATIVO', '3001111111'),
('Ana', 'Rojas', 'cliente@correo.com', '$2y$12$7vY7ssUf1tY6YZZEeU6nPez8/K5Hp0bbVFM6jUKzHlLlTL7uNBnD.', 'ACTIVO', 'ADMINISTRATIVO', '3002222222'),
('Maria', 'Castro', 'institucional@correo.com', '$2y$12$7vY7ssUf1tY6YZZEeU6nPez8/K5Hp0bbVFM6jUKzHlLlTL7uNBnD.', 'ACTIVO', 'ADMINISTRATIVO', '3003333333')
ON DUPLICATE KEY UPDATE estado = 'ACTIVO';

INSERT IGNORE INTO users_roles (id_usuario, id_rol)
SELECT u.id_usuario, r.id_rol FROM users u JOIN rol r ON
(r.nombre = 'Administrador' AND u.correo = 'admin@correo.com') OR
(r.nombre = 'Coach' AND u.correo = 'coach@correo.com') OR
(r.nombre = 'Cliente' AND u.correo = 'cliente@correo.com') OR
(r.nombre = 'Cliente-Institucional' AND u.correo = 'institucional@correo.com');

INSERT IGNORE INTO coach (id_coach, especialidad, credencial, biografia)
SELECT id_usuario, 'Fuerza y recomposición corporal', 'Coach certificado StayFit', 'Especialista en entrenamiento femenino y planes mixtos.' FROM users WHERE correo = 'coach@correo.com';

INSERT IGNORE INTO cliente (id_cliente, tipo_cliente, fecha_nacimiento, estatura_m, peso_inicial, objetivos)
SELECT id_usuario, 'INDIVIDUAL', '1998-04-14', 1.65, 66.50, 'Mejorar composición corporal' FROM users WHERE correo = 'cliente@correo.com';

INSERT INTO plan (nombre, descripcion, precio, duracion_dias, modalidad, requiere_coach, incluye_entrenamiento, incluye_nutricion, incluye_videos, incluye_sesiones, estado_plan) VALUES
('Programa Virtual Fit', 'Programa con videos pregrabados y seguimiento de avance.', 90000, 30, 'VIRTUAL', FALSE, TRUE, FALSE, TRUE, FALSE, 'ACTIVO'),
('Plan Presencial Integral', 'Plan con coach asignado, entrenamiento y nutrición.', 180000, 30, 'PRESENCIAL', TRUE, TRUE, TRUE, FALSE, TRUE, 'ACTIVO'),
('Plan Mixto Premium', 'Plan con coach, sesiones y contenido virtual.', 240000, 30, 'MIXTA', TRUE, TRUE, TRUE, TRUE, TRUE, 'ACTIVO')
ON DUPLICATE KEY UPDATE descripcion = VALUES(descripcion);

INSERT INTO categoria_video (nombre, descripcion) VALUES
('Principiante', 'Videos para iniciar de forma segura'),
('Intermedio', 'Videos de fuerza y acondicionamiento'),
('Movilidad', 'Movilidad, recuperación y técnica')
ON DUPLICATE KEY UPDATE descripcion = VALUES(descripcion);
