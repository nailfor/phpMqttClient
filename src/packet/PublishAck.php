<?php
/**
 * @author Oliver Lorenz
 * @since 2015-05-08
 * Time: 17:06
 */

namespace oliverlorenz\reactphpmqtt\packet;

/**
 * A PUBACK Packet is the response to a PUBLISH Packet with QoS level 1.
 */
class PublishAck extends ControlPacket 
{
    protected $messageId = 0;

    public static function getControlPacketType()
    {
        return ControlPacketType::PUBACK;
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
