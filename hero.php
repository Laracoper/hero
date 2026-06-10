#!/usr/bin/env php
<?php

// Подключаем автозагрузку зависимостей Symfony и наших классов [INDEX]
require __DIR__ . '/vendor/autoload.php';

use ComposerHero\Parser;
use ComposerHero\PortChecker;
use ComposerHero\Renderer;

// Инициализируем наши модули [INDEX]
$parser = new Parser();
$portChecker = new PortChecker();
$renderer = new Renderer();

// Ищем docker-compose.yml в текущей папке, где пользователь запустил команду
$composeFile = getcwd() . '/docker-compose.yml'; 

if (!file_exists($composeFile)) {
    echo "\033[0;31m❌ Ошибка: Файл docker-compose.yml не найден в текущей папке!\033[0m\n";
    exit(1);
}

// 1. Парсим файл [INDEX]
$services = $parser->parse($composeFile);

if ($services === false) {
    echo "\033[0;31m❌ Ошибка: Не удалось прочесть файл. Проверьте синтаксис (пробелы и отступы) в конфигурации YAML.\033[0m\n";
    exit(1);
}

if (empty($services)) {
    echo "\033[1;33m⚠️ Предупреждение: В файле конфигурации не найдено ни одного сервиса.\033[0m\n";
    exit(0);
}

// 2. Рисуем сетевую карту [INDEX]
$renderer->renderMap($services);

// 3. Инспектируем порты хоста [INDEX]
$conflicts = $portChecker->checkPorts($services);
$renderer->renderConflicts($conflicts);
