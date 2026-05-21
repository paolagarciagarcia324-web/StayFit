<?php

declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';

class ProgramaVirtualModel extends StayFitModel
{
    protected string $table = 'programa_virtual';
    protected string $primaryKey = 'id_programa_virtual';
    protected array $fillable = ['id_plan', 'nombre', 'descripcion', 'nivel', 'activo'];

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
