<?php

declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';

class UsuarioModel extends StayFitModel
{
    protected string $table = 'users';
    protected string $primaryKey = 'id_usuario';
    protected array $fillable = ['nombre', 'apellido', 'correo', 'hash_contrasena', 'estado', 'origen_registro', 'foto_perfil', 'telefono'];

    public function activos(int $limit = 100): array
    {
        foreach (['activo', 'estado', 'estado_plan', 'estado_pago'] as $column) {
            if (in_array($column, sf_columns($this->table), true)) {
                return $this->where($column, in_array($column, ['activo']) ? 1 : 'ACTIVO', $limit);
            }
        }
        return $this->all($limit);
    }
}
