<?php
namespace DemandwareXml\Parser;

use SimpleXMLElement;
use XMLReader;

class ProductNodeParser implements NodeParserInterface
{
    use CommonDetailsNodeParserTrait;
    protected SimpleXMLElement|null $element = null;

    public function __construct(protected XMLReader $reader)
    {
    }

    public function isMatch(): bool
    {
        if ($this->reader->nodeType !== XMLReader::ELEMENT || $this->reader->localName !== 'product') {
            return false;
        }

        $this->element = new SimpleXMLElement($this->reader->readOuterXml());

        return isset($this->element->{'variations'});
    }

    protected function getCommonDetails(SimpleXMLElement $element)
    {
        return $this->commonDetails($element);
    }

    public function parse(): array
    {
        $details = $this->getCommonDetails($this->element);

        foreach ($this->element->{'variations'}->{'variants'}->{'variant'} as $variation) {
            $details['variations'][(string) $variation['product-id']] = isset($variation['default']);
        }

        return [
            'id'   => (string) $this->element['product-id'],
            'data' => $details,
        ];
    }
}
