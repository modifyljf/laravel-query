<?php

namespace Guesl\Query\Models;

/**
 * Class EagerLoading
 * @package Guesl\Query\Models
 */
class EagerLoading
{
    /**
     * @var string
     */
    private $name;

    /**
     * EagerLoading constructor.
     * @param string $name
     */
    public function __construct(string $name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return EagerLoading
     */
    public function setName(string $name): EagerLoading
    {
        $this->name = $name;
        return $this;
    }
}
