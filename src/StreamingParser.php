<?php
namespace DemandwareXml;

use XMLReader;

class StreamingParser
{
    protected $path;

    public function __construct(string $path)
    {
        $this->path = $path;
    }

    public function validate(): bool
    {
        if (! file_exists($this->path)) {
            throw new XmlException('XML file does not exist: ' . basename($this->path));
        } elseif (! is_readable($this->path)) {
            throw new XmlException('XML file is not readable: ' . basename($this->path));
        }

        $previousErrors = libxml_use_internal_errors(true);

        set_error_handler(function (int $severity, string $message, string $file, int $line) {
            throw new XmlException($message . ' in ' . basename($file) . ' on line ' . $line, $severity);
        });

        try {
            $reader = new XMLReader;
            $reader->open($this->path);
            $reader->setSchema(realpath(__DIR__ . '/../xsd/catalog.xsd'));

            while ($reader->read());

            $errors = libxml_get_errors();
            libxml_clear_errors();

            if (count($errors) > 0) {
                throw $this->libXmlErrorToException(reset($errors));
            }
        } finally {
            libxml_use_internal_errors($previousErrors);
            restore_error_handler();
        }

        return true;
    }

    protected function libXmlErrorToException($error): XMLException
    {
        switch ($error->level) {
            case LIBXML_ERR_WARNING:
                $level = 'Warning';
                break;

            case LIBXML_ERR_ERROR:
                $level = 'Error';
                break;

            case LIBXML_ERR_FATAL:
                $level = 'Fatal';
                break;
        }

        return new XmlException($level . ': ' . trim($error->message) . ' in ' . basename($error->file) . ' on line ' . $error->line . ' column ' . $error->column, $error->code);
    }
}
