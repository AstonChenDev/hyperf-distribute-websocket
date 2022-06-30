<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace Aston\DistributeWs;

use Aston\DistributeWs\Contract\ISender;
use Aston\DistributeWs\Contract\ISocketClientService;
use Aston\DistributeWs\Implement\Sender;
use Aston\DistributeWs\Implement\SocketClientService;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [
                ISocketClientService::class => SocketClientService::class,
                ISender::class => Sender::class,
            ],
            'annotations' => [
                'scan' => [
                    'paths' => [
                        __DIR__,
                    ],
                ],
            ],
            'publish' => [
                [
                    'id' => 'config',
                    'description' => 'The config for distribute ws.',
                    'source' => __DIR__ . '/../publish/distribute_ws.php',
                    'destination' => BASE_PATH . '/config/autoload/distribute_ws.php',
                ],
            ],
        ];
    }
}
