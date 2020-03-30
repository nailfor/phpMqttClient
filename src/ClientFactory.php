<?php

namespace oliverlorenz\reactphpmqtt;

use protocol\Version;
use oliverlorenz\reactphpmqtt\protocol\Version4;
use oliverlorenz\reactphpmqtt\packet\Publish;
use oliverlorenz\reactphpmqtt\packet\PublishAck;
use oliverlorenz\reactphpmqtt\packet\PublishReceived;
use oliverlorenz\reactphpmqtt\packet\PublishRelease;
use oliverlorenz\reactphpmqtt\packet\PublishComplete;

use React\Dns\Resolver\Factory as DnsResolverFactory;
use React\EventLoop\Factory as EventLoopFactory;
use React\Socket\DnsConnector;
use React\Socket\SecureConnector;
use React\Socket\TcpConnector;

class ClientFactory
{
    public static $client;
    public static $connection;
    protected static $version;

    protected static function setClient(string $url, array $options = [], Version $version = null, string $resolverIp = '8.8.8.8') {
        $loop = EventLoopFactory::create();
        $connector = self::createDnsConnector($resolverIp, $loop);

        if (!$version) {
            $version = new Version4();
        }
        static::$version = $version;

        static::$client =  new MqttClient($url, $loop, $connector, $version, $options);
    }

    protected static function setTopics($topics)
    {
        $version = static::$version;
        foreach ($topics as $topic => $params) {
            static::$connection->then(function($stream) use ($topic, $params, $version) {
                $events = $params['events'] ?? [];
                foreach ($events as $event => $closure){
                    //$stream->on("$event:$topic", $closure);
                    $stream->on("$event", $closure);
                }

                $clear = $params['clear'] ?? true;
                if ($clear) {
                    $stream->on(Publish::EVENT, function(Publish $message) use ($stream, $version) {
                        switch ($message->getQos()) {
                            case 1:
                                $id = $message->getMessageId();

                                $packet = new PublishAck($version);
                                $packet->setMessageId($id);
                                $message = $packet->get();

                                $stream->write($message);
                                break;
                            case 2:
                                $id = $message->getMessageId();

                                $packet = new PublishReceived($version);
                                $packet->setMessageId($id);
                                $message = $packet->get();

                                $stream->write($message);
                                break;
                        }
                    });
                    $stream->on(PublishRelease::EVENT, function(PublishRelease $message) use ($stream, $version) {
                        $id = $message->getMessageId();

                        $packet = new PublishComplete($version);
                        $packet->setMessageId($id);
                        $message = $packet->get();

                        $stream->write($message);

                    });
                }

                ClientFactory::$client->subscribe($stream, $topic, $params['qos'] ?? 0);
            });

        }
    }

    public static function run(string $url, array $options = [], $errorClosure = null, Version $version = null, $resolverIp = '')
    {
        static::setClient($url, $options, $version, $resolverIp);

        static::$connection = static::$client->connect();
        if ($errorClosure) {
            static::$connection->then(null, $errorClosure);
        }

        static::setTopics($options['topics'] ?? []);

        $loop = static::$client->getLoop();
        $loop->run();
    }

    public static function createSecureClient(Version $version, $resolverIp = '8.8.8.8')
    {
        $loop = EventLoopFactory::create();
        $connector = self::createDnsConnector($resolverIp, $loop);
        $secureConnector = new SecureConnector($connector, $loop);

        return new MqttClient($loop, $secureConnector, $version);
    }

    private static function createDnsConnector($resolverIp, $loop)
    {
        $dnsResolverFactory = new DnsResolverFactory();
        $resolver = $dnsResolverFactory->createCached($resolverIp, $loop);

        return new DnsConnector(new TcpConnector($loop), $resolver);
    }
}
