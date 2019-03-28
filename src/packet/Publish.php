<?php
/**
 * @author Oliver Lorenz
 * @since 2015-04-24
 * Time: 01:22
 */

namespace oliverlorenz\reactphpmqtt\packet;

use oliverlorenz\reactphpmqtt\protocol\Version;

/**
 * A PUBLISH Control Packet is sent from a Client to a Server or from
 * Server to a Client to transport an Application Message.
 */
class Publish extends ControlPacket
{
    const EVENT = 'PUBLISH';

    protected $messageId;

    protected $topic = '';

    protected $qos = 0;

    protected $dup = false;

    protected $retain = false;
    
    protected $offset;

    public static function getControlPacketType()
    {
        return ControlPacketType::PUBLISH;
    }

    public function parse()
    {
        $this->parseOffset();
        
        $byte1 = $this->rawData{0};
        if (!empty($byte1)) {
            $byte1 = ord($byte1);
            $this->setRetain(($byte1 & 1) === 1);
            if (($byte1 & 2) === 2) {
                $this->setQos(1);
            } elseif (($byte1 & 4) === 4) {
                $this->setQos(2);
            }
            $this->setDup(($byte1 & 8) === 8);
        }
        
        $this->parseTopic();
        $this->parsePayload();
        return $this;
    }

    protected function parseOffset()
    {
        $offset = 1;
        do {
            $encodedByte = ord($this->rawData{$offset});
            $offset++;
        }while(($encodedByte & 0x80) != 0);
        $this->offset = $offset;
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
    
    /**
     * @param string $rawInput
     * @return string
     */
    protected function parseTopic()
    {
        
        $headerLength = 2;
        $header = substr($this->rawData, $this->offset, $headerLength);
        $topicLength = $this->getInteger($header);
        
        $this->topic = substr($this->rawData, $this->offset + $headerLength, $topicLength);
    }    
    
    protected function parsePayload() 
    {
        $idlen = 0;
        if ($this->qos) {
            $idlen = 2;
            
            $idintifier = substr(
                $this->rawData,
                2 + strlen($this->topic) + $this->offset,
                $idlen
            );

            $this->messageId =  $this->getInteger($idintifier);
        } 
        $this->payload = substr(
            $this->rawData,
            2 + strlen($this->topic) + $idlen + $this->offset
        );        
    }    
    
    /**
     * @param $topic
     * @return $this
     */
    public function setTopic($topic)
    {
        $this->topic = $topic;
        return $this;
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
     * @param int $qos 0,1,2
     * @return $this
     */
    public function setQos($qos)
    {
        $this->qos = $qos;
        return $this;
    }

    /**
     * @param bool $dup
     * @return $this
     */
    public function setDup($dup)
    {
        $this->dup = $dup;
        return $this;
    }

    /**
     * @param bool $retain
     * @return $this
     */
    public function setRetain($retain)
    {
        $this->retain = $retain;
        return $this;
    }

    /**
     * @return string
     */
    public function getTopic()
    {
        return $this->topic;
    }

    /**
     * @return int
     */
    public function getQos()
    {
        return $this->qos;
    }


    /**
     * @return string
     */
    protected function getVariableHeader()
    {
        return $this->getLengthPrefixField($this->topic);
    }

    protected function addReservedBitsToFixedHeaderControlPacketType($byte1)
    {
        $qosByte = 0;
        if ($this->qos === 1) {
            $qosByte = 1;
        } else if ($this->qos === 2) {
            $qosByte = 2;
        }
        $byte1 += $qosByte << 1;

        if ($this->dup) {
            $byte1 += 8;
        }

        if ($this->retain) {
            $byte1 += 1;
        }

        return $byte1;
    }
}
