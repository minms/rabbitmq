<?php
/**
 * Created by PhpStorm.
 * User: minms
 * Date: 2020/1/15
 * Time: 10:47
 */

require '../vendor/autoload.php';
require './queue/TestMessage.php';
require './queue/TestHandler.php';



\Minms\RabbitMQ\Config::setConfig('192.168.2.186', 5672, 'guest', 'guest', '/');

\Minms\RabbitMQ\Listen::callback(function (\PhpAmqpLib\Message\AMQPMessage $message){
    $data = json_decode($message->getBody(), true);

    //todo, 自定义处理

    /** @var \Minms\RabbitMQ\Handler $handlerClassInstance */
    $handlerClassInstance = new $data['handler']($data['params'], $data['message']);

    /** 执行任务 */
    $handlerClassInstance->run();
});