<?php

declare(strict_types=1);

namespace Loom\Spinner\Classes\OS;

class PortGenerator
{
    public function generateRandomPort(): int
    {
        $port = 0;
        $portInUse = true;

        while ($portInUse) {
            $port = rand(49152, 65535);
            $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);

            if (!$socket) {
                throw new \RuntimeException('Failed to create socket: ' . socket_strerror(socket_last_error()));
            }

            if (@socket_bind($socket, '127.0.0.1', $port)) {
                $portInUse = false;
            }

            socket_close($socket);
        }

        return $port;
    }
}