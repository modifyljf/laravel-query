<?php

namespace Guesl\Query\Models;

/**
 * Class Scope
 * @package Guesl\Query\Models
 */
class Scope
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var array
     */
    private $parameters;

    /**
     * Scope constructor.
     * @param string $name
     * @param array $parameters
     */
    public function __construct(string $name, array $parameters)
    {
        $this->name = $name;
        $this->parameters = $parameters;
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
     * @return Scope
     */
    public function setName(string $name): Scope
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return array
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * @param array $parameters
     * @return Scope
     */
    public function setParameters(array $parameters): Scope
    {
        $this->parameters = $parameters;
        return $this;
    }
}
