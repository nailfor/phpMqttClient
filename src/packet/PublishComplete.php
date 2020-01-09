<?php
/**
 * @author Oliver Lorenz
 * @since 2015-05-08
 * Time: 17:08
 */

namespace oliverlorenz\reactphpmqtt\packet;

/**
 * The PUBCOMP Packet is the response to a PUBREL Packet.
 * It is the fourth and final packet of the QoS 2 protocol exchange.
 */
class PublishComplete extends ControlPacket
{
    const EVENT = 'PUBLISH_COMPLETE';

    public static function getControlPacketType()
    {
        return ControlPacketType::PUBCOMP;
    }

    /**
     * @param $messageId
     * @return $this
     */
    public function setMessageId($messageId)
    {
        $this->messageId = $messageId;
        return $this;
    }

    /**
     * @return $messageId
     */
    public function getMessageId()
    {
        return $this->messageId;
    }

    /**
     * @return string
     */
    protected function getVariableHeader()
    {
        $str = '  ';
        $str{0} = chr($this->messageId >> 8);
        $str{1} = chr($this->messageId & 0xFF);
        return $str;
    }
}
