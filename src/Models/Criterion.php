<?php

namespace Guesl\Query\Models;

/**
 * Class Criterion
 * @package Guesl\Query\Models
 */
class Criterion
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $operation;

    /**
     * @var string
     */
    private $value;

    /**
     * @var bool
     */
    private $exclusive;

    /**
     * Criterion constructor.
     *
     * @param string $name
     * @param string $operation
     * @param mixed $value
     * @param bool $exclusive
     */
    public function __construct(string $name, string $operation, $value, bool $exclusive = true)
    {
        $this->name = $name;
        $this->operation = $operation;
        $this->value = $value;
        $this->exclusive = $exclusive;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     * @return Criterion
     */
    public function setName($name): Criterion
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getOperation(): string
    {
        return $this->operation;
    }

    /**
     * @param string $operation
     * @return Criterion
     */
    public function setOperation(string $operation): Criterion
    {
        $this->operation = $operation;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * @param mixed $value
     * @return Criterion
     */
    public function setValue($value): Criterion
    {
        $this->value = $value;
        return $this;
    }

    /**
     * @return bool
     */
    public function isExclusive(): bool
    {
        return $this->exclusive;
    }

    /**
     * @param bool $exclusive
     * @return Criterion
     */
    public function setExclusive(bool $exclusive): Criterion
    {
        $this->exclusive = $exclusive;
        return $this;
    }
}
