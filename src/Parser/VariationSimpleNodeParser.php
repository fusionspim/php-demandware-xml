<?php

namespace DemandwareXml\Parser;

use SimpleXMLElement;

class VariationSimpleNodeParser extends VariationNodeParser implements NodeParserInterface
{
    protected function getCommonDetails(SimpleXMLElement $element)
    {
        return $this->commonDetails($element, true);
    }
}
