-- Enlaces de registro público por institución (MVP cliente institucional)
CREATE TABLE IF NOT EXISTS enlaces_registro_institucional (
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
