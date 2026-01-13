<?php
declare(strict_types=1);

require_once __DIR__ . '/env.php';
require_once __DIR__ . '/helpers.php';

//env_load(project_root());
env_load(dirname(__DIR__));


session_name(env('SESSION_NAME', 'dp71_session'));
session_start();

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/simulado.php';
require_once __DIR__ . '/metrics.php';


migrate_if_needed();
