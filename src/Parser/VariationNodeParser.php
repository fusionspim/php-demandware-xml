<?php

namespace DemandwareXml\Parser;

use SimpleXMLElement;
use XMLReader;

class VariationNodeParser implements NodeParserInterface
{
    use CommonDetailsNodeParserTrait;
    protected ?SimpleXMLElement $element = null;

    public function __construct(protected XMLReader $reader) {}

    public function isMatch(): bool
    {
        if ($this->reader->nodeType !== XMLReader::ELEMENT || $this->reader->localName !== 'product') {
            return false;
        }

        $this->element = new SimpleXMLElement($this->reader->readOuterXml());

        return ! (isset($this->element->{'bundled-products'}) || isset($this->element->{'product-set-products'}) || isset($this->element->{'variations'}));
    }

    protected function getCommonDetails(SimpleXMLElement $element)
    {
        return $this->commonDetails($element);
    }

    public function parse(): array
    {
        return [
            'id'   => (string) $this->element['product-id'],
            'data' => $this->getCommonDetails($this->element),
        ];
    }
}
