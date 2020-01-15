<?php
/**
 * Created by PhpStorm.
 * User: minms
 * Date: 2020/1/15
 * Time: 10:44
 */

class TestMessage extends \Minms\RabbitMQ\Message
{
    public function __construct($index, $pushTime)
    {
        parent::__construct(compact('index', 'pushTime'), TestHandler::class);
    }
}