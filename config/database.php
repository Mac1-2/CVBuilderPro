<?php
return [
    'host' => '127.0.0.1',
    'port' => 3306,
    'database' => 'cv_builder',
    'username' => 'cv_builder',
    'password' => 'cv_builder_pass_2024',
    'charset' => 'utf8mb4',
    'options' => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]
];
