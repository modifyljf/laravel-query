<?php

namespace Guesl\Query\Models;

/**
 * Class Sort
 * @package Guesl\Query\Models
 */
class Sort
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $direction;

    /**
     * Sort constructor.
     * @param string $name
     * @param string $direction
     */
    public function __construct(string $name, string $direction)
    {
        $this->name = $name;
        $this->direction = $direction;
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
     * @return Sort
     */
    public function setName(string $name): Sort
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getDirection(): string
    {
        return $this->direction;
    }

    /**
     * @param string $direction
     * @return Sort
     */
    public function setDirection(string $direction): Sort
    {
        $this->direction = $direction;
        return $this;
    }
}
