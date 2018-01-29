<?php
namespace DemandwareXml;

use Generator;
use SimpleXMLElement;
use XMLReader;

class StreamingParser
{
    const ITEM_ASSIGNMENT = 'Assignment';
    const ITEM_CATEGORY   = 'Category';
    const ITEM_BUNDLE     = 'Bundle';
    const ITEM_SET        = 'Set';
    const ITEM_PHOTO      = 'Photo';
    const ITEM_PRODUCT    = 'Product';
    const ITEM_VARIATION  = 'Variation';

    const ITEM_NODES = [
        self::ITEM_ASSIGNMENT => 'category-assignment',
        self::ITEM_CATEGORY   => 'category',
        self::ITEM_BUNDLE     => 'product',
        self::ITEM_SET        => 'product',
        self::ITEM_PHOTO      => 'product',
        self::ITEM_PRODUCT    => 'product',
        self::ITEM_VARIATION  => 'product',
    ];

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

    protected function parseNodes(string $item): Generator
    {
        $node = static::ITEM_NODES[$item];

        try {
            $reader = $this->getXmlReader();

            while ($reader->read()) {
                if ($reader->nodeType !== XMLReader::ELEMENT || $reader->localName !== $node) {
                    continue;
                }

                $element = new SimpleXMLElement($reader->readOuterXml());

                if (
                    in_array($item, [static::ITEM_ASSIGNMENT, static::ITEM_CATEGORY]) ||
                    $item === static::ITEM_PHOTO ||
                    ($item === static::ITEM_BUNDLE && isset($element->{'bundled-products'})) ||
                    ($item === static::ITEM_SET && isset($element->{'product-set-products'})) ||
                    ($item === static::ITEM_PRODUCT && isset($element->{'variations'})) ||
                    ($item === static::ITEM_VARIATION && ! isset($element->{'bundled-products'}) && ! isset($element->{'product-set-products'}) && ! isset($element->{'variations'}))
                ) {
                    yield $element;
                }
            }
        } finally {
            $reader->close();
        }
    }

    public function getAssignments(): Generator
    {
        foreach ($this->parseNodes(static::ITEM_ASSIGNMENT) as $element) {
            $assignment = $this->extractAssignment($element); // @todo: Use array destructuring when on PHP 7.1.
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

    public function getBundles(): Generator
    {
        foreach ($this->parseNodes(static::ITEM_BUNDLE) as $element) {
            $bundle = $this->extractBundle($element); // @todo: Use array destructuring when on PHP 7.1.
            yield key($bundle) => reset($bundle);
        }
    }

    protected function extractBundle(SimpleXMLElement $element): array
    {
        $details = $this->commonDetails($element);

        foreach ($element->{'bundled-products'}->{'bundled-product'} as $variation) {
            $quantity = (int) ($variation->{'quantity'} ?? 0);

            $details['variations'][(string) $variation['product-id']] = $quantity;
        }

        return [(string) $element['product-id'] => $details];
    }

    public function getCategories(): Generator
    {
        foreach ($this->parseNodes(static::ITEM_CATEGORY) as $element) {
            $category = $this->extractCategory($element); // @todo: Use array destructuring when on PHP 7.1.
            yield key($category) => reset($category);
        }
    }

    protected function extractCategory(SimpleXMLElement $element): array
    {
        return [(string) $element['category-id'] => $this->commonDetails($element)];
    }

    public function getPhotos(): Generator
    {
        foreach ($this->parseNodes(static::ITEM_PHOTO) as $element) {
            $photo = $this->extractPhoto($element); // @todo: Use array destructuring when on PHP 7.1.
            yield key($photo) => reset($photo);
        }
    }

    protected function extractPhoto(SimpleXMLElement $element): array
    {
        $details = [
            'enriched' => false,
            'images'   => [],
        ];

        $total = count($element->{'custom-attribute'});

        for ($i = 0; $i < $total; $i++) {
            if (! isset($element->{'custom-attribute'}[$i])) {
                continue;
            }

            $name = (string) $element->{'custom-attribute'}[$i];
            $type = (string) $element->{'custom-attribute'}[$i]['attribute-id'];

            if ($type === 'productEnriched') {
                $details['enriched'] = filter_var($name, FILTER_VALIDATE_BOOLEAN);
            } else {
                switch ($type) {
                    case 'primaryImage':
                        $type = 'main';
                        break;
                    case 'imageSwatch':
                        $type = 'swatch';
                        break;
                    default:
                        $type = 'image' . substr($type, -1);
                        break;
                }

                $details['images'][$type] = $name;
            }
        }

        return [(string) $element['product-id'] => $details];
    }

    public function getProducts(): Generator
    {
        foreach ($this->parseNodes(static::ITEM_PRODUCT) as $element) {
            $product = $this->extractProduct($element); // @todo: Use array destructuring when on PHP 7.1.
            yield key($product) => reset($product);
        }
    }

    protected function extractProduct(SimpleXMLElement $element): array
    {
        $details = $this->commonDetails($element);

        foreach ($element->{'variations'}->{'variants'}->{'variant'} as $variation) {
            $details['variations'][(string) $variation['product-id']] = isset($variation['default']);
        }

        return [(string) $element['product-id'] => $details];
    }

    public function getSets(): Generator
    {
        foreach ($this->parseNodes(static::ITEM_SET) as $element) {
            $set = $this->extractSet($element); // @todo: Use array destructuring when on PHP 7.1.
            yield key($set) => reset($set);
        }
    }

    protected function extractSet(SimpleXMLElement $element)
    {
        $details = $this->commonDetails($element);

        foreach ($element->{'product-set-products'}->{'product-set-product'} as $product) {
            $details['products'][] = (string) $product['product-id'];
        }

        return [(string) $element['product-id'] => $details];
    }

    public function getVariations(): Generator
    {
        foreach ($this->parseNodes(static::ITEM_VARIATION) as $element) {
            $variation = $this->extractVariation($element); // @todo: Use array destructuring when on PHP 7.1.
            yield key($variation) => reset($variation);
        }
    }

    protected function extractVariation(SimpleXMLElement $element)
    {
        return [(string) $element['product-id'] => $this->commonDetails($element)];
    }

    protected function commonDetails(SimpleXMLElement $element): array
    {
        $details = [];

        if (! $this->skipAttributes) {
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

        // If they exist, online/searchable will always be a true/false string, so cast for ease of use.
        foreach (['online', 'searchable'] as $name) {
            if (isset($details[$name])) {
                $details[$name] = filter_var($details[$name], FILTER_VALIDATE_BOOLEAN);
            }
        }

        // Convert the tax string to a meaningful number.
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

                // Cast strings to booleans (only needed for single values, as multi-value booleans make no sense).
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
