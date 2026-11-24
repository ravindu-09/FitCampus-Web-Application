<?php

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit();
}

require_once __DIR__ . '/../../includes/db_connection.php';
require_once __DIR__ . '/../../models/common/VisitPointsModel.php';

try {
    $model = new VisitPointsModel($pdo);
    $result = $model->finalizeFacilityClosures();
    printf(
        "Closed %d visits; recorded %d penalties; awarded %d points.\n",
        $result['closed'],
        $result['penalties'],
        $result['points']
    );
} catch (Throwable $e) {
    error_log('Facility close finalization failed: ' . $e->getMessage());
    fwrite(STDERR, "Facility close finalization failed.\n");
    exit(1);
}
