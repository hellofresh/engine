<?php

namespace HelloFresh\Engine\Domain;

/**
 * Defines basic methods for an aggregate id
 */
interface AggregateIdInterface
{
    /**
     * This is what gets saved to data stores.
     *
     * @return string
     */
    public function __toString();

    /**
     * Rebuild an instance based on a string
     *
     * @param  string $string
     * @return AggregateIdInterface
     */
    public static function fromString($string);
}
