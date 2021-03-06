<?php

namespace Genkgo\Camt\DTO;

/**
 * Class GroupHeader
 * @package Genkgo\Camt\DTO
 */
class GroupHeader
{
    /**
     * @var string
     */
    private $messageId;
    /**
     * @var \DateTimeImmutable
     */
    private $createdOn;

    /**
     * @param $messageId
     * @param \DateTimeImmutable $createdOn
     */
    public function __construct($messageId, \DateTimeImmutable $createdOn)
    {
        $this->messageId = $messageId;
        $this->createdOn = $createdOn;
    }

    /**
     * @return string
     */
    public function getMessageId()
    {
        return $this->messageId;
    }

    /**
     * @return \DateTimeImmutable
     */
    public function getCreatedOn()
    {
        return $this->createdOn;
    }
}
