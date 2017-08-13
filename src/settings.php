<?php
return [
    'settings' => [
        'prod' => true,
        'displayErrorDetails' => true, // set to false in production
        'addContentLengthHeader' => false, // Allow the web server to send the content-length header

        // Renderer settings
        'renderer' => [
            'cache_path' => __DIR__ . '/../cache/',
            'template_path' => __DIR__ . '/../templates/',
        ],

        // Monolog settings
        'logger' => [
            'name' => 'slim-app',
            'path' => __DIR__ . '/../logs/app.log',
            'level' => \Monolog\Logger::DEBUG,
        ],

        'database' => \Symfony\Component\Yaml\Yaml::parse(file_get_contents(__DIR__ . '/db.yaml'))['database'],
        'databaseMessage' => \Symfony\Component\Yaml\Yaml::parse(file_get_contents(__DIR__ . '/db.yaml'))['databaseMessage'],
    ],
];
