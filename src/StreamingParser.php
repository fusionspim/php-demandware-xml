<?php
namespace DemandwareXml;

use DemandwareXml\Parser\NodeParserInterface;
use Generator;
use XMLReader;

class StreamingParser
{
    protected $file;

    public function __construct(string $file)
    {
        $this->file = $file;
    }

    // Validation without a schema is allowed for photos as they don't have one.
    public function validate($useSchema = true): bool
    {
        if (! file_exists($this->file)) {
            throw new XmlException('XML file does not exist: ' . basename($this->file));
        } elseif (! is_readable($this->file)) {
            throw new XmlException('XML file is not readable: ' . basename($this->file));
        }

        $previousErrors = libxml_use_internal_errors(true);
        libxml_clear_errors();

        set_error_handler(function (int $severity, string $message, string $file, int $line) {
            throw new XmlException($message . ' in ' . basename($file) . ' on line ' . $line, $severity);
        });

        try {
            $reader = new XMLReader;
            $reader->open($this->file);

            if ($useSchema) {
                $reader->setSchema(realpath(__DIR__ . '/../xsd/catalog.xsd'));
            }

            while ($reader->read());

            $reader->close();

            $errors = libxml_get_errors();

            if (count($errors) > 0) {
                throw $this->libXmlErrorToException(reset($errors));
            }
        } finally {
            libxml_use_internal_errors($previousErrors);
            libxml_clear_errors();
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

    public function parse(string $class): Generator
    {
        if (! is_subclass_of($class, NodeParserInterface::class)) {
            throw new XmlException('Node parser class "' . $class . '" must implement ' . NodeParserInterface::class);
        }

        $reader = new XMLReader;
        $reader->open($this->file);

        while ($reader->read()) {
            $nodeParser = new $class($reader);

            if ($nodeParser->isMatch()) {
                $result = $nodeParser->parse(); // @todo: Use array destructuring when on PHP 7.1.
                yield key($result) => reset($result);
            }
        }

        $reader->close();
    }
}
