<?php

namespace Guesl\Query\Exceptions;

use Exception;

/**
 * Class AuthException
 * @package Guesl\Query\Exceptions
 */
class AuthException extends Exception
{
    /**
     * @var string
     */
    protected $key;

    /**
     * @var
     */
    protected $attributes;

    /**
     * @var string
     */
    protected $errorBag;

    /**
     * Create a new authentication exception.
     *
     * @param string $key
     * @param string $message
     * @param string $errorBag
     * @param int $code
     */
    public function __construct(string $key, string $message = 'Authorization Exception.', string $errorBag = "default", int $code = 422)
    {
        parent::__construct($message, $code);
        $this->key = $key;
        $this->errorBag = $errorBag;
    }

    /**
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @param string $key
     */
    public function setKey(string $key)
    {
        $this->key = $key;
    }

    /**
     * @return string
     */
    public function getErrorBag(): string
    {
        return $this->errorBag;
    }

    /**
     * @param string $errorBag
     */
    public function setErrorBag($errorBag)
    {
        $this->errorBag = $errorBag;
    }
}
