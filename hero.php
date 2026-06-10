#!/usr/bin/env php
<?php

require __DIR__ . '/vendor/autoload.php';

use ComposerHero\Parser;
use ComposerHero\PortChecker;
use ComposerHero\Renderer;

$parser = new Parser();
$portChecker = new PortChecker();
$renderer = new Renderer();

// 1. УМНЫЙ АЛГОРИТМ ПОИСКА ФАЙЛА ВВЕРХ ПО ДИРЕКТОРИЯМ
$currentDir = getcwd();
$composeFile = null;

// Мы будем подниматься вверх максимум 5 раз, чтобы не уйти в корень всей ОС Linux
for ($i = 0; $i < 5; $i++) {
    $targetFile = $currentDir . '/docker-compose.yml';
    
    if (file_exists($targetFile)) {
        $composeFile = $targetFile;
        break; // Ура! Файл найден, выходим из цикла
    }
    
    // Поднимаемся на один уровень выше (например, из /hi-d/laravel-app в /hi-d)
    $parentDir = dirname($currentDir);
    
    // Если выше подняться уже физически нельзя (дошли до корня системы /)
    if ($parentDir === $currentDir) {
        break;
    }
    $currentDir = $parentDir;
}

// Если файл так и не нашли
if (!$composeFile) {
    echo "\033[0;31m❌ Ошибка: Файл docker-compose.yml не найден ни в текущей папке, ни в папках выше!\033[0m\n";
    exit(1);
}

// Информируем пользователя, где именно мы нашли файл конфигурации
echo "\033[0;34m🔍 Найдена конфигурация: " . $composeFile . "\033[0m\n";

// 2. Парсим файл
$services = $parser->parse($composeFile);

if ($services === false) {
    echo "\033[0;31m❌ Ошибка: Не удалось прочесть файл. Проверьте синтаксис YAML.\033[0m\n";
    exit(1);
}

if (empty($services)) {
    echo "\033[1;33m⚠️ Предупреждение: В файле конфигурации не найдено ни одного сервиса.\033[0m\n";
    exit(0);
}

// 3. Рисуем сетевую карту
$renderer->renderMap($services);

// 4. Инспектируем порты хоста
$conflicts = $portChecker->checkPorts($services);
$renderer->renderConflicts($conflicts);
