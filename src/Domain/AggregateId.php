<?php

namespace HelloFresh\Engine\Domain;

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class AggregateId implements AggregateIdInterface
{
    /** @var UuidInterface */
    protected $value;

    /**
     * UserId constructor.
     * @param UuidInterface $value
     */
    public function __construct(UuidInterface $value)
    {
        $this->value = $value;
    }

    /**
     * This is what gets saved to data stores.
     *
     * @return string
     */
    public function __toString() : string
    {
        return $this->value->toString();
    }

    /**
     * Rebuild an instance based on a string
     *
     * @param  string $string
     * @return AggregateIdInterface
     */
    public static function fromString($string)
    {
        return new static(Uuid::fromString($string));
    }

    public static function generate()
    {
        return new static(Uuid::uuid4());
    }
}
