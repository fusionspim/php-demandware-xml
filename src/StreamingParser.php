<?php
namespace DemandwareXml;

use SimpleXMLElement;
use XMLReader;

class StreamingParser
{
    protected $file;
    protected $skipAttributes;

    public function __construct(string $file, bool $skipAttributes = false)
    {
        $this->file           = $file;
        $this->skipAttributes = $skipAttributes;
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

    public function getCategories(): iterable
    {
        foreach ($this->parseNodes(['category']) as $element) {
            $category = $this->extractCategory($element);
            yield key($category) => reset($category);
        }
    }

    protected function extractCategory(SimpleXMLElement $element): array
    {
        return [(string) $element['category-id'] => $this->commonDetails($element)];
    }

    protected function commonDetails(SimpleXMLElement $element): array
    {
        if ($this->skipAttributes) {
            $details = [];
        } else {
            $details = [
                'attributes' => $this->customAttributes($element),
                'page'       => $this->pageAttributes($element),
            ];
        }

        $map = [
            'description'             => 'long-description',
            'name'                    => 'display-name',
            'start'                   => 'online-from',
            'classification'          => 'classification-category',
            'online'                  => 'online-flag',
            'searchable'              => 'searchable-flag',
            'parent'                  => 'parent',
            'tax'                     => 'tax-class-id',
            'brand'                   => 'brand',
            'sitemap-included-flag'   => 'sitemap-included-flag',
            'sitemap-changefrequency' => 'sitemap-changefrequency',
            'sitemap-priority'        => 'sitemap-priority',
        ];

        foreach ($map as $name => $source) {
            $cleansed = html_entity_decode(trim((string) $element->{$source}));

            if (strlen($cleansed) > 0) {
                $details[$name] = $cleansed;
            }
        }

        // if they exist, online/searchable will always be a true/false string, so cast for ease of use
        foreach (['online', 'searchable'] as $name) {
            if (isset($details[$name])) {
                $details[$name] = filter_var($details[$name], FILTER_VALIDATE_BOOLEAN);
            }
        }

        // convert the tax string to a meaningful number
        if (isset($details['tax'])) {
            $details['tax'] = (float) str_replace(['TAX_', '_'], ['', '.'], $details['tax']);
        }

        ksort($details);

        return $details;
    }

    protected function customAttributes(SimpleXMLElement $element): array
    {
        if (! isset($element->{'custom-attributes'}->{'custom-attribute'})) {
            return [];
        }

        $attributes = [];

        foreach ($element->{'custom-attributes'}->{'custom-attribute'} as $attribute) {
            if (isset($attribute->{'value'})) {
                $value = [];

                foreach ($attribute->{'value'} as $item) {
                    $value[] = trim((string) $item);
                }
            } else {
                $value = trim((string) $attribute);

                // cast strings to booleans (only needed for single values, as multi-value booleans make no sense)
                if ('true' === $value || 'false' === $value) {
                    $value = ('true' === $value);
                }
            }

            $attributes[(string) $attribute['attribute-id']] = $value;
        }

        ksort($attributes);

        return $attributes;
    }

    protected function pageAttributes(SimpleXMLElement $element): array
    {
        $attributes = [];

        foreach (['title', 'description', 'keywords', 'url'] as $part) {
            $value = html_entity_decode(trim((string) $element->{'page-attributes'}->{'page-' . $part}));

            if (strlen($value) > 0) {
                $attributes[$part] = $value;
            }
        }

        return $attributes;
    }
}
