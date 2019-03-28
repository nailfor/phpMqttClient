<?php
/**
 * @author Oliver Lorenz
 * @since 2015-04-24
 * Time: 19:40
 */

namespace oliverlorenz\reactphpmqtt\packet;

use oliverlorenz\reactphpmqtt\protocol\Version;
use oliverlorenz\reactphpmqtt\protocol\Violation as ProtocolViolation;

class Factory
{
    static $rawData = '';
    static $packetLength = 0;


    /**
     * @param Version $version
     * @param string $remainingData
     * @throws ProtocolViolation
     * @return ConnectionAck|PingResponse|SubscribeAck|Publish|PublishComplete|PublishRelease|PublishReceived|void
     */
    public static function getNextPacket(Version $version, $remainingData)
    {
        while(isset($remainingData{1})) {
            if (static::$rawData) {
                static::$rawData .= $remainingData;

                if (strlen(static::$rawData) < static::$packetLength){
                    $remainingData = '';
                    continue;
                }
                $remainingData  = static::$rawData;
                $packetLength   = static::$packetLength;
            }
            else {
                $offset = 1;
                $remainingLength = static::getLength($remainingData, $offset);
                $packetLength = $offset + $remainingLength;
            }
            
            $nextPacketData = substr($remainingData, 0, $packetLength);
            $remainingData = substr($remainingData, $packetLength);

            static::$rawData        = $nextPacketData;
            static::$packetLength   = $packetLength;
            if (strlen($nextPacketData) < $packetLength) {
                continue;
            }
            
            $nextPacketData = static::$rawData;
            static::$rawData = '';

            yield self::getPacketByMessage($version, $nextPacketData);
        }
    }

    protected static function getPacketByMessage(Version $version, $input)
    {
        $controlPacketType = ord($input{0}) >> 4;
        
        switch ($controlPacketType) {
            case ConnectionAck::getControlPacketType():
                return ConnectionAck::parsePacket($version, $input);

            case PingResponse::getControlPacketType():
                return PingResponse::parsePacket($version, $input);

            case SubscribeAck::getControlPacketType():
                return SubscribeAck::parsePacket($version, $input);

            case Publish::getControlPacketType():
                return Publish::parsePacket($version, $input);

            case PublishComplete::getControlPacketType():
                return PublishComplete::parsePacket($version, $input);

            case PublishRelease::getControlPacketType():
                return PublishRelease::parsePacket($version, $input);

            case PublishReceived::getControlPacketType():
                return PublishReceived::parsePacket($version, $input);
        }

        throw new ProtocolViolation('Unexpected packet type: ' . $controlPacketType);
    }
    
    /**
     * Calculate length of packet
     * @param string $remainingData
     * @param int $offset
     * @return int length
     * @throws type
     */
    protected static function getLength(string $remainingData, int &$offset) : int
    {
       $multiplier = 1;
       $value = 0;
       $offset = 1;

       do{
            $encodedByte = ord($remainingData{$offset});
            $offset ++;
            $value += ($encodedByte & 0x7F) * $multiplier;
            $multiplier *= 0x80;
            if ($multiplier > 128*128*128){
               throw \RuntimeException('Malformed Remaining Length');
            }

       }while (($encodedByte & 0x80) != 0);
       return $value;
    }
}
