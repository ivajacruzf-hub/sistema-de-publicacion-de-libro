<?php
require_once __DIR__ . '/../config/conexionDB.php';

class DashboardController
{
    public function getStats(): void
    {
        try {
            $stats = ConexionPDO::query(
                "SELECT
                    (SELECT COUNT(*) FROM libro WHERE estado_publicacion = 'Publicado') AS libros_publicados,
                    (SELECT COUNT(*) FROM usuarios WHERE rol = 'Autor') AS autores_registrados,
                    (SELECT COUNT(*) FROM usuarios WHERE rol = 'Lector') AS lectores,
                    (SELECT COUNT(*) FROM libro WHERE estado_publicacion = 'En Revisión') AS pendientes"
            );

            if (!isset($stats[0])) {
                throw new RuntimeException('La consulta de indicadores no devolvió resultados');
            }

            echo json_encode(array_map('intval', $stats[0]), JSON_UNESCAPED_UNICODE);
        } catch (Throwable $error) {
            error_log('Error al consultar indicadores del panel: ' . $error);
            http_response_code(500);
            echo json_encode([
                'error' => 'No se pudieron consultar los indicadores. ' . ConexionPDO::connectionHint($error)
            ], JSON_UNESCAPED_UNICODE);
        }
    }
}
