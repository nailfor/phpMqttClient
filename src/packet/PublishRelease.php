<?php
/**
 * @author Oliver Lorenz
 * @since 2015-05-08
 * Time: 17:07
 */

namespace oliverlorenz\reactphpmqtt\packet;

/**
 * A PUBREL Packet is the response to a PUBREC Packet.
 * It is the third packet of the QoS 2 protocol exchange.
 */
class PublishRelease extends ControlPacket
{
    const EVENT = 'PUBLISH_RELEASE';
    protected $messageId;

    public static function getControlPacketType()
    {
        return ControlPacketType::PUBREL;
    }

    /**
     * @return $messageId
     */
    public function getMessageId()
    {
        return $this->messageId;
    }

    public function parse()
    {
        $idintifier = substr($this->rawData, 2, 2);

        $this->messageId =  $this->getInteger($idintifier);

        return $this;
    }

    protected function getInteger(string $data) : int
    {
        if (isset($data{1})) {
            $m = ord($data{0})<<8;
            $l = ord($data{1});
            return $m + $l;
        }
        return 0;
    }

}
