<?php

namespace ComposerHero;

use Symfony\Component\Yaml\Yaml;

class Parser
{
    /**
     * Читает и парсит файл docker-compose.yml в удобный массив данных
     */
    public function parse($filePath)
    {
        if (!file_exists($filePath)) {
            return null;
        }

        try {
            // Используем библиотеку Symfony для парсинга YAML-текста в PHP-массив
            $config = Yaml::parseFile($filePath);
        } catch (\Exception $e) {
            // Если в файле синтаксическая ошибка (например, уехала запятая или пробел)
            return false;
        }

        // Проверяем, есть ли в файле вообще хоть какие-то сервисы
        if (!isset($config['services']) || !is_array($config['services'])) {
            return [];
        }

        $parsedServices = [];

        foreach ($config['services'] as $name => $details) {
            $parsedServices[$name] = [
                'ports' => $this->extractPorts($details),
                'depends_on' => $this->extractDependencies($details)
            ];
        }

        return $parsedServices;
    }

    /**
     * Вытаскивает порты контейнера и разделяет их на Внешний и Внутренний
     */
    private function extractPorts($details)
    {
        if (!isset($details['ports']) || !is_array($details['ports'])) {
            return [];
        }

        $portsList = [];
        foreach ($details['ports'] as $portMapping) {
            // Бывает формат "8081:80", а бывает "127.0.0.1:8081:80"
            // Нам нужно вытащить именно ту часть, которая относится к хосту и контейнеру
            $parts = explode(':', $portMapping);
            
            if (count($parts) === 2) {
                // Формат "8081:80"
                $portsList[] = [
                    'host' => trim($parts[0]),
                    'container' => trim($parts[1])
                ];
            } elseif (count($parts) === 3) {
                // Формат "127.0.0.1:8081:80"
                $portsList[] = [
                    'host' => trim($parts[1]),
                    'container' => trim($parts[2])
                ];
            }
        }

        return $portsList;
    }

    /**
     * Вытаскивает связи контейнера (от кого он зависит)
     */
    private function extractDependencies($details)
    {
        if (!isset($details['depends_on'])) {
            return [];
        }

        // depends_on может быть простым массивом ['db', 'redis']
        // или сложным ассоциативным массивом с условиями condition: service_healthy
        if (is_array($details['depends_on'])) {
            if (isset($details['depends_on'][0])) {
                return $details['depends_on'];
            }
            return array_keys($details['depends_on']);
        }

        return [];
    }
}
