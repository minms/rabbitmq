<?php
/**
 * Created by PhpStorm.
 * User: minms
 * Date: 2020/1/15
 * Time: 10:45
 */

class TestHandler extends \Minms\RabbitMQ\Handler
{
    public function run()
    {
        echo date('Y-m-d H:i:s')." 队列消息消费: pushTime: ". $this->getBody("pushTime") . ", index: ".$this->getBody("index")." \n";
    }
}