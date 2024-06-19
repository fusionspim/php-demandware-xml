<?php

namespace DemandwareXml\Writer\EntityWriter;

use DemandwareXml\Writer\Entity\Variant;
use DemandwareXml\Writer\Xml\XmlWriter;

class VariantXmlWriter
{
    public function __construct(private XmlWriter $writer, private Variant $variant)
    {
    }

    public function write(): void
    {
        $this->writer->startElement('variation-attribute');
        $this->writer->ifNotEmpty()->writeAttribute('variation-attribute-id', $this->variant->id);
        $this->writer->ifNotEmpty()->writeAttribute('attribute-id', $this->variant->id);
        $this->writeDisplayValues();
        $this->writer->endElement();
    }

    private function writeDisplayValues(): void
    {
        if (count($this->variant->displayValues) === 0) {
            return;
        }

        $this->writer->startElement('variation-attribute-values');

        foreach ($this->variant->displayValues as $value => $displayValue) {
            $this->writer->startElement('variation-attribute-value');
            $this->writer->writeAttribute('value', $value);
            $this->writer->writeElementWithAttributes('display-value', $displayValue, ['xml:lang' => 'x-default']);
            $this->writer->endElement();
        }

        $this->writer->endElement();
    }
}
