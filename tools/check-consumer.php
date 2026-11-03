<?php

declare(strict_types=1);

// Устанавливаем копию SDK в чистый проект без его пакетов разработки.
require __DIR__ . '/../tests/Support/LocalServer.php';

use ProdumanApi\Tests\Support\LocalServer;

function runCommand(array $command, string $directory): void
{
    $process = proc_open($command, [0 => ['file', '/dev/null', 'r'], 1 => STDOUT, 2 => STDERR], $pipes, $directory);
    if (!is_resource($process)) {
        throw new RuntimeException('Не удалось запустить проверку');
    }
    $code = proc_close($process);
    if (0 !== $code) {
        throw new RuntimeException('Проверка завершилась с кодом ' . $code . ': ' . implode(' ', $command));
    }
}

function copyDirectory(string $source, string $destination): void
{
    mkdir($destination, 0700, true);
    foreach (new FilesystemIterator($source) as $item) {
        $target = $destination . '/' . $item->getFilename();
        if ($item->isDir()) {
            copyDirectory($item->getPathname(), $target);
        } else {
            copy($item->getPathname(), $target);
        }
    }
}

function removeDirectory(string $directory): void
{
    if (!is_dir($directory)) {
        return;
    }
    foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST) as $item) {
        if ($item->isDir() && !$item->isLink()) {
            rmdir($item->getPathname());
        } else {
            unlink($item->getPathname());
        }
    }
    rmdir($directory);
}

$sdkRoot = dirname(__DIR__);
$temporary = sys_get_temp_dir() . '/kassa-consumer-' . bin2hex(random_bytes(8));
$project = $temporary . '/consumer';
$package = $temporary . '/package';
$server = null;
$environment = [];
$exitCode = 0;
try {
    $sdkManifest = json_decode(file_get_contents($sdkRoot . '/composer.json'), true, 512, JSON_THROW_ON_ERROR);
    $httpConstraint = $sdkManifest['require']['symfony/http-client'];
    foreach (array_slice($argv, 1) as $argument) {
        if (str_starts_with($argument, '--http-client=')) {
            $httpConstraint = substr($argument, strlen('--http-client='));
        } elseif (!in_array($argument, ['--latest', '--prefer-lowest', '--run-tests'], true)) {
            throw new InvalidArgumentException('Неизвестный параметр: ' . $argument);
        }
    }
    mkdir($project, 0700, true);
    mkdir($package, 0700);
    copy($sdkRoot . '/composer.json', $package . '/composer.json');
    copyDirectory($sdkRoot . '/src', $package . '/src');
    $manifest = [
        'name' => 'local/kassa-sdk-consumer-check',
        'require' => [
            'php' => $sdkManifest['require']['php'],
            'produman-org/kassa-php' => 'dev-sdk-check',
            'symfony/http-client' => $httpConstraint,
        ],
        'repositories' => [[
            'type' => 'path', 'url' => $package,
            'options' => ['symlink' => false, 'versions' => ['produman-org/kassa-php' => 'dev-sdk-check']],
        ]],
        'config' => ['allow-plugins' => false],
    ];
    $writeManifest = static function () use (&$manifest, $project): void {
        file_put_contents($project . '/composer.json', json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . "\n");
    };
    $writeManifest();
    $command = ['composer', 'update', '--no-interaction', '--no-progress', '--prefer-dist', '--no-plugins', '--no-scripts'];
    if (in_array('--prefer-lowest', $argv, true)) {
        array_push($command, '--prefer-lowest', '--prefer-stable');
    }
    runCommand($command, $project);
    runCommand(['composer', 'check-platform-reqs'], $project);
    runCommand(['composer', 'audit', '--format=plain'], $project);
    $artifacts = getenv('KASSA_TEST_ARTIFACTS');
    if ($artifacts) {
        if (!is_dir($artifacts)) {
            mkdir($artifacts, 0700, true);
        }
        copy($project . '/composer.lock', $artifacts . '/consumer-production.lock');
    }
    foreach (['PRODUMAN_API_URL', 'HTTP_PROXY', 'HTTPS_PROXY', 'ALL_PROXY', 'http_proxy', 'https_proxy', 'all_proxy', 'NO_PROXY', 'no_proxy', 'KASSA_EXPECTED_BUILDER_HASH'] as $name) {
        $environment[$name] = getenv($name);
        putenv($name);
    }
    putenv('NO_PROXY=127.0.0.1,localhost');
    $server = new LocalServer();
    putenv('PRODUMAN_API_URL=' . $server->url . '/kassa');
    putenv('KASSA_EXPECTED_BUILDER_HASH=' . hash_file('sha256', $sdkRoot . '/src/Builder.php'));
    file_put_contents($project . '/verify.php', <<<'CHECK'
<?php
require __DIR__.'/vendor/autoload.php';
function ensure(bool $condition, string $message): void {
    if (!$condition) { throw new RuntimeException($message); }
}
ensure(!class_exists(PHPUnit\Framework\TestCase::class), 'У потребителя оказались require-dev пакеты SDK');
$file = (new ReflectionClass(ProdumanApi\Builder::class))->getFileName();
ensure(str_starts_with(realpath($file), realpath(__DIR__.'/vendor').DIRECTORY_SEPARATOR), 'SDK загружен мимо vendor');
ensure(hash_file('sha256', $file) === getenv('KASSA_EXPECTED_BUILDER_HASH'), 'Установлена другая версия SDK');
$client = ProdumanApi\Builder::buildClient('fixture-token', 'fixture-app', 'fixture-secret');
$position = new ProdumanApi\Request\Orders\Model\PositionModel();
$position->name = 'Товар';
$position->itemCode = '000123';
$position->quantity = 1;
$position->price = 100;
$request = new ProdumanApi\Request\Orders\CreateRequest();
$request->positions = [$position];
ensure($client->orders->create($request)->positions[0]->itemCode === '000123', 'Не сохранился itemCode');
ensure($client->orders->update('order-1', $request)->id === 'order-1', 'Ошибка замены заказа');
ensure($client->orders->get('order-1')->id === 'order-1', 'Ошибка чтения заказа');
ensure($client->orders->list()->items[0]->id === 'order-1', 'Ошибка списка заказов');
$client->orders->delete('order-1');
ensure($client->getLastStatusCode() === 204, 'Ошибка удаления заказа');
$app = ProdumanApi\Builder::buildApplicationClient('fixture-app', 'fixture-secret');
ensure($app->webhooks->endpointCreate(new ProdumanApi\Request\Webhooks\EndpointCreateRequest())->id === 'endpoint-1', 'Ошибка сервисного клиента');
echo 'Чистый потребитель: SDK из vendor, PHP '.PHP_VERSION.', HttpClient '.Composer\InstalledVersions::getPrettyVersion('symfony/http-client').PHP_EOL;
CHECK
    );
    runCommand([PHP_BINARY, $project . '/verify.php'], $project);
    $server->stop();
    $server = null;

    if (in_array('--run-tests', $argv, true)) {
        // Фиксируем все production-версии до добавления PHPUnit.
        $productionLock = json_decode(file_get_contents($project . '/composer.lock'), true, 512, JSON_THROW_ON_ERROR);
        $pinned = [];
        foreach ($productionLock['packages'] as $dependency) {
            $pinned[$dependency['name']] = $dependency['version'];
            $manifest['require'][$dependency['name']] = $dependency['version'];
        }
        $manifest['require-dev'] = ['phpunit/phpunit' => $sdkManifest['require-dev']['phpunit/phpunit']];
        $manifest['autoload-dev'] = ['psr-4' => ['ProdumanApi\\Tests\\' => $sdkRoot . '/tests/']];
        $writeManifest();
        runCommand(['composer', 'update', '--no-interaction', '--no-progress', '--prefer-dist', '--no-plugins', '--no-scripts'], $project);
        $testLock = json_decode(file_get_contents($project . '/composer.lock'), true, 512, JSON_THROW_ON_ERROR);
        $installed = array_column(array_merge($testLock['packages'], $testLock['packages-dev']), 'version', 'name');
        foreach ($pinned as $name => $version) {
            if (($installed[$name] ?? null) !== $version) {
                throw new RuntimeException('Изменилась зафиксированная production-зависимость: ' . $name);
            }
        }
        $testCommand = [PHP_BINARY, '-d', 'error_reporting=-1', $project . '/vendor/bin/phpunit', '--configuration', $sdkRoot . '/phpunit.xml.dist', '--bootstrap', $project . '/vendor/autoload.php'];
        if ($artifacts) {
            copy($project . '/composer.lock', $artifacts . '/consumer-tests.lock');
            array_push($testCommand, '--log-junit', $artifacts . '/consumer-junit.xml');
        }
        runCommand($testCommand, $project);
    }
} catch (Throwable $error) {
    fwrite(STDERR, $error->getMessage() . "\n");
    $exitCode = 1;
} finally {
    $server?->stop();
    foreach ($environment as $name => $value) {
        putenv(false === $value ? $name : $name . '=' . $value);
    }
    removeDirectory($temporary);
}
exit($exitCode);
