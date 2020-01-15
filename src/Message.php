<?php
/**
 * Created by PhpStorm.
 * User: minms
 * Date: 2020/1/14
 * Time: 18:06
 */

namespace Minms\RabbitMQ;

use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Exchange\AMQPExchangeType;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Wire\AMQPTable;

class Message
{
    protected $_body;
    protected $_params;
    private $_config;

    public function __construct($params = [], $handlerClass = null)
    {
        $this->_body = [
            'handler' => $handlerClass,
            'message' => static::class,
            'params' => $params,
        ];
        $this->_params = $params;
        $this->_config = Config::getConfig();
    }

    public function __get($name)
    {
        return $this->_params[$name] ?? null;
    }


    public function setConfig($host, $port, $user, $password, $vhost = '/')
    {
        $this->_config = compact('host','port', 'user', 'password', 'vhost');
    }

    /**
     * @return AMQPStreamConnection
     * @throws \AMQPConnectionException
     */
    protected function getConnection()
    {
        if(!isset($this->_config['host']) || empty($this->_config['host'])){
            throw new \AMQPConnectionException("Invalid connection host");
        }

        return new AMQPStreamConnection(
            $this->_config['host'],
            $this->_config['port'],
            $this->_config['user'],
            $this->_config['password'],
            $this->_config['vhost']
        );
    }

    /**
     * @return array
     * @throws \AMQPConnectionException
     */
    public function getChannel()
    {
        $connection = $this->getConnection();
        $channel = $connection->channel();

        $channel->exchange_declare($this->getExchange(), AMQPExchangeType::DIRECT, false, true, false);

        $channel->queue_declare($this->getQueue(), false, true, false, false);
        $channel->queue_bind($this->getQueue(), $this->getExchange(), $this->getRoutingKey());

        return [$connection, $channel];
    }

    /**
     * The exchange name
     *
     * @return string
     */
    protected function getExchange(): string
    {
        return 'exchange';
    }

    /**
     * The queue name
     *
     * @return string
     */
    public function getQueue(): string
    {
        return $this->getExchange() . '.queue';
    }


    /**
     * @return string
     */
    protected function getRoutingKey(): string
    {
        return $this->getExchange() . '.routing';
    }

    /**
     * return queue body
     *
     * @return string
     */
    protected function getBodyString(): string
    {
        return is_string($this->_body) ? $this->_body : json_encode($this->_body, JSON_UNESCAPED_UNICODE);
    }

    /**
     * 发送消息
     * @throws \AMQPConnectionException
     */
    public function publish()
    {
        /** @var $channel AMQPChannel */
        list($connection, $channel) = $this->getChannel();

        $message = new AMQPMessage($this->getBodyString(), [
            'content_type' => 'text/plain',
            'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT
        ]);
        $channel->basic_publish($message, $this->getExchange(), $this->getRoutingKey());

        $channel->close();
        $connection->close();
    }

    /**
     * 发送消息到延迟队列
     * @param int $time
     * @throws \AMQPConnectionException
     */
    public function publishDelay($time = 60)
    {
        /** @var $channel AMQPChannel */
        list($connection, $channel) = $this->getChannel();

        $exchangeKey = $this->getExchange() . '.delay.' . $time . 's';
        $queueKey = $this->getQueue() . '.delay.' . $time . 's';
        $routingKey = $this->getRoutingKey() . '.delay.' . $time . 's';

        /** 定义延迟队列 */
        $channel->exchange_declare($exchangeKey, AMQPExchangeType::DIRECT, false, true, false);

        $table = new AMQPTable();
        $table->set('x-dead-letter-exchange', $this->getExchange());
        $table->set('x-dead-letter-routing-key', $this->getRoutingKey());
        $table->set('x-message-ttl', $time * 1000);

        $channel->queue_declare($queueKey, false, true, false, false, false, $table);
        $channel->queue_bind($queueKey, $exchangeKey, $routingKey);

        $message = new AMQPMessage($this->getBodyString(), [
            'expiration' => $time * 1000,
            'content_type' => 'text/plain',
            'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT
        ]);
        $channel->basic_publish($message, $exchangeKey, $routingKey);

        $channel->close();
        $connection->close();
    }

    /**
     * 推送自定义数据
     * @param AMQPChannel $channel
     * @param $dataString
     */
    public function pushCustom(AMQPChannel $channel, $dataString)
    {
        $message = new AMQPMessage($dataString, [
            'content_type' => 'text/plain',
            'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT
        ]);
        $channel->basic_publish($message, $this->getExchange(), $this->getRoutingKey());
    }
}