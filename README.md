# PHP MQTT Client

phpMqttClient is an MQTT client library for PHP. Its based on the reactPHP socket-client and added the MQTT protocol
specific functions. I hope its a better starting point that the existing PHP MQTT libraries. 

[![Build Status](https://travis-ci.org/oliverlorenz/phpMqttClient.svg?branch=master)](https://travis-ci.org/oliverlorenz/phpMqttClient) 
[![Code Climate](https://codeclimate.com/github/oliverlorenz/phpMqttClient/badges/gpa.svg)](https://codeclimate.com/github/oliverlorenz/phpMqttClient) 
[![Test Coverage](https://codeclimate.com/github/oliverlorenz/phpMqttClient/badges/coverage.svg)](https://codeclimate.com/github/oliverlorenz/phpMqttClient/coverage)

## Goal

Goal of this project is easy to use MQTT client for PHP in a modern architecture without using any php modules.
Currently, only protocol version 4 (mqtt 3.1.1) is implemented.
* Protocol specifications: http://docs.oasis-open.org/mqtt/mqtt/v3.1.1/csprd02/mqtt-v3.1.1-csprd02.html

## Example publish

```php
$config = require 'config.php';

$connector = ClientFactory::createClient(new Version4());

$p = $connector->create($config['server'], $config['port'], $config['options']);
$p->then(function(Stream $stream) use ($connector) {
    return $connector->publish($stream, 'a/b', 'example message');
});
$connector->getLoop()->run();
```

## Example subscribe

```php

use oliverlorenz\reactphpmqtt\ClientFactory;

class MQTTClass 
{
    public static function topic($packet)
    {
        $id = $packet->getMessageId();
        $payload = $packet->getPayload();
    }

    public static function capital($packet)
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
                        'PUBLISH' => [static::class, 'topic'],
                    ],
                ],
                'capital_name' => [
                    'qos'       => 0,
                    'events'    => [
                        'PUBLISH' => [static::class, 'capital'],
                    ],
                ],
            ],        
        ];

        ClientFactory::run($url, $options, [static::class, 'error']);
    }
}
```

## Notice - (May 12th, 2015)
This is library is not stable currently. Its an early state, but I am working on it. I will add more features if I need them. If you need features: please give feedback or contribute to get this library running.

Currently works:
* connect (clean session, no other connect flags)
* disconnect
* publish
* subscribe

## Run tests

    ./vendor/bin/phpunit -c ./tests/phpunit.xml ./tests


## Troubleshooting

### Why does the connect to localhost:1883 not work?

The answer is simple: In the example is the DNS 8.8.8.8 configured. Your local server is not visible for them, so you can't connect.
