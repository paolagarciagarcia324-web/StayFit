-- StayFit - Esquema relacional MySQL 8
CREATE DATABASE IF NOT EXISTS stayfit CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE stayfit;

CREATE TABLE rol (
  id_rol BIGINT PRIMARY KEY AUTO_INCREMENT,
  nombre ENUM('ADMIN','COACH','CLIENTE') NOT NULL UNIQUE,
  descripcion VARCHAR(255),
  permisos TEXT,
  activo BOOLEAN DEFAULT TRUE
);

CREATE TABLE users (
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

CREATE TABLE users_roles (
  id_usuario BIGINT NOT NULL,
  id_rol BIGINT NOT NULL,
  PRIMARY KEY (id_usuario, id_rol),
  CONSTRAINT fk_users_roles_usuario FOREIGN KEY (id_usuario) REFERENCES users(id_usuario),
  CONSTRAINT fk_users_roles_rol FOREIGN KEY (id_rol) REFERENCES rol(id_rol)
);

CREATE TABLE coach (
  id_coach BIGINT PRIMARY KEY,
  especialidad VARCHAR(150),
  credencial VARCHAR(255),
  biografia TEXT,
  CONSTRAINT fk_coach_user FOREIGN KEY (id_coach) REFERENCES users(id_usuario)
);

CREATE TABLE cliente (
  id_cliente BIGINT PRIMARY KEY,
  tipo_cliente ENUM('INDIVIDUAL','INSTITUCIONAL') NOT NULL DEFAULT 'INDIVIDUAL',
  fecha_nacimiento DATE,
  estatura_m DECIMAL(4,2),
  peso_inicial DECIMAL(5,2),
  objetivos TEXT,
  restricciones_medicas TEXT,
  CONSTRAINT fk_cliente_user FOREIGN KEY (id_cliente) REFERENCES users(id_usuario)
);

CREATE TABLE institucion (
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

CREATE TABLE cliente_institucional (
  id_cliente BIGINT PRIMARY KEY,
  id_institucion BIGINT NOT NULL,
  cargo VARCHAR(100),
  es_contacto_principal BOOLEAN DEFAULT FALSE,
  fecha_vinculacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_cliente_inst_cliente FOREIGN KEY (id_cliente) REFERENCES cliente(id_cliente),
  CONSTRAINT fk_cliente_inst_institucion FOREIGN KEY (id_institucion) REFERENCES institucion(id_institucion)
);

CREATE TABLE modulo_servicio (
  id_modulo_servicio BIGINT PRIMARY KEY AUTO_INCREMENT,
  nombre ENUM('ENTRENAMIENTO','NUTRICION') NOT NULL UNIQUE,
  descripcion VARCHAR(255),
  activo BOOLEAN DEFAULT TRUE
);

CREATE TABLE plan (
  id_plan BIGINT PRIMARY KEY AUTO_INCREMENT,
  nombre VARCHAR(150) NOT NULL,
  descripcion TEXT,
  precio DECIMAL(10,2) NOT NULL,
  duracion_dias INT,
  dias_previos_recordatorio_default INT DEFAULT 5,
  estado_plan ENUM('ACTIVO','INACTIVO') DEFAULT 'ACTIVO'
);

CREATE TABLE plan_modulo (
  id_plan BIGINT NOT NULL,
  id_modulo_servicio BIGINT NOT NULL,
  PRIMARY KEY (id_plan, id_modulo_servicio),
  CONSTRAINT fk_plan_modulo_plan FOREIGN KEY (id_plan) REFERENCES plan(id_plan),
  CONSTRAINT fk_plan_modulo_modulo FOREIGN KEY (id_modulo_servicio) REFERENCES modulo_servicio(id_modulo_servicio)
);

CREATE TABLE plan_cliente (
  id_plan_cliente BIGINT PRIMARY KEY AUTO_INCREMENT,
  id_plan BIGINT NOT NULL,
  id_cliente BIGINT NOT NULL,
  id_coach BIGINT NOT NULL,
  fecha_inicio DATE NOT NULL,
  fecha_fin DATE NOT NULL,
  dias_previos_recordatorio INT DEFAULT 5,
  estado ENUM('ACTIVO','PAUSADO','FINALIZADO','CANCELADO','VENCIDO') DEFAULT 'ACTIVO',
  observaciones TEXT,
  CONSTRAINT fk_plan_cliente_plan FOREIGN KEY (id_plan) REFERENCES plan(id_plan),
  CONSTRAINT fk_plan_cliente_cliente FOREIGN KEY (id_cliente) REFERENCES cliente(id_cliente),
  CONSTRAINT fk_plan_cliente_coach FOREIGN KEY (id_coach) REFERENCES coach(id_coach)
);

CREATE TABLE acceso_cliente_modulo (
  id_acceso_cliente_modulo BIGINT PRIMARY KEY AUTO_INCREMENT,
  id_plan_cliente BIGINT NOT NULL,
  id_modulo_servicio BIGINT NOT NULL,
  habilitado BOOLEAN DEFAULT FALSE,
  fecha_habilitacion TIMESTAMP NULL,
  fecha_expiracion TIMESTAMP NULL,
  CONSTRAINT fk_acceso_plan_cliente FOREIGN KEY (id_plan_cliente) REFERENCES plan_cliente(id_plan_cliente),
  CONSTRAINT fk_acceso_modulo FOREIGN KEY (id_modulo_servicio) REFERENCES modulo_servicio(id_modulo_servicio)
);

CREATE TABLE plan_entrenamiento (
  id_plan_entrenamiento BIGINT PRIMARY KEY AUTO_INCREMENT,
  id_plan_cliente BIGINT NOT NULL,
  nombre VARCHAR(150) NOT NULL,
  objetivo TEXT,
  nivel_dificultad VARCHAR(50),
  duracion_total_dias INT,
  estado_plan ENUM('ACTIVO','INACTIVO','FINALIZADO') DEFAULT 'ACTIVO',
  CONSTRAINT fk_plan_entrenamiento_plan_cliente FOREIGN KEY (id_plan_cliente) REFERENCES plan_cliente(id_plan_cliente)
);

CREATE TABLE rutina (
  id_rutina BIGINT PRIMARY KEY AUTO_INCREMENT,
  id_plan_entrenamiento BIGINT NOT NULL,
  nombre VARCHAR(150) NOT NULL,
  dias_semana VARCHAR(100),
  duracion_minutos INT,
  version INT DEFAULT 1,
  observaciones TEXT,
  CONSTRAINT fk_rutina_plan_entrenamiento FOREIGN KEY (id_plan_entrenamiento) REFERENCES plan_entrenamiento(id_plan_entrenamiento)
);

CREATE TABLE ejercicio (
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

CREATE TABLE material (
  id_material BIGINT PRIMARY KEY AUTO_INCREMENT,
  id_ejercicio BIGINT NOT NULL,
  tipo ENUM('VIDEO','PDF','IMAGEN','AUDIO') NOT NULL,
  nombre VARCHAR(150),
  url_archivo VARCHAR(300),
  CONSTRAINT fk_material_ejercicio FOREIGN KEY (id_ejercicio) REFERENCES ejercicio(id_ejercicio)
);

CREATE TABLE plan_nutricional (
  id_plan_nutricional BIGINT PRIMARY KEY AUTO_INCREMENT,
  id_plan_cliente BIGINT NOT NULL,
  nombre VARCHAR(150) NOT NULL,
  objetivo TEXT,
  estado_plan ENUM('ACTIVO','INACTIVO','FINALIZADO') DEFAULT 'ACTIVO',
  recomendaciones_adicionales TEXT,
  CONSTRAINT fk_plan_nutricional_plan_cliente FOREIGN KEY (id_plan_cliente) REFERENCES plan_cliente(id_plan_cliente)
);

CREATE TABLE comida (
  id_comida BIGINT PRIMARY KEY AUTO_INCREMENT,
  id_plan_nutricional BIGINT NOT NULL,
  tiempo_comida VARCHAR(50),
  grupos_alimenticios TEXT,
  porciones VARCHAR(255),
  calorias_aprox DECIMAL(7,2),
  observaciones TEXT,
  CONSTRAINT fk_comida_plan_nutricional FOREIGN KEY (id_plan_nutricional) REFERENCES plan_nutricional(id_plan_nutricional)
);

CREATE TABLE registro_progreso (
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

CREATE TABLE registro_rutina (
  id_registro_rutina BIGINT PRIMARY KEY AUTO_INCREMENT,
  id_rutina BIGINT NOT NULL,
  fecha DATE NOT NULL,
  estado ENUM('PENDIENTE','EN_PROGRESO','COMPLETADA','OMITIDA') DEFAULT 'PENDIENTE',
  porcentaje_avance INT,
  observaciones TEXT,
  CONSTRAINT fk_registro_rutina_rutina FOREIGN KEY (id_rutina) REFERENCES rutina(id_rutina)
);

CREATE TABLE agenda (
  id_agenda BIGINT PRIMARY KEY AUTO_INCREMENT,
  id_coach BIGINT NOT NULL,
  fecha_hora_inicio TIMESTAMP NOT NULL,
  fecha_hora_fin TIMESTAMP NOT NULL,
  disponible BOOLEAN DEFAULT TRUE,
  descripcion TEXT,
  CONSTRAINT fk_agenda_coach FOREIGN KEY (id_coach) REFERENCES coach(id_coach)
);

CREATE TABLE sesion (
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

CREATE TABLE sesion_participante (
  id_sesion BIGINT NOT NULL,
  id_plan_cliente BIGINT NOT NULL,
  estado_asistencia ENUM('INSCRITO','ASISTIO','AUSENTE','CANCELADO') DEFAULT 'INSCRITO',
  observaciones TEXT,
  PRIMARY KEY (id_sesion, id_plan_cliente),
  CONSTRAINT fk_sesion_participante_sesion FOREIGN KEY (id_sesion) REFERENCES sesion(id_sesion),
  CONSTRAINT fk_sesion_participante_plan_cliente FOREIGN KEY (id_plan_cliente) REFERENCES plan_cliente(id_plan_cliente)
);

CREATE TABLE pago (
  id_pago BIGINT PRIMARY KEY AUTO_INCREMENT,
  id_plan_cliente BIGINT NOT NULL,
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
  CONSTRAINT fk_pago_plan_cliente FOREIGN KEY (id_plan_cliente) REFERENCES plan_cliente(id_plan_cliente)
);

CREATE TABLE evento (
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

CREATE TABLE evento_participante (
  id_evento BIGINT NOT NULL,
  id_cliente BIGINT NOT NULL,
  estado ENUM('INSCRITO','ASISTIO','AUSENTE','CANCELADO') DEFAULT 'INSCRITO',
  fecha_inscripcion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id_evento, id_cliente),
  CONSTRAINT fk_evento_participante_evento FOREIGN KEY (id_evento) REFERENCES evento(id_evento),
  CONSTRAINT fk_evento_participante_cliente FOREIGN KEY (id_cliente) REFERENCES cliente(id_cliente)
);

CREATE TABLE chat (
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

CREATE TABLE mensaje (
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

CREATE TABLE notificacion (
  id_notificacion BIGINT PRIMARY KEY AUTO_INCREMENT,
  id_usuario BIGINT NOT NULL,
  titulo VARCHAR(150) NOT NULL,
  contenido TEXT NOT NULL,
  tipo ENUM('SISTEMA','MENSAJE','PAGO','SESION','PLAN','EVENTO') DEFAULT 'SISTEMA',
  leida BOOLEAN DEFAULT FALSE,
  fecha_envio TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  fecha_lectura TIMESTAMP NULL,
  CONSTRAINT fk_notificacion_usuario FOREIGN KEY (id_usuario) REFERENCES users(id_usuario)
);

CREATE TABLE token_recuperacion (
  id_token_recuperacion BIGINT PRIMARY KEY AUTO_INCREMENT,
  id_usuario BIGINT NOT NULL,
  token VARCHAR(255) NOT NULL UNIQUE,
  fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  fecha_expiracion TIMESTAMP NOT NULL,
  usado BOOLEAN DEFAULT FALSE,
  CONSTRAINT fk_token_recuperacion_usuario FOREIGN KEY (id_usuario) REFERENCES users(id_usuario)
);

CREATE TABLE reporte_temporal (
  id_reporte_temporal BIGINT PRIMARY KEY AUTO_INCREMENT,
  id_usuario_generador BIGINT NOT NULL,
  id_plan_cliente BIGINT NULL,
  tipo ENUM('PROGRESO','NUTRICIONAL','FINANCIERO','GENERAL') NOT NULL,
  fecha_generacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  periodo VARCHAR(50),
  contenido TEXT,
  fecha_expiracion TIMESTAMP NULL,
  CONSTRAINT fk_reporte_generador FOREIGN KEY (id_usuario_generador) REFERENCES users(id_usuario),
  CONSTRAINT fk_reporte_plan_cliente FOREIGN KEY (id_plan_cliente) REFERENCES plan_cliente(id_plan_cliente)
);

CREATE TABLE bitacora_busqueda (
  id_bitacora_busqueda BIGINT PRIMARY KEY AUTO_INCREMENT,
  id_usuario BIGINT NOT NULL,
  accion VARCHAR(255),
  modulo VARCHAR(100),
  criterio_busqueda TEXT,
  fecha_hora TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  fecha_expiracion TIMESTAMP NULL,
  descripcion TEXT,
  CONSTRAINT fk_bitacora_usuario FOREIGN KEY (id_usuario) REFERENCES users(id_usuario)
);
