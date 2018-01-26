<?php
namespace DemandwareXml;

use SimpleXMLElement;
use XMLReader;

class StreamingParser
{
    protected $file;

    public function __construct(string $file)
    {
        $this->file = $file;
    }

    public function validate(): bool
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
            $reader->setSchema(realpath(__DIR__ . '/../xsd/catalog.xsd'));

            while ($reader->read());

            $errors = libxml_get_errors();

            if (count($errors) > 0) {
                throw $this->libXmlErrorToException(reset($errors));
            }
        } finally {
            $reader->close();
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

    protected function getXmlReader(): XMLReader
    {
        $reader = new XMLReader;
        $reader->open($this->file);

        return $reader;
    }

    protected function parseNodes(array $nodes): iterable
    {
        try {
            $reader = $this->getXmlReader();

            while ($reader->read()) {
                if ($reader->nodeType !== XMLReader::ELEMENT || ! in_array($reader->localName, $nodes)) {
                    continue;
                }

                yield new SimpleXMLElement($reader->readOuterXml());
            }
        } finally {
            $reader->close();
        }
    }

    public function getAssignments(): iterable
    {
        foreach ($this->parseNodes(['category-assignment']) as $element) {
            $assignment = $this->extractAssignment($element);
            yield key($assignment) => reset($assignment);
        }
    }

    protected function extractAssignment(SimpleXMLElement $element): array
    {
        $productId  = (string) $element['product-id'];
        $categoryId = (string) $element['category-id'];
        $primary    = (isset($element->{'primary-flag'}) ? ((string) $element->{'primary-flag'}) === 'true': false);

        return [$productId => [$categoryId => $primary]];
    }

    public static function toArrayGroupedByKey(iterable $items): array
    {
        $results = [];

        foreach ($items as $key => $item) {
            $results[$key][] = $item;
        }

        return $results;
    }
}