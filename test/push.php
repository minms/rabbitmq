<?php
/**
 * Created by PhpStorm.
 * User: minms
 * Date: 2020/1/15
 * Time: 10:43
 */

require '../vendor/autoload.php';
require './queue/TestMessage.php';
require './queue/TestHandler.php';



\Minms\RabbitMQ\Config::setConfig('192.168.2.186', 5672, 'guest', 'guest', '/');

echo "推送消息(index: 1)到队列: ".date('Y-m-d H:i:s')." \n";
(new TestMessage(1, date('Y-m-d H:i:s')))->publish();

sleep(1);
echo "推送延迟消息(index: 2)到队列: ".date('Y-m-d H:i:s')." \n";
(new TestMessage(2, date('Y-m-d H:i:s')))->publishDelay(30);

sleep(2);
echo "推送延迟消息(index: 3)到队列: ".date('Y-m-d H:i:s')." \n";
(new TestMessage(3, date('Y-m-d H:i:s')))->publishDelay(30);