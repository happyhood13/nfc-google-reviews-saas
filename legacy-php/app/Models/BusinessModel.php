<?php
/**
 * ==============================================================================
 * SaaS NFC & QR Reviews - Modelo de Negocio (Acceso a Datos Seguro)
 * ==============================================================================
 * Implementación estricta de Prepared Statements (PDO) para mitigar SQL Injection (OWASP A03).
 */

declare(strict_types=1);

namespace App\Models;

use App\Config\Database;
use PDO;

class BusinessModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /**
     * Obtiene el listado completo de negocios con filtro de búsqueda opcional
     */
    public function getAll(?string $search = null, ?string $status = null): array
    {
        $sql = "SELECT id, nombre_negocio, telefono, logo_path, google_maps_url, qr_code_path, slug, total_scans, status, created_at 
                FROM negocios 
                WHERE 1=1";
        $params = [];

        if ($search !== null && trim($search) !== '') {
            $sql .= " AND (nombre_negocio LIKE :search OR telefono LIKE :search)";
            $params[':search'] = '%' . trim($search) . '%';
        }

        if ($status !== null && in_array($status, ['activo', 'inactivo'], true)) {
            $sql .= " AND status = :status";
            $params[':status'] = $status;
        }

        $sql .= " ORDER BY id DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    /**
     * Obtiene un negocio por su ID primario
     */
    public function getById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM negocios WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    /**
     * Obtiene un negocio por su Slug único (usado para la redirección NFC)
     */
    public function getBySlug(string $slug): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM negocios WHERE slug = :slug LIMIT 1");
        $stmt->execute([':slug' => $slug]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    /**
     * Comprueba si un slug ya existe en la base de datos
     */
    public function slugExists(string $slug, ?int $excludeId = null): bool
    {
        $sql = "SELECT COUNT(*) FROM negocios WHERE slug = :slug";
        $params = [':slug' => $slug];

        if ($excludeId !== null) {
            $sql .= " AND id != :excludeId";
            $params[':excludeId'] = $excludeId;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return ((int) $stmt->fetchColumn()) > 0;
    }

    /**
     * Inserta un nuevo negocio usando Prepared Statements
     */
    public function create(array $data): int
    {
        $sql = "INSERT INTO negocios (
                    nombre_negocio, 
                    telefono, 
                    logo_path, 
                    google_maps_url, 
                    qr_code_path, 
                    slug, 
                    total_scans, 
                    status, 
                    created_at
                ) VALUES (
                    :nombre_negocio, 
                    :telefono, 
                    :logo_path, 
                    :google_maps_url, 
                    :qr_code_path, 
                    :slug, 
                    :total_scans, 
                    :status, 
                    NOW()
                )";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':nombre_negocio'   => $data['nombre_negocio'],
            ':telefono'         => $data['telefono'],
            ':logo_path'        => $data['logo_path'] ?? null,
            ':google_maps_url'  => $data['google_maps_url'],
            ':qr_code_path'     => $data['qr_code_path'],
            ':slug'             => $data['slug'],
            ':total_scans'      => $data['total_scans'] ?? 0,
            ':status'           => $data['status'] ?? 'activo'
        ]);

        return (int) $this->db->lastInsertId();
    }

    /**
     * Incrementa atómicamente el contador de visitas / taps NFC
     */
    public function incrementScans(int $id): bool
    {
        $stmt = $this->db->prepare("UPDATE negocios SET total_scans = total_scans + 1 WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    /**
     * Elimina un negocio por ID
     */
    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM negocios WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    /**
     * Obtiene métricas generales para las tarjetas de estadísticas del Dashboard
     */
    public function getStats(): array
    {
        $totalBusinesses = (int) $this->db->query("SELECT COUNT(*) FROM negocios")->fetchColumn();
        $totalScans      = (int) $this->db->query("SELECT COALESCE(SUM(total_scans), 0) FROM negocios")->fetchColumn();
        $activeBusinesses= (int) $this->db->query("SELECT COUNT(*) FROM negocios WHERE status = 'activo'")->fetchColumn();

        $latestStmt = $this->db->query("SELECT nombre_negocio, created_at FROM negocios ORDER BY id DESC LIMIT 1");
        $latest = $latestStmt->fetch();

        return [
            'total_businesses'  => $totalBusinesses,
            'total_scans'       => $totalScans,
            'active_businesses' => $activeBusinesses,
            'latest_business'   => $latest ? $latest['nombre_negocio'] : 'Ninguno aún',
            'latest_date'       => $latest ? $latest['created_at'] : null
        ];
    }
}
