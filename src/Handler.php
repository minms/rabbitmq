<?php
/**
 * Created by PhpStorm.
 * User: minms
 * Date: 2020/1/14
 * Time: 18:07
 */

namespace Minms\RabbitMQ;

abstract class Handler
{
    /**
     * @var null | mixed
     */
    private $_body;

    /**
     * @var null|Message
     */
    private $_message;

    public function __construct($body = null, $message = null)
    {
        $this->_body = $body;

        if(!class_exists($message)){
            return;
        }

        $class = new \ReflectionClass($message);
        // 获得构造函数
        $construct = $class->getConstructor();
        // 没有构造函数的话，就可以直接 new 本类型了
        if (is_null($construct))
        {
            $this->_message = new $message();
            return ;
        }

        $this->_message = $class->newInstanceArgs($body);
    }

    /**
     * @return Message
     */
    public function getMessage()
    {
        return $this->_message;
    }

    /**
     * 获取传入的参数
     * @param $key
     * @return mixed|null
     */
    public function getBody($key)
    {
        return $this->getMessage()->$key;
    }

    abstract public function run();
}