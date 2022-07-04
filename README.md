## Hyperf 分布式 websocket 解决方案

场景：

1、多个 websocket server
2、客户端 clientA 连接到 serverA 得到 fdA, clientB 连接到 serverB 得到 fdB
3、clientA 想要给 clientB 发送消息

注：fd 类似于当前链接的文件描述符，每一次新的连接会自增 1 ，可以理解为连接号

问题：clientA 和 clientB 连接到的是不同的服务器，fd 作用域仅限于当前服务器，要想跨服务器想实现通讯，需要借助中间件来传递消息

环境：

php >=7.2
hyperf >= 2.2

### 理论参考

[Hyperf搭建websocket集群项目（通过redis发布订阅）](https://learnku.com/articles/69165)

## 安装

使用 composer

```
composer require aston/distribute-ws
```

发布配置文件

```
php bin/hyperf.php vendor:publish aston/distribute-ws
```

## 配置文件说明

```php
[
    'user_relate_fd_key' => 'user:relate:fd:%s',//用户ID与分布式FD关联key
    'fd_relate_user_key' => 'fd:relate:user:%s',//分布式FD与用户ID关联key
    'ttl' => 7200,//key的过期时间
    'default_opcode' => WEBSOCKET_OPCODE_BINARY,//默认消息类型 发送时也可传参指定,
    'driver' => QueueDriver::class,// 可选择 Aston\DistributeWs\Driver\QueueDriver::class 异步队列 |  Aston\DistributeWs\Driver\SubscribeDriver::class 发布订阅
    'queue_config' => [
        'process_num' => env('LOCAL_PUSH_PROCESS_NUM', 1),//消费队列进程数量
        'process_concurrent_limit' => env('LOCAL_PUSH_PROCESS_CONCURRENT_LIMIT', 10)//消费队列同时处理消息数
    ],
    'server_id' => env('DISTRIBUTE_SERVER_ID', uniqid()),//服务器ID，分布式部署时保证每台服务器的SERVER_ID不同即可
]
```

## 使用方法演示

[根据hyperf官方文档搭建好websocket服务器](https://hyperf.wiki/2.2/#/zh-cn/websocket-server)

在控制器中注入本服务

```php
<?php
declare(strict_types=1);

namespace App\Controller;

use Aston\DistributeWs\Contract\ISender;
use Aston\DistributeWs\Contract\ISocketClientService;
use Hyperf\Contract\OnCloseInterface;
use Hyperf\Contract\OnMessageInterface;
use Hyperf\Contract\OnOpenInterface;
use Hyperf\Di\Annotation\Inject;
use Hyperf\WebSocketServer\Context;
use Swoole\Http\Request;
use Swoole\Websocket\Frame;

class WebSocketController implements OnMessageInterface, OnOpenInterface, OnCloseInterface
{
    /**
         * @Inject()
         * @var ISocketClientService
         */
    protected ISocketClientService $socketClientService;

    /**
         * @Inject()
         * @var ISender
         */
    protected ISender $sender;

    public function onOpen($server, Request $request): void
    {
        $uid = (int)$request->get['uid'];
        $server->push($request->fd, 'Opened');
        //绑定fd与用户关系
        $this->socketClientService->bindRelation($request->fd, $uid);
    }

    public function onMessage($server, Frame $frame): void
    {
        $data = json_decode($frame->data, true);
        $uid = (int)$data['uid'];
        $text = $data['data'];

        $distribute_fd = $this->socketClientService->findUserFd($uid);
        if (!$distribute_fd) {
            $server->push($frame->fd, 'not exist');
            return;
        }
        //向这个fd单独推送消息
        $distribute_fd->send($text);
        $distribute_fd->send($text, WEBSOCKET_OPCODE_TEXT);
        //向这个uid单独推送消息
        $this->sender->send($uid, $text);
        $this->sender->send($uid, $text, WEBSOCKET_OPCODE_TEXT);
        //向多个用户发送同一条消息
        $this->sender->sendMulti([$uid, (int)Context::get('uid')], $text);
        $this->sender->sendMulti([$uid, (int)Context::get('uid')], $text, WEBSOCKET_OPCODE_TEXT);
        //向所有服务器的所有客户端推送消息
        $this->sender->sendAll($text);
        $this->sender->sendAll($text, WEBSOCKET_OPCODE_TEXT);
    }

    public function onClose($server, int $fd, int $reactorId): void
    {
         // 解除分布式fd与用户绑定关系
         $this->socketClientService->removeRelation($this->socketClientService->genDistributeFd($fd)->toString());
    }
}


```

#### 注意的点：由于redis的subscribe方法是阻塞的，框架启动时会自动启动一个自定义进程，该进程只负责订阅和回调，不影响其他进程，收到订阅消息后执行回调即可

# 验证：

### 本地开启两个不同端口的 server

配置 env

HTTP_PORT=9501
WS_PORT=9502
DISTRIBUTE_SERVER_ID=server1

启动第一个服务器

修改 env

HTTP_PORT=9503
WS_PORT=9504
DISTRIBUTE_SERVER_ID=server2

启动第二个服务器

写一个简易的view当作websocket客户端

我这里的demo是想要实现给指定的uid发送消息

然后分别连接到两个不同的websocket服务器

# 效果演示

![Hyperf搭建websocket集群项目（通过redis发布订阅）](https://cdn.learnku.com/uploads/images/202206/29/100058/FxmhpgPEcv.gif!large)