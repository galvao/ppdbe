<?php

declare(strict_types=1);

use Mezzio\Application;
use Mezzio\MiddlewareFactory;
use Psr\Container\ContainerInterface;

return static function (Application $app, MiddlewareFactory $factory, ContainerInterface $container): void {
    $app->get('/', App\Handler\HomePageHandler::class, 'home');
    $app->post('/transfer', App\Handler\TransferHandler::class, 'transfer');
};
