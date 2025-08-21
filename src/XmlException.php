<?php

namespace DemandwareXml;

use Exception;

/**
 * Extends the default Exception class for ease of catching errors thrown by this library
 */
class XmlException extends Exception
{
    protected ?string $url = null;

    public function __construct($message = '', $code = 0, ?\Throwable $previous = null, ?string $url = null)
    {
        $this->url = $url;

        parent::__construct($message, $code, $previous);
    }

    public function getUrl(): string
    {
        return $this->url;
    }
}
