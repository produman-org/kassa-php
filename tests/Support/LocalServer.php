<?php

declare(strict_types=1);

namespace ProdumanApi\Tests\Support;

final class LocalServer
{
    private array $processes = [];
    private array $markers = [];
    public readonly string $directory;
    public readonly int $port;
    public readonly string $url;

    public function __construct()
    {
        $this->directory = sys_get_temp_dir() . '/kassa-http-' . bin2hex(random_bytes(8));
        if (!mkdir($this->directory, 0700)) {
            throw new \RuntimeException('Не удалось создать каталог HTTP-теста');
        }
        $this->port = (int) (getenv('KASSA_TEST_PORT_BASE') ?: 42100);
        $this->url = 'http://127.0.0.1:' . $this->port;
        try {
            $this->start($this->port, 'origin');
            $this->start($this->port + 1, 'capture');
        } catch (\Throwable $error) {
            $this->stop();
            throw $error;
        }
    }

    private function start(int $port, string $role): void
    {
        $socket = @stream_socket_server('tcp://127.0.0.1:' . $port);
        if (false === $socket) {
            throw new \RuntimeException('Порт HTTP-теста занят: ' . $port);
        }
        fclose($socket);
        $marker = bin2hex(random_bytes(8));
        $this->markers[$role] = $marker;
        $environment = array_merge(getenv(), [
            'KASSA_FIXTURE_DIRECTORY' => $this->directory,
            'KASSA_FIXTURE_ROLE' => $role,
            'KASSA_FIXTURE_MARKER' => $marker,
            'KASSA_FIXTURE_CAPTURE_PORT' => (string) ($this->port + 1),
        ]);
        $log = $this->directory . '/' . $role . '.log';
        $process = proc_open([PHP_BINARY, '-S', '127.0.0.1:' . $port, __DIR__ . '/../Fixtures/router.php'], [
            0 => ['file', '/dev/null', 'r'],
            1 => ['file', $log, 'a'],
            2 => ['file', $log, 'a'],
        ], $pipes, null, $environment);
        if (!is_resource($process)) {
            throw new \RuntimeException('Не удалось запустить HTTP-сервер');
        }
        $this->processes[] = $process;
        $context = stream_context_create(['http' => ['timeout' => 0.2, 'ignore_errors' => true]]);
        $deadline = microtime(true) + 5;
        do {
            if ($marker === @file_get_contents('http://127.0.0.1:' . $port . '/health', false, $context)) {
                return;
            }
            if (!proc_get_status($process)['running']) {
                break;
            }
            usleep(20000);
        } while (microtime(true) < $deadline);

        throw new \RuntimeException('HTTP-сервер не готов: ' . @file_get_contents($log));
    }

    public function requests(string $role = 'origin'): array
    {
        $file = $this->directory . '/' . $role . '.requests';
        if (!is_file($file)) {
            return [];
        }

        return array_map(static fn ($line) => json_decode($line, true, 512, JSON_THROW_ON_ERROR), file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES));
    }

    public function waitForPendingRequests(): void
    {
        // Однопоточный сервер обработает этот барьер после уже отправленных запросов,
        // в том числе повторов, ожидающих завершения блокирующего обработчика.
        $context = stream_context_create(['http' => ['timeout' => 8, 'ignore_errors' => true]]);
        $marker = @file_get_contents($this->url . '/health', false, $context);
        if ($this->markers['origin'] !== $marker) {
            throw new \RuntimeException('HTTP-сервер не обработал очередь запросов за отведённое время');
        }
    }

    public function stop(): void
    {
        foreach ($this->processes as $process) {
            if (is_resource($process)) {
                proc_terminate($process);
                proc_close($process);
            }
        }
        $this->processes = [];
        foreach (glob($this->directory . '/*') ?: [] as $file) {
            unlink($file);
        }
        if (is_dir($this->directory)) {
            rmdir($this->directory);
        }
    }
}
