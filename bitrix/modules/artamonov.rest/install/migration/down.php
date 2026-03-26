<?php

use Bitrix\Main\Application;

$settings = require __DIR__ . '/../../settings.php';

Application::getConnection()->queryExecute('DROP TABLE IF EXISTS ' . $settings['config']['table']['request-response']);
Application::getConnection()->queryExecute('DROP TABLE IF EXISTS ' . $settings['config']['table']['request-limit']);
