## PHP RABBITMQ 快捷脚手架

用于快捷搭建基于RabbitMQ的消息处理队列, 支持实时队列, 延迟队列

#### 运行案例
![demo](https://raw.githubusercontent.com/minms/rabbitmq/master/test/test.jpg)


#### 使用方法 
*安装*
```
composer require minms/rabbitmq
```

*第一步 定义消息结构*

```
# 创建消息结构

class TestMessage extends \Minms\RabbitMQ\Message
{
    public function __construct($index, $pushTime)
    {
        parent::__construct(compact('index', 'pushTime'), TestHandler::class);
    }
}
```

```
# 创建消息对应消费方法

class TestHandler extends \Minms\RabbitMQ\Handler
{
    public function run()
    {
        echo date('Y-m-d H:i:s')." 队列消息消费: pushTime: ". $this->getBody("pushTime") . ", index: ".$this->getBody("index")." \n";
    }
}
```

*第二步 监听队列消息*
```
# 配置RabbitMQ连接信息
\Minms\RabbitMQ\Config::setConfig('192.168.2.186', 5672, 'guest', 'guest', '/');

# 监听队列默认有个Listen帮助类, 也可以参考进行重写, !! 需要在callback里面处理异常情况
\Minms\RabbitMQ\Listen::callback(function (\PhpAmqpLib\Message\AMQPMessage $message){
    $data = json_decode($message->getBody(), true);

    //todo, 自定义处理

    /** @var \Minms\RabbitMQ\Handler $handlerClassInstance */
    $handlerClassInstance = new $data['handler']($data['params'], $data['message']);

    /** 执行任务 */
    $handlerClassInstance->run();
});
```

*第三步 推送到RabbitMQ*

```
# 需要提前定义RabbitMQ配置, 只需要配置一次
\Minms\RabbitMQ\Config::setConfig('192.168.2.186', 5672, 'guest', 'guest', '/');
# 覆盖配置
# (new TestMessage(1, date('Y-m-d H:i:s')))->setConfig('192.168.2.186', 5672, 'guest', 'guest', '/')->publish();

echo "推送消息(index: 1)到队列: ".date('Y-m-d H:i:s')." \n";
(new TestMessage(1, date('Y-m-d H:i:s')))->publish();

sleep(1);
echo "推送延迟消息(index: 2)到队列: ".date('Y-m-d H:i:s')." \n";
(new TestMessage(2, date('Y-m-d H:i:s')))->publishDelay(30);

sleep(2);
echo "推送延迟消息(index: 3)到队列: ".date('Y-m-d H:i:s')." \n";
(new TestMessage(3, date('Y-m-d H:i:s')))->publishDelay(30);
```
