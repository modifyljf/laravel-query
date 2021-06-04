<?php

namespace Guesl\Query\Models;

/**
 * Class Fuzzy
 * @package Guesl\Query\Models
 */
class Fuzzy
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $value;

    /**
     * Fuzzy constructor.
     * @param string $name
     * @param string $value
     */
    public function __construct(string $name, string $value)
    {
        $this->name = $name;
        $this->value = $value;
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
     * @return Fuzzy
     */
    public function setName(string $name): Fuzzy
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * @param string $value
     * @return Fuzzy
     */
    public function setValue(string $value): Fuzzy
    {
        $this->value = $value;
        return $this;
    }
}
