<?php
/**
 * @author Oliver Lorenz
 * @since 2015-05-08
 * Time: 17:07
 */

namespace oliverlorenz\reactphpmqtt\packet;

/**
 * A PUBREC Packet is the response to a PUBLISH Packet with QoS 2.
 * It is the second packet of the QoS 2 protocol exchange.
 */
class PublishReceived extends ControlPacket
{
    const EVENT = 'PUBLISH_RECEIVED';

    public static function getControlPacketType()
    {
        return ControlPacketType::PUBREC;
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
