<?php

namespace ComposerHero;

class PortChecker
{
    /**
     * Проверяет массив портов на внешние и внутренние конфликты
     */
    public function checkPorts(array $services)
    {
        $conflicts = [];
        $seenPorts = [];

        foreach ($services as $serviceName => $data) {
            foreach ($data['ports'] as $portInfo) {
                $hostPort = (int)$portInfo['host'];

                if ($hostPort <= 0) {
                    continue;
                }

                // 1. ПРОВЕРКА НА ВНУТРЕННИЕ ДУБЛИКАТЫ (Внутри файла конфигурации)
                if (isset($seenPorts[$hostPort])) {
                    $conflicts[] = [
                        'type' => 'internal',
                        'service' => $serviceName,
                        'conflict_with' => $seenPorts[$hostPort],
                        'port' => $hostPort
                    ];
                } else {
                    $seenPorts[$hostPort] = $serviceName;
                }

                // 2. ПРОВЕРКА НА ВНЕШНИЕ КОНФЛИКТЫ (Занято системой)
                if ($this->isPortInUse($hostPort)) {
                    $conflicts[] = [
                        'type' => 'external',
                        'service' => $serviceName,
                        'port' => $hostPort
                    ];
                }
            }
        }

        return $conflicts;
    }

    /**
     * Проверяет конкретный порт на компьютере через сетевой сокет
     */
    private function isPortInUse($port)
    {
        $connection = @fsockopen('127.0.0.1', $port, $errno, $errstr, 0.1);

        if (is_resource($connection)) {
            fclose($connection);
            return true;
        }

        return false;
    }
}
