<?php

use oliverlorenz\reactphpmqtt\ClientFactory;

class MQTTClass 
{
    public static function message($packet)
    {
        $id = $packet->getMessageId();
        $payload = $packet->getPayload();
    }

    public static function error($reason) {
        echo $reason->getMessage(). PHP_EOL;
        exit;
    }

    public static function subscribe()
    {
        $url    = '127.0.0.1:1883';

        $options = [
            //'username'  => '',
            //'password'  => '',
            'clientId'  => 'php',
            'cleanSession' => false,

            'topics'    => [
                'topic_name' => [
                    //this flag clear message after reciev. Default true
                    //'clear'     => false,
                    'qos'       => 1,
                    'events'    => [
                        'PUBLISH' => [static::class, 'message'],
                    ],
                ],
            ],        
        ];

        ClientFactory::run($url, $options, [static::class, 'error']);
    }
}
