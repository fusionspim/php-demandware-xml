<?php
namespace DemandwareXml\Parser;

use SimpleXMLElement;

class SetSimpleNodeParser extends SetNodeParser implements NodeParserInterface
{
    protected function getCommonDetails(SimpleXMLElement $element)
    {
        return $this->commonDetails($element, true);
    }
}
