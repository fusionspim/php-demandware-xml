<?php
namespace DemandwareXml\Parser;

use SimpleXMLElement;
use XMLReader;

class SetNodeParser implements NodeParserInterface
{
    use CommonDetailsNodeParserTrait;

    protected $reader;
    protected $element;

    public function __construct(XMLReader $reader)
    {
        $this->reader = $reader;
    }

    public function isMatch(): bool
    {
        if ($this->reader->nodeType !== XMLReader::ELEMENT || $this->reader->localName !== 'product') {
            return false;
        }

        $this->element = new SimpleXMLElement($this->reader->readOuterXml());

        if (! isset($this->element->{'product-set-products'})) {
            return false;
        }

        return true;
    }

    protected function getCommonDetails(SimpleXMLElement $element)
    {
        return $this->commonDetails($element);
    }

    public function parse(): array
    {
        $details = $this->getCommonDetails($this->element);

        foreach ($this->element->{'product-set-products'}->{'product-set-product'} as $product) {
            $details['products'][] = (string) $product['product-id'];
        }

        return [
            'id'   => (string) $this->element['product-id'],
            'data' => $details,
        ];
    }
}
