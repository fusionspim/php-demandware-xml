<?php

namespace DemandwareXml;

use Exception;

/**
 * Extends the default Exception class for ease of catching errors thrown by this library
 */
class XmlException extends Exception
{
    protected ?string $invalidCharUrl = null;

    public function __construct($message = '', $code = 0, ?\Throwable $previous = null)
    {
        $this->invalidCharUrl = $this->parseInvalidChar($message);

        parent::__construct($message, $code, $previous);
    }

    public function getInvalidCharUrl(): ?string
    {
        return $this->invalidCharUrl;
    }

    protected function parseInvalidChar(?string $message): ?string
    {
        if (preg_match('/^Fatal: PCDATA invalid Char value (\d+)/', $message, $matches)) {
            return sprintf('https://www.fileformat.info/info/unicode/char/%d/index.htm', $matches[1]);
        }

        return null;
    }
}
