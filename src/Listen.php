<?php
/**
 * Created by PhpStorm.
 * User: minms
 * Date: 2020/1/15
 * Time: 10:21
 */

namespace Minms\RabbitMQ;

use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class Listen
{
    public static function callback(callable $callback, $message = Message::class)
    {
        /** @var Message $message */
        $message = new $message();

        /** @var AMQPChannel $channel */
        /** @var AMQPStreamConnection $connection */
        list($connection, $channel) = $message->getChannel();

        $channel->basic_qos(null, 1, null);
        $channel->basic_consume(
            $message->getQueue(),
            '',
            false,
            false,
            false,
            false,
            function (AMQPMessage $message) use ($callback) {
                try {
                    $callback($message);
                    /** ack */
                    $message->delivery_info['channel']->basic_ack($message->delivery_info['delivery_tag']);
                } catch (\Exception $exception){
                    echo "消费失败: {$exception->getMessage()} \n";
                }
            }
        );
        while (count($channel->callbacks)) $channel->wait();
        $channel->close();
        $connection->close();
    }
}