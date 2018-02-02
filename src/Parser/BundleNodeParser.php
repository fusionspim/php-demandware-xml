<?php
namespace DemandwareXml\Parser;

use SimpleXMLElement;
use XMLReader;

class BundleNodeParser implements NodeParserInterface
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

        if (! isset($this->element->{'bundled-products'})) {
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

        foreach ($this->element->{'bundled-products'}->{'bundled-product'} as $variation) {
            $quantity = (int) ($variation->{'quantity'} ?? 0);

            $details['variations'][(string) $variation['product-id']] = $quantity;
        }

        return [
            'id'   => (string) $this->element['product-id'],
            'data' => $details,
        ];
    }
}
