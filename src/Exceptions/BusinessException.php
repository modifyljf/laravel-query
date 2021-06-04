<?php

namespace Guesl\Query\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * Class BusinessException
 * @package Guesl\Query\Exceptions
 */
class BusinessException extends Exception
{
    /**
     * @var string
     */
    protected string $key;

    /**
     * @var string
     */
    protected string $errorBag;

    /**
     * Create a new authentication exception.
     *
     * @param string $key
     * @param string $message
     * @param string $errorBag
     * @param int $code
     */
    public function __construct(string $key, string $message = 'Business exception.', string $errorBag = "default", int $code = 422)
    {
        parent::__construct($message, $code);
        $this->key = $key;
        $this->errorBag = $errorBag;
    }

    /**
     * @return string
     */
    public function getKey(): string
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
    public function setErrorBag(string $errorBag)
    {
        $this->errorBag = $errorBag;
    }

    /**
     * Render the exception into an HTTP response.
     *
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function render(Request $request): JsonResponse
    {
        $errors = [
            "message" => "The given data was invalid.",
            "errors" => [$this->getKey() => [$this->getMessage()]],
        ];

        if ($request->expectsJson()) {
            return response()->json($errors, $this->getCode() ?: 422);
        } else {
            throw ValidationException::withMessages([
                $this->getKey() => [$this->getMessage()],
            ])->errorBag($this->getErrorBag());
        }
    }
}
