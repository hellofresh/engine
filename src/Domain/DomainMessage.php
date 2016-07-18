<?php

namespace HelloFresh\Engine\Domain;

/**
 * Represents an important change in the domain.
 */
final class DomainMessage
{
    /**
     * @var DomainEventInterface
     */
    private $payload;

    /**
     * @var string
     */
    private $id;

    /**
     * @var \DateTimeImmutable
     */
    private $recordedOn;

    /**
     * @var int
     */
    private $version;

    /**
     * @param string $id
     * @param $version
     * @param mixed $payload
     * @param \DateTimeImmutable $recordedOn
     */
    public function __construct($id, $version, $payload, \DateTimeImmutable $recordedOn)
    {
        $this->id = $id;
        $this->payload = $payload;
        $this->recordedOn = $recordedOn->setTimezone(new \DateTimeZone('UTC'));
        $this->version = $version;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return DomainEventInterface
     */
    public function getPayload()
    {
        return $this->payload;
    }

    /**
     * {@inheritDoc}
     */
    public function getRecordedOn()
    {
        return $this->recordedOn;
    }

    /**
     * {@inheritDoc}
     */
    public function getType()
    {
        return get_class($this->payload);
    }

    /**
     * @param string $id
     * @param $version
     * @param DomainEventInterface $payload
     * @return DomainMessage
     */
    public static function recordNow($id, $version, DomainEventInterface $payload)
    {
        $recordedOn = \DateTimeImmutable::createFromFormat('U.u', sprintf('%.6F', microtime(true)));

        return new DomainMessage($id, $version, $payload, $recordedOn);
    }

    /**
     * @return int
     */
    public function getVersion()
    {
        return $this->version;
    }
}
