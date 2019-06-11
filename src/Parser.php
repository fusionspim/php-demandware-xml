<?php
namespace DemandwareXml;

use DemandwareXml\Parser\NodeParserInterface;
use Generator;
use InvalidArgumentException;
use XMLReader;

class Parser
{
    protected $file;
    protected $parsed = [];

    public function __construct(string $file)
    {
        $this->file = $file;
    }

    public function validate(bool $useSchema = true): bool
    {
        if (! file_exists($this->file)) {
            throw new XmlException('XML file does not exist: ' . basename($this->file));
        } elseif (! is_readable($this->file)) {
            throw new XmlException('XML file is not readable: ' . basename($this->file));
        }

        $previousErrors = libxml_use_internal_errors(true);
        libxml_clear_errors();

        set_error_handler(function (int $severity, string $message, string $file, int $line): void {
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

    protected function validateNodeParserClass(string $class): void
    {
        if (! is_subclass_of($class, NodeParserInterface::class)) {
            throw new InvalidArgumentException('Node parser class "' . $class . '" must implement ' . NodeParserInterface::class);
        }
    }

    public function parse(string $class): Generator
    {
        $this->validateNodeParserClass($class);

        $reader = new XMLReader;
        $reader->open($this->file);

        while ($reader->read()) {
            $nodeParser = new $class($reader);

            if ($nodeParser->isMatch()) {
                ['id' => $id, 'data' => $data] = $nodeParser->parse();

                yield $id => $data;
            }
        }

        $reader->close();
    }

    public function parseToArray(array $classes, array $groupedByKey = []): array
    {
        $this->parsed = [];

        foreach ($classes as $index => $class) {
            $this->validateNodeParserClass($class);
            $this->parsed[$index] = [];
        }

        $reader = new XMLReader;
        $reader->open($this->file);

        while ($reader->read()) {
            foreach ($classes as $index => $class) {
                $nodeParser = new $class($reader);

                if ($nodeParser->isMatch()) {
                    ['id' => $id, 'data' => $data] = $nodeParser->parse();

                    if (in_array($index, $groupedByKey)) {
                        $this->parsed[$index][$id][] = $data;
                    } else {
                        $this->parsed[$index][$id] = $data;
                    }
                }
            }
        }

        $reader->close();

        return $this->parsed;
    }
}
