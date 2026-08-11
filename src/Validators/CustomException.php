<?php

namespace Webkul\GraphQLAPI\Validators;

use Exception;
use GraphQL\Error\ClientAware;
use GraphQL\Error\ProvidesExtensions;

class CustomException extends Exception implements ClientAware, ProvidesExtensions
{
    /**
     * The Exception message to throw.
     *
     * @return void
     */
    public function __construct(string $message, protected array $extensions = [])
    {
        parent::__construct($message);
    }

    /**
     * Returns true when exception message is safe to be displayed to a client.
     *
     * @api
     */
    public function isClientSafe(): bool
    {
        return true;
    }

    /**
     * Data to include within the "extensions" key of the formatted error.
     * 
     * @return array<string, mixed>|null
     */
    public function getExtensions(): ?array
    {
        return $this->extensions ?: null;
    }
}
