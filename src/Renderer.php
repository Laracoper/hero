<?php

namespace ComposerHero;

class Renderer
{
    // Цветовые коды Bash для красивого оформления [INDEX]
    private const GREEN = "\033[0;32m";
    private const CYAN = "\033[0;36m";
    private const YELLOW = "\033[1;33m";
    private const RED = "\033[0;31m";
    private const RESET = "\033[0m";
    private const BOLD = "\033[1;37m";

    /**
     * Рисует визуальную карту сети контейнеров
     */
    public function renderMap(array $services)
    {
        echo self::CYAN . "\n=== 🗺️ СЕТЕВАЯ КАРТА ПРОЕКТА DOCKER COMPOSE ===\n" . self::RESET;

        if (empty($services)) {
            echo self::YELLOW . "В файле конфигурации не найдено активных сервисов.\n" . self::RESET;
            return;
        }

        foreach ($services as $name => $data) {
            // Формируем блок контейнера
            $containerBlock = self::GREEN . "[ " . self::BOLD . sprintf("%-10s", $name) . self::GREEN . " ]" . self::RESET;
            echo "\n" . $containerBlock;

            // Выводим информацию о портах рядом с контейнером [INDEX]
            if (!empty($data['ports'])) {
                foreach ($data['ports'] as $port) {
                    echo " ──(" . self::YELLOW . "хост:" . $port['host'] . " ➔ конт:" . $port['container'] . self::RESET . ")";
                }
            }

            // Выводим связи стрелочками вниз
            if (!empty($data['depends_on'])) {
                echo "\n";
                foreach ($data['depends_on'] as $dependency) {
                    echo "    └───➔ " . self::CYAN . $dependency . self::RESET . "\n";
                }
            } else {
                echo "\n";
            }
        }
        echo self::CYAN . "==============================================\n\n" . self::RESET;
    }

    /**
     * Выводит предупреждения о конфликтах портов
     */
    /**
     * Выводит предупреждения о конфликтах портов
     */
    public function renderConflicts(array $conflicts)
    {
        if (empty($conflicts)) {
            echo self::GREEN . "✅ Проверка портов пройдена! Конфликтов не обнаружено.\n\n" . self::RESET;
            return true;
        }

        echo self::RED . "⚠️ ВНИМАНИЕ! ОБНАРУЖЕНЫ КОНФЛИКТЫ ПОРТОВ:\n" . self::RESET;

        foreach ($conflicts as $conflict) {
            if ($conflict['type'] === 'internal') {
                echo self::YELLOW . " • Ошибка дублирования: Сервисы " . self::BOLD . "[" . $conflict['service'] . "]" . self::YELLOW .
                    " и " . self::BOLD . "[" . $conflict['conflict_with'] . "]" . self::YELLOW .
                    " одновременно используют один порт хоста " . self::RED . $conflict['port'] . self::YELLOW . "!\n" . self::RESET;
            } else {
                echo self::YELLOW . " • Внешний конфликт: Порт " . self::RED . $conflict['port'] . self::YELLOW .
                    ", требуемый сервисом " . self::BOLD . "[" . $conflict['service'] . "]" . self::YELLOW .
                    ", уже занят другой запущенной программой на вашем ПК!\n" . self::RESET;
            }
        }

        echo self::RED . "\n👉 Рекомендация: Измените порты хоста в вашем файле конфигурации, чтобы они не пересекались.\n\n" . self::RESET;
        return false;
    }
}
