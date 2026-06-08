-- =========================================================
-- BASE DE DATOS FINAL PROPUESTA - STAYFIT
-- Plataforma fitness con planes, nutricion, entrenamiento,
-- pagos, solicitudes de compra, cupos, instituciones,
-- agenda, eventos, chat, notificaciones y reportes.
-- Motor recomendado: MySQL 8 / MariaDB 10.4+
-- =========================================================

CREATE DATABASE IF NOT EXISTS stayfit
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE stayfit;

-- =========================================================
-- 1. SEGURIDAD, USUARIOS Y ROLES
-- =========================================================

CREATE TABLE user (
  id_user INT AUTO_INCREMENT PRIMARY KEY,
  nombres VARCHAR(100) NOT NULL,
  apellidos VARCHAR(100) NOT NULL,
  documento_identidad VARCHAR(30) NULL UNIQUE,
  correo VARCHAR(150) NOT NULL UNIQUE,
  telefono VARCHAR(30) NULL,
  password_hash VARCHAR(255) NOT NULL,
  foto_perfil_url VARCHAR(255) NULL,
  estado ENUM('ACTIVO','INACTIVO','BLOQUEADO','PENDIENTE') NOT NULL DEFAULT 'ACTIVO',
  correo_verificado_en DATETIME NULL,
  ultimo_acceso_en DATETIME NULL,
  creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  actualizado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE roles (
  id_rol INT AUTO_INCREMENT PRIMARY KEY,
  codigo VARCHAR(50) NOT NULL UNIQUE,
  nombre VARCHAR(80) NOT NULL,
  descripcion VARCHAR(255) NULL,
  estado ENUM('ACTIVO','INACTIVO') NOT NULL DEFAULT 'ACTIVO',
  creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  actualizado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE user_roles (
  id_user_rol INT AUTO_INCREMENT PRIMARY KEY,
  id_user INT NOT NULL,
  id_rol INT NOT NULL,
  creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_user_rol (id_user, id_rol),
  CONSTRAINT fk_user_roles_user
    FOREIGN KEY (id_user) REFERENCES user(id_user)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_user_roles_rol
    FOREIGN KEY (id_rol) REFERENCES roles(id_rol)
    ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE tokens_recuperacion (
  id_token INT AUTO_INCREMENT PRIMARY KEY,
  id_user INT NOT NULL,
  token VARCHAR(255) NOT NULL UNIQUE,
  usado TINYINT(1) NOT NULL DEFAULT 0,
  expira_en DATETIME NOT NULL,
  creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_tokens_usuario
    FOREIGN KEY (id_user) REFERENCES user(id_user)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

-- =========================================================
-- 2. INSTITUCIONES, COACHES Y CLIENTES
-- =========================================================

CREATE TABLE instituciones (
  id_institucion INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(150) NOT NULL,
  nit VARCHAR(50) NULL UNIQUE,
  tipo_institucion VARCHAR(80) NULL,
  nombre_contacto VARCHAR(120) NULL,
  correo_contacto VARCHAR(150) NULL,
  telefono_contacto VARCHAR(30) NULL,
  direccion VARCHAR(180) NULL,
  ciudad VARCHAR(100) NULL,
  observaciones TEXT NULL,
  estado ENUM('ACTIVA','INACTIVA') NOT NULL DEFAULT 'ACTIVA',
  creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  actualizado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE enlaces_registro_institucional (
  id_enlace INT AUTO_INCREMENT PRIMARY KEY,
  id_institucion INT NOT NULL,
  id_plan INT NOT NULL,
  token VARCHAR(64) NOT NULL,
  activo TINYINT(1) NOT NULL DEFAULT 1,
  registros_realizados INT NOT NULL DEFAULT 0,
  creado_por INT NULL,
  creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  actualizado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uk_enlace_token (token),
  UNIQUE KEY uk_enlace_institucion (id_institucion),
  CONSTRAINT fk_enlace_institucion
    FOREIGN KEY (id_institucion) REFERENCES instituciones(id_institucion)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_enlace_plan
    FOREIGN KEY (id_plan) REFERENCES planes(id_plan)
    ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE coaches (
  id_coach INT AUTO_INCREMENT PRIMARY KEY,
  id_user INT NOT NULL UNIQUE,
  especialidad VARCHAR(120) NULL,
  experiencia_anios INT NULL,
  certificaciones TEXT NULL,
  biografia TEXT NULL,
  estado_coach ENUM('ACTIVO','INACTIVO') NOT NULL DEFAULT 'ACTIVO',
  creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  actualizado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_coaches_usuario
    FOREIGN KEY (id_user) REFERENCES user(id_user)
    ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE clientes (
  id_cliente INT AUTO_INCREMENT PRIMARY KEY,
  id_user INT NOT NULL UNIQUE,
  id_coach INT NULL,
  id_institucion INT NULL,
  fecha_nacimiento DATE NULL,
  genero ENUM('FEMENINO','MASCULINO','OTRO','NO_ESPECIFICA') NOT NULL DEFAULT 'FEMENINO',
  direccion VARCHAR(180) NULL,
  ciudad VARCHAR(100) NULL,
  objetivo_principal VARCHAR(180) NULL,
  peso_inicial_kg DECIMAL(5,2) NULL,
  estatura_cm DECIMAL(5,2) NULL,
  estado_cliente ENUM('ACTIVA','INACTIVA','SUSPENDIDA') NOT NULL DEFAULT 'ACTIVA',
  fecha_alta DATE NOT NULL,
  creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  actualizado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_clientes_usuario
    FOREIGN KEY (id_user) REFERENCES user(id_user)
    ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT fk_clientes_coach
    FOREIGN KEY (id_coach) REFERENCES coaches(id_coach)
    ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT fk_clientes_institucion
    FOREIGN KEY (id_institucion) REFERENCES instituciones(id_institucion)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB;

-- =========================================================
-- 3. PLANES, MODULOS, CUPOS, SOLICITUDES Y PAGOS
-- =========================================================

CREATE TABLE planes (
  id_plan INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(150) NOT NULL,
  slug VARCHAR(170) NOT NULL UNIQUE,
  descripcion TEXT NULL,
  precio DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  duracion_dias INT NOT NULL DEFAULT 30,
  modalidad ENUM('VIRTUAL','PRESENCIAL','MIXTO') NOT NULL DEFAULT 'VIRTUAL',
  tipo_cliente ENUM('INDIVIDUAL','INSTITUCIONAL','AMBOS') NOT NULL DEFAULT 'INDIVIDUAL',
  cupo_maximo INT NULL,
  requiere_coach TINYINT(1) NOT NULL DEFAULT 0,
  incluye_entrenamiento TINYINT(1) NOT NULL DEFAULT 0,
  incluye_nutricion TINYINT(1) NOT NULL DEFAULT 0,
  incluye_videos TINYINT(1) NOT NULL DEFAULT 0,
  incluye_sesiones TINYINT(1) NOT NULL DEFAULT 0,
  incluye_eventos TINYINT(1) NOT NULL DEFAULT 0,
  imagen_url VARCHAR(255) NULL,
  destacado TINYINT(1) NOT NULL DEFAULT 0,
  fecha_inicio_programa DATE NULL,
  fecha_fin_programa DATE NULL,
  estado_plan ENUM('ACTIVO','INACTIVO') NOT NULL DEFAULT 'ACTIVO',
  creado_por INT NULL,
  creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  actualizado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT chk_planes_precio CHECK (precio >= 0),
  CONSTRAINT chk_planes_duracion CHECK (duracion_dias > 0),
  CONSTRAINT chk_planes_cupo CHECK (cupo_maximo IS NULL OR cupo_maximo > 0),
  CONSTRAINT fk_planes_creado_por
    FOREIGN KEY (creado_por) REFERENCES user(id_user)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE modulos_servicio (
  id_modulo INT AUTO_INCREMENT PRIMARY KEY,
  codigo VARCHAR(50) NOT NULL UNIQUE,
  nombre VARCHAR(100) NOT NULL,
  descripcion VARCHAR(255) NULL,
  estado ENUM('ACTIVO','INACTIVO') NOT NULL DEFAULT 'ACTIVO',
  creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE plan_modulos (
  id_plan_modulo INT AUTO_INCREMENT PRIMARY KEY,
  id_plan INT NOT NULL,
  id_modulo INT NOT NULL,
  creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_plan_modulo (id_plan, id_modulo),
  CONSTRAINT fk_plan_modulos_plan
    FOREIGN KEY (id_plan) REFERENCES planes(id_plan)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_plan_modulos_modulo
    FOREIGN KEY (id_modulo) REFERENCES modulos_servicio(id_modulo)
    ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE solicitudes_compra (
  id_solicitud INT AUTO_INCREMENT PRIMARY KEY,
  codigo_solicitud VARCHAR(40) NOT NULL UNIQUE,
  id_plan INT NOT NULL,
  id_institucion INT NULL,
  nombres VARCHAR(100) NOT NULL,
  apellidos VARCHAR(100) NOT NULL,
  documento_identidad VARCHAR(30) NOT NULL,
  correo VARCHAR(150) NOT NULL,
  telefono VARCHAR(30) NULL,
  ciudad VARCHAR(100) NULL,
  tipo_cliente ENUM('INDIVIDUAL','INSTITUCIONAL') NOT NULL DEFAULT 'INDIVIDUAL',
  observacion_usuario TEXT NULL,
  estado_solicitud ENUM('PENDIENTE','VALIDADA','RECHAZADA','CANCELADA') NOT NULL DEFAULT 'PENDIENTE',
  revisado_por INT NULL,
  fecha_revision DATETIME NULL,
  observacion_admin TEXT NULL,
  creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  actualizado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_solicitudes_plan
    FOREIGN KEY (id_plan) REFERENCES planes(id_plan)
    ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT fk_solicitudes_institucion
    FOREIGN KEY (id_institucion) REFERENCES instituciones(id_institucion)
    ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT fk_solicitudes_revisado_por
    FOREIGN KEY (revisado_por) REFERENCES user(id_user)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE pagos (
  id_pago INT AUTO_INCREMENT PRIMARY KEY,
  id_solicitud INT NOT NULL,
  id_cliente INT NULL,
  id_plan INT NOT NULL,
  monto DECIMAL(12,2) NOT NULL,
  metodo_pago ENUM('TRANSFERENCIA','NEQUI','DAVIPLATA','TARJETA','EFECTIVO','OTRO') NOT NULL DEFAULT 'TRANSFERENCIA',
  referencia_pago VARCHAR(120) NULL,
  comprobante_url VARCHAR(255) NULL,
  estado_pago ENUM('PENDIENTE','VALIDADO','RECHAZADO') NOT NULL DEFAULT 'PENDIENTE',
  fecha_pago DATETIME NOT NULL,
  validado_por INT NULL,
  fecha_validacion DATETIME NULL,
  observacion_admin TEXT NULL,
  creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  actualizado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_pago_solicitud (id_solicitud),
  CONSTRAINT chk_pagos_monto CHECK (monto >= 0),
  CONSTRAINT fk_pagos_solicitud
    FOREIGN KEY (id_solicitud) REFERENCES solicitudes_compra(id_solicitud)
    ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT fk_pagos_cliente
    FOREIGN KEY (id_cliente) REFERENCES clientes(id_cliente)
    ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT fk_pagos_plan
    FOREIGN KEY (id_plan) REFERENCES planes(id_plan)
    ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT fk_pagos_validado_por
    FOREIGN KEY (validado_por) REFERENCES user(id_user)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE planes_cliente (
  id_plan_cliente INT AUTO_INCREMENT PRIMARY KEY,
  id_cliente INT NOT NULL,
  id_plan INT NOT NULL,
  id_solicitud INT NULL,
  id_pago INT NULL,
  fecha_inicio DATE NOT NULL,
  fecha_fin DATE NOT NULL,
  estado_plan_cliente ENUM('ACTIVO','VENCIDO','CANCELADO','SUSPENDIDO') NOT NULL DEFAULT 'ACTIVO',
  creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  actualizado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_planes_cliente_cliente
    FOREIGN KEY (id_cliente) REFERENCES clientes(id_cliente)
    ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT fk_planes_cliente_plan
    FOREIGN KEY (id_plan) REFERENCES planes(id_plan)
    ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT fk_planes_cliente_solicitud
    FOREIGN KEY (id_solicitud) REFERENCES solicitudes_compra(id_solicitud)
    ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT fk_planes_cliente_pago
    FOREIGN KEY (id_pago) REFERENCES pagos(id_pago)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE acceso_cliente_modulo (
  id_acceso INT AUTO_INCREMENT PRIMARY KEY,
  id_plan_cliente INT NOT NULL,
  id_cliente INT NOT NULL,
  id_modulo INT NOT NULL,
  fecha_inicio DATE NOT NULL,
  fecha_fin DATE NOT NULL,
  estado_acceso ENUM('ACTIVO','INACTIVO','VENCIDO') NOT NULL DEFAULT 'ACTIVO',
  creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  actualizado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_acceso_plan_cliente_modulo (id_plan_cliente, id_modulo),
  CONSTRAINT fk_acceso_plan_cliente
    FOREIGN KEY (id_plan_cliente) REFERENCES planes_cliente(id_plan_cliente)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_acceso_cliente
    FOREIGN KEY (id_cliente) REFERENCES clientes(id_cliente)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_acceso_modulo
    FOREIGN KEY (id_modulo) REFERENCES modulos_servicio(id_modulo)
    ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB;

-- =========================================================
-- 4. PROGRAMAS VIRTUALES Y VIDEOS
-- =========================================================

CREATE TABLE programas_virtuales (
  id_programa INT AUTO_INCREMENT PRIMARY KEY,
  id_plan INT NOT NULL,
  nombre VARCHAR(150) NOT NULL,
  descripcion TEXT NULL,
  imagen_url VARCHAR(255) NULL,
  estado_programa ENUM('ACTIVO','INACTIVO') NOT NULL DEFAULT 'ACTIVO',
  creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  actualizado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_programas_plan
    FOREIGN KEY (id_plan) REFERENCES planes(id_plan)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE categorias_video (
  id_categoria INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(120) NOT NULL,
  descripcion VARCHAR(255) NULL,
  orden INT NOT NULL DEFAULT 1,
  estado_categoria ENUM('ACTIVA','INACTIVA') NOT NULL DEFAULT 'ACTIVA',
  creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE videos (
  id_video INT AUTO_INCREMENT PRIMARY KEY,
  id_programa INT NOT NULL,
  id_categoria INT NULL,
  titulo VARCHAR(160) NOT NULL,
  descripcion TEXT NULL,
  url_video VARCHAR(255) NOT NULL,
  imagen_portada_url VARCHAR(255) NULL,
  duracion_minutos INT NULL,
  orden INT NOT NULL DEFAULT 1,
  estado_video ENUM('ACTIVO','INACTIVO') NOT NULL DEFAULT 'ACTIVO',
  creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  actualizado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_videos_programa
    FOREIGN KEY (id_programa) REFERENCES programas_virtuales(id_programa)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_videos_categoria
    FOREIGN KEY (id_categoria) REFERENCES categorias_video(id_categoria)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE progreso_videos (
  id_progreso_video INT AUTO_INCREMENT PRIMARY KEY,
  id_cliente INT NOT NULL,
  id_video INT NOT NULL,
  estado_progreso ENUM('NO_INICIADO','EN_PROGRESO','COMPLETADO') NOT NULL DEFAULT 'NO_INICIADO',
  porcentaje_avance DECIMAL(5,2) NOT NULL DEFAULT 0.00,
  iniciado_en DATETIME NULL,
  completado_en DATETIME NULL,
  actualizado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_cliente_video (id_cliente, id_video),
  CONSTRAINT chk_progreso_video_porcentaje CHECK (porcentaje_avance >= 0 AND porcentaje_avance <= 100),
  CONSTRAINT fk_progreso_video_cliente
    FOREIGN KEY (id_cliente) REFERENCES clientes(id_cliente)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_progreso_video_video
    FOREIGN KEY (id_video) REFERENCES videos(id_video)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

-- =========================================================
-- 5. ENTRENAMIENTO
-- =========================================================

CREATE TABLE planes_entrenamiento (
  id_plan_entrenamiento INT AUTO_INCREMENT PRIMARY KEY,
  id_cliente INT NULL,
  id_plan INT NULL,
  id_coach INT NULL,
  titulo VARCHAR(150) NOT NULL,
  objetivo TEXT NULL,
  nivel ENUM('PRINCIPIANTE','INTERMEDIO','AVANZADO') NOT NULL DEFAULT 'PRINCIPIANTE',
  fecha_inicio DATE NULL,
  fecha_fin DATE NULL,
  estado_entrenamiento ENUM('ACTIVO','INACTIVO','FINALIZADO') NOT NULL DEFAULT 'ACTIVO',
  creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  actualizado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_entrenamiento_cliente
    FOREIGN KEY (id_cliente) REFERENCES clientes(id_cliente)
    ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT fk_entrenamiento_plan
    FOREIGN KEY (id_plan) REFERENCES planes(id_plan)
    ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT fk_entrenamiento_coach
    FOREIGN KEY (id_coach) REFERENCES coaches(id_coach)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE rutinas (
  id_rutina INT AUTO_INCREMENT PRIMARY KEY,
  id_plan_entrenamiento INT NOT NULL,
  titulo VARCHAR(150) NOT NULL,
  descripcion TEXT NULL,
  dia_semana ENUM('LUNES','MARTES','MIERCOLES','JUEVES','VIERNES','SABADO','DOMINGO') NULL,
  orden INT NOT NULL DEFAULT 1,
  estado_rutina ENUM('ACTIVA','INACTIVA') NOT NULL DEFAULT 'ACTIVA',
  creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  actualizado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_rutinas_plan_entrenamiento
    FOREIGN KEY (id_plan_entrenamiento) REFERENCES planes_entrenamiento(id_plan_entrenamiento)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE ejercicios (
  id_ejercicio INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(150) NOT NULL,
  descripcion TEXT NULL,
  grupo_muscular VARCHAR(120) NULL,
  equipo_necesario VARCHAR(150) NULL,
  url_video VARCHAR(255) NULL,
  imagen_url VARCHAR(255) NULL,
  estado_ejercicio ENUM('ACTIVO','INACTIVO') NOT NULL DEFAULT 'ACTIVO',
  creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  actualizado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE rutina_ejercicios (
  id_rutina_ejercicio INT AUTO_INCREMENT PRIMARY KEY,
  id_rutina INT NOT NULL,
  id_ejercicio INT NOT NULL,
  series INT NULL,
  repeticiones VARCHAR(50) NULL,
  duracion_segundos INT NULL,
  descanso_segundos INT NULL,
  orden INT NOT NULL DEFAULT 1,
  observaciones TEXT NULL,
  CONSTRAINT fk_rutina_ejercicios_rutina
    FOREIGN KEY (id_rutina) REFERENCES rutinas(id_rutina)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_rutina_ejercicios_ejercicio
    FOREIGN KEY (id_ejercicio) REFERENCES ejercicios(id_ejercicio)
    ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE material_entrenamiento (
  id_material INT AUTO_INCREMENT PRIMARY KEY,
  id_plan_entrenamiento INT NOT NULL,
  titulo VARCHAR(150) NOT NULL,
  tipo_material ENUM('PDF','VIDEO','IMAGEN','ENLACE','OTRO') NOT NULL DEFAULT 'ENLACE',
  url_material VARCHAR(255) NOT NULL,
  descripcion TEXT NULL,
  creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_material_entrenamiento_plan
    FOREIGN KEY (id_plan_entrenamiento) REFERENCES planes_entrenamiento(id_plan_entrenamiento)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE registro_rutinas (
  id_registro_rutina INT AUTO_INCREMENT PRIMARY KEY,
  id_cliente INT NOT NULL,
  id_rutina INT NOT NULL,
  fecha_registro DATE NOT NULL,
  estado_registro ENUM('PENDIENTE','COMPLETADA','NO_REALIZADA') NOT NULL DEFAULT 'COMPLETADA',
  observaciones TEXT NULL,
  creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_cliente_rutina_fecha (id_cliente, id_rutina, fecha_registro),
  CONSTRAINT fk_registro_rutinas_cliente
    FOREIGN KEY (id_cliente) REFERENCES clientes(id_cliente)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_registro_rutinas_rutina
    FOREIGN KEY (id_rutina) REFERENCES rutinas(id_rutina)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

-- =========================================================
-- 6. NUTRICION
-- =========================================================

CREATE TABLE planes_nutricionales (
  id_plan_nutricional INT AUTO_INCREMENT PRIMARY KEY,
  id_cliente INT NOT NULL,
  id_coach INT NULL,
  titulo VARCHAR(150) NOT NULL,
  objetivo TEXT NULL,
  calorias_diarias INT NULL,
  recomendaciones_generales TEXT NULL,
  fecha_inicio DATE NULL,
  fecha_fin DATE NULL,
  estado_nutricional ENUM('ACTIVO','INACTIVO','FINALIZADO') NOT NULL DEFAULT 'ACTIVO',
  creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  actualizado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_nutricional_cliente
    FOREIGN KEY (id_cliente) REFERENCES clientes(id_cliente)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_nutricional_coach
    FOREIGN KEY (id_coach) REFERENCES coaches(id_coach)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE comidas (
  id_comida INT AUTO_INCREMENT PRIMARY KEY,
  id_plan_nutricional INT NOT NULL,
  tipo_comida ENUM('DESAYUNO','MEDIA_MANANA','ALMUERZO','MERIENDA','CENA','SNACK','OTRO') NOT NULL,
  nombre VARCHAR(150) NOT NULL,
  descripcion TEXT NULL,
  hora_sugerida TIME NULL,
  calorias_aprox INT NULL,
  orden INT NOT NULL DEFAULT 1,
  creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  actualizado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_comidas_plan_nutricional
    FOREIGN KEY (id_plan_nutricional) REFERENCES planes_nutricionales(id_plan_nutricional)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

-- =========================================================
-- 7. PROGRESO FISICO
-- =========================================================

CREATE TABLE registros_progreso (
  id_registro_progreso INT AUTO_INCREMENT PRIMARY KEY,
  id_cliente INT NOT NULL,
  registrado_por INT NULL,
  fecha_registro DATE NOT NULL,
  peso_kg DECIMAL(5,2) NULL,
  cintura_cm DECIMAL(5,2) NULL,
  cadera_cm DECIMAL(5,2) NULL,
  pecho_cm DECIMAL(5,2) NULL,
  brazo_cm DECIMAL(5,2) NULL,
  pierna_cm DECIMAL(5,2) NULL,
  foto_url VARCHAR(255) NULL,
  observaciones_cliente TEXT NULL,
  observaciones_coach TEXT NULL,
  creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_progreso_cliente
    FOREIGN KEY (id_cliente) REFERENCES clientes(id_cliente)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_progreso_registrado_por
    FOREIGN KEY (registrado_por) REFERENCES user(id_user)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB;

-- =========================================================
-- 8. AGENDA, SESIONES Y EVENTOS
-- =========================================================

CREATE TABLE sesiones (
  id_sesion INT AUTO_INCREMENT PRIMARY KEY,
  id_coach INT NULL,
  id_cliente INT NULL,
  id_institucion INT NULL,
  id_plan INT NULL,
  titulo VARCHAR(160) NOT NULL,
  descripcion TEXT NULL,
  tipo_sesion ENUM('INDIVIDUAL','GRUPAL','INSTITUCIONAL','EVENTO') NOT NULL DEFAULT 'INDIVIDUAL',
  modalidad ENUM('VIRTUAL','PRESENCIAL','MIXTO') NOT NULL DEFAULT 'VIRTUAL',
  fecha_inicio DATETIME NOT NULL,
  fecha_fin DATETIME NOT NULL,
  ubicacion VARCHAR(180) NULL,
  enlace_virtual VARCHAR(255) NULL,
  cupo_maximo INT NULL,
  estado_sesion ENUM('PROGRAMADA','REALIZADA','CANCELADA') NOT NULL DEFAULT 'PROGRAMADA',
  creado_por INT NULL,
  creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  actualizado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_sesiones_coach
    FOREIGN KEY (id_coach) REFERENCES coaches(id_coach)
    ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT fk_sesiones_cliente
    FOREIGN KEY (id_cliente) REFERENCES clientes(id_cliente)
    ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT fk_sesiones_institucion
    FOREIGN KEY (id_institucion) REFERENCES instituciones(id_institucion)
    ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT fk_sesiones_plan
    FOREIGN KEY (id_plan) REFERENCES planes(id_plan)
    ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT fk_sesiones_creado_por
    FOREIGN KEY (creado_por) REFERENCES user(id_user)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE sesion_participantes (
  id_sesion_participante INT AUTO_INCREMENT PRIMARY KEY,
  id_sesion INT NOT NULL,
  id_cliente INT NOT NULL,
  estado_asistencia ENUM('INSCRITA','ASISTIO','NO_ASISTIO','CANCELADA') NOT NULL DEFAULT 'INSCRITA',
  observaciones TEXT NULL,
  creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_sesion_cliente (id_sesion, id_cliente),
  CONSTRAINT fk_sesion_participantes_sesion
    FOREIGN KEY (id_sesion) REFERENCES sesiones(id_sesion)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_sesion_participantes_cliente
    FOREIGN KEY (id_cliente) REFERENCES clientes(id_cliente)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE eventos (
  id_evento INT AUTO_INCREMENT PRIMARY KEY,
  id_institucion INT NULL,
  titulo VARCHAR(160) NOT NULL,
  descripcion TEXT NULL,
  modalidad ENUM('VIRTUAL','PRESENCIAL','MIXTO') NOT NULL DEFAULT 'PRESENCIAL',
  fecha_inicio DATETIME NOT NULL,
  fecha_fin DATETIME NOT NULL,
  ubicacion VARCHAR(180) NULL,
  enlace_virtual VARCHAR(255) NULL,
  cupo_maximo INT NULL,
  precio DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  imagen_url VARCHAR(255) NULL,
  estado_evento ENUM('PUBLICADO','BORRADOR','FINALIZADO','CANCELADO') NOT NULL DEFAULT 'PUBLICADO',
  creado_por INT NULL,
  creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  actualizado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_eventos_institucion
    FOREIGN KEY (id_institucion) REFERENCES instituciones(id_institucion)
    ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT fk_eventos_creado_por
    FOREIGN KEY (creado_por) REFERENCES user(id_user)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE evento_participantes (
  id_evento_participante INT AUTO_INCREMENT PRIMARY KEY,
  id_evento INT NOT NULL,
  id_cliente INT NULL,
  nombre_invitado VARCHAR(150) NULL,
  correo_invitado VARCHAR(150) NULL,
  telefono_invitado VARCHAR(30) NULL,
  estado_participacion ENUM('INSCRITA','ASISTIO','NO_ASISTIO','CANCELADA') NOT NULL DEFAULT 'INSCRITA',
  creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_evento_participantes_evento
    FOREIGN KEY (id_evento) REFERENCES eventos(id_evento)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_evento_participantes_cliente
    FOREIGN KEY (id_cliente) REFERENCES clientes(id_cliente)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB;

-- =========================================================
-- 9. COMUNICACION: CHAT Y NOTIFICACIONES
-- =========================================================

CREATE TABLE chats (
  id_chat INT AUTO_INCREMENT PRIMARY KEY,
  tipo_chat ENUM('CLIENTE_COACH','GRUPAL','SOPORTE') NOT NULL DEFAULT 'CLIENTE_COACH',
  id_cliente INT NULL,
  id_coach INT NULL,
  id_institucion INT NULL,
  asunto VARCHAR(160) NULL,
  estado_chat ENUM('ACTIVO','CERRADO') NOT NULL DEFAULT 'ACTIVO',
  creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  actualizado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_chats_cliente
    FOREIGN KEY (id_cliente) REFERENCES clientes(id_cliente)
    ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT fk_chats_coach
    FOREIGN KEY (id_coach) REFERENCES coaches(id_coach)
    ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT fk_chats_institucion
    FOREIGN KEY (id_institucion) REFERENCES instituciones(id_institucion)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE mensajes (
  id_mensaje INT AUTO_INCREMENT PRIMARY KEY,
  id_chat INT NOT NULL,
  id_user INT NOT NULL,
  mensaje TEXT NOT NULL,
  archivo_url VARCHAR(255) NULL,
  leido TINYINT(1) NOT NULL DEFAULT 0,
  fecha_lectura DATETIME NULL,
  creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_mensajes_chat
    FOREIGN KEY (id_chat) REFERENCES chats(id_chat)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_mensajes_usuario_emisor
    FOREIGN KEY (id_user) REFERENCES user(id_user)
    ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE notificaciones (
  id_notificacion INT AUTO_INCREMENT PRIMARY KEY,
  id_user INT NOT NULL,
  titulo VARCHAR(160) NOT NULL,
  mensaje TEXT NOT NULL,
  tipo_notificacion ENUM('SISTEMA','PAGO','PLAN','AGENDA','CHAT','EVENTO','PROGRESO') NOT NULL DEFAULT 'SISTEMA',
  url_destino VARCHAR(255) NULL,
  leida TINYINT(1) NOT NULL DEFAULT 0,
  fecha_lectura DATETIME NULL,
  creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_notificaciones_usuario
    FOREIGN KEY (id_user) REFERENCES user(id_user)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

-- =========================================================
-- 10. REPORTES Y TRAZABILIDAD
-- =========================================================

CREATE TABLE reportes_generados (
  id_reporte INT AUTO_INCREMENT PRIMARY KEY,
  id_user INT NULL,
  tipo_reporte ENUM('CLIENTES','PAGOS','SOLICITUDES','PLANES','PROGRESO','EVENTOS','GENERAL') NOT NULL DEFAULT 'GENERAL',
  nombre_reporte VARCHAR(160) NOT NULL,
  parametros_json JSON NULL,
  archivo_url VARCHAR(255) NULL,
  generado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_reportes_usuario
    FOREIGN KEY (id_user) REFERENCES user(id_user)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE bitacora_sistema (
  id_bitacora INT AUTO_INCREMENT PRIMARY KEY,
  id_user INT NULL,
  modulo VARCHAR(80) NOT NULL,
  accion VARCHAR(120) NOT NULL,
  descripcion TEXT NULL,
  ip VARCHAR(45) NULL,
  user_agent VARCHAR(255) NULL,
  creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_bitacora_usuario
    FOREIGN KEY (id_user) REFERENCES user(id_user)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB;

-- =========================================================
-- 11. VISTAS UTILES PARA BACKEND Y PANEL ADMIN
-- =========================================================

CREATE VIEW vw_cupos_planes AS
SELECT
  p.id_plan,
  p.nombre,
  p.slug,
  p.modalidad,
  p.tipo_cliente,
  p.cupo_maximo,
  SUM(CASE WHEN sc.estado_solicitud IN ('PENDIENTE','VALIDADA') THEN 1 ELSE 0 END) AS cupos_reservados,
  CASE
    WHEN p.cupo_maximo IS NULL THEN NULL
    ELSE p.cupo_maximo - SUM(CASE WHEN sc.estado_solicitud IN ('PENDIENTE','VALIDADA') THEN 1 ELSE 0 END)
  END AS cupos_disponibles,
  CASE
    WHEN p.estado_plan = 'INACTIVO' THEN 'INACTIVO'
    WHEN p.cupo_maximo IS NULL THEN 'SIN_LIMITE'
    WHEN p.cupo_maximo - SUM(CASE WHEN sc.estado_solicitud IN ('PENDIENTE','VALIDADA') THEN 1 ELSE 0 END) <= 0 THEN 'CUPO_LLENO'
    WHEN p.cupo_maximo - SUM(CASE WHEN sc.estado_solicitud IN ('PENDIENTE','VALIDADA') THEN 1 ELSE 0 END) <= 5 THEN 'ULTIMOS_CUPOS'
    ELSE 'DISPONIBLE'
  END AS estado_cupo
FROM planes p
LEFT JOIN solicitudes_compra sc ON sc.id_plan = p.id_plan
GROUP BY p.id_plan, p.nombre, p.slug, p.modalidad, p.tipo_cliente, p.cupo_maximo, p.estado_plan;

CREATE VIEW vw_clientes_activos AS
SELECT
  c.id_cliente,
  u.nombres,
  u.apellidos,
  u.documento_identidad,
  u.correo,
  u.telefono,
  c.estado_cliente,
  pc.id_plan_cliente,
  p.nombre AS plan_actual,
  pc.fecha_inicio,
  pc.fecha_fin,
  pc.estado_plan_cliente
FROM clientes c
INNER JOIN user u ON u.id_user = c.id_user
LEFT JOIN planes_cliente pc
  ON pc.id_cliente = c.id_cliente
  AND pc.estado_plan_cliente = 'ACTIVO'
LEFT JOIN planes p ON p.id_plan = pc.id_plan
WHERE c.estado_cliente = 'ACTIVA';

CREATE VIEW vw_solicitudes_pendientes AS
SELECT
  sc.id_solicitud,
  sc.codigo_solicitud,
  CONCAT(sc.nombres, ' ', sc.apellidos) AS nombre_solicitante,
  sc.documento_identidad,
  sc.correo,
  sc.telefono,
  p.nombre AS plan_solicitado,
  sc.estado_solicitud,
  pg.estado_pago,
  pg.monto,
  pg.metodo_pago,
  pg.comprobante_url,
  sc.creado_en
FROM solicitudes_compra sc
INNER JOIN planes p ON p.id_plan = sc.id_plan
LEFT JOIN pagos pg ON pg.id_solicitud = sc.id_solicitud
WHERE sc.estado_solicitud = 'PENDIENTE';

-- =========================================================
-- 12. DATOS BASE DEL SISTEMA
-- =========================================================

INSERT INTO roles (codigo, nombre, descripcion) VALUES
('ADMIN', 'Administrador', 'Gestiona toda la plataforma StayFit.'),
('COACH', 'Coach', 'Gestiona clientes, rutinas, nutricion, progreso y agenda.'),
('CLIENTE', 'Cliente', 'Usuaria o usuario individual con plan activo.'),
('CLIENTE_INSTITUCIONAL', 'Cliente Institucional', 'Cliente vinculado a una institucion.');

INSERT INTO modulos_servicio (codigo, nombre, descripcion) VALUES
('ENTRENAMIENTO', 'Entrenamiento', 'Acceso a planes, rutinas y ejercicios.'),
('NUTRICION', 'Nutricion', 'Acceso a planes nutricionales y comidas.'),
('VIDEOS', 'Videos', 'Acceso a programas virtuales y videos.'),
('SESIONES', 'Sesiones', 'Acceso a sesiones individuales o grupales.'),
('EVENTOS', 'Eventos', 'Acceso a eventos, talleres o actividades.'),
('PROGRESO', 'Progreso', 'Registro y seguimiento de progreso fisico.'),
('AGENDA', 'Agenda', 'Calendario de sesiones, eventos y actividades.'),
('CHAT', 'Chat', 'Comunicacion entre cliente, coach y administracion.');

-- =========================================================
-- 13. REGLAS DE NEGOCIO QUE DEBE APLICAR EL BACKEND
-- =========================================================
-- 1. Una persona NO se convierte en cliente al llenar el formulario.
-- 2. Primero elige plan, verifica cupo, registra datos y pago.
-- 3. Despues se crea una solicitud_compra en estado PENDIENTE.
-- 4. La solicitud PENDIENTE reserva cupo temporalmente.
-- 5. Si el administrador VALIDA:
--    - solicitud_compra.estado_solicitud = 'VALIDADA'
--    - pagos.estado_pago = 'VALIDADO'
--    - crear usuario si no existe
--    - asignar rol CLIENTE o CLIENTE_INSTITUCIONAL
--    - crear registro en clientes
--    - crear registro en planes_cliente
--    - crear accesos en acceso_cliente_modulo segun plan_modulos
-- 6. Si el administrador RECHAZA:
--    - solicitud_compra.estado_solicitud = 'RECHAZADA'
--    - pagos.estado_pago = 'RECHAZADO'
--    - el cupo reservado se libera automaticamente en vw_cupos_planes
-- 7. Si vw_cupos_planes.estado_cupo = 'CUPO_LLENO', el frontend debe bloquear la compra.
-- =========================================================