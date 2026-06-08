<?php

/**
 * Resuelve nombres de tablas entre esquema legacy y esquema actual (stayfit).
 */
class SchemaHelper
{
    private PDO $db;

    /** @var array<string, bool> */
    private static array $cacheTablas = [];

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function tablaExiste(string $nombre): bool
    {
        if (array_key_exists($nombre, self::$cacheTablas)) {
            return self::$cacheTablas[$nombre];
        }

        try {
            $stmt = $this->db->query('SHOW TABLES LIKE ' . $this->db->quote($nombre));
            self::$cacheTablas[$nombre] = (bool) $stmt->fetch(PDO::FETCH_NUM);
        } catch (PDOException $e) {
            self::$cacheTablas[$nombre] = false;
        }

        return self::$cacheTablas[$nombre];
    }

    public function usaEsquemaNuevo(): bool
    {
        return $this->tablaExiste('clientes') || $this->tablaExiste('user');
    }

    public function tabla(string $nueva, string $legacy): string
    {
        if ($this->tablaExiste($nueva)) {
            return $nueva;
        }

        if ($this->tablaExiste($legacy)) {
            return $legacy;
        }

        return $nueva;
    }
}
