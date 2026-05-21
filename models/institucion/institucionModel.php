<?php

declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';

class InstitucionModel extends StayFitModel
{
    protected string $table = 'institucion';
    protected string $primaryKey = 'id_institucion';
    protected array $fillable = ['nombre', 'tipo_institucion', 'nit', 'direccion', 'telefono', 'correo_contacto', 'num_participantes', 'activo'];

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
