<?php

namespace DemandwareXml\Writer\EntityWriter;

use DemandwareXml\Writer\Entity\CustomAttribute;
use DemandwareXml\Writer\Xml\XmlFormatter;
use DemandwareXml\Writer\Xml\XmlWriter;

class CustomAttributeWriter
{
    public function __construct(private XmlWriter $writer, private CustomAttribute $customAttribute)
    {
    }

    public function write(): void
    {
        if (is_array($this->customAttribute->value)) {
            $this->writeMultiple();
        } else {
            $this->writeSingle();
        }
    }

    private function writeSingle(): void
    {
        $value = XmlFormatter::fromType($this->customAttribute->value);

        if (! XmlFormatter::isEmptyValue($value)) {
            $this->writer->writeElementWithAttributes('custom-attribute', XmlFormatter::fromType($this->customAttribute->value), [
                'attribute-id' => $this->customAttribute->id,
            ]);
        } else {
            $this->writer->writeEmptyElementWithAttributes('custom-attribute', [
                'attribute-id' => $this->customAttribute->id,
            ]);
        }
    }

    private function writeMultiple(): void
    {
        $values = XmlFormatter::filterEmptyValues($this->customAttribute->value);

        if (count($values) > 0) {
            $this->writer->startElement('custom-attribute');
            $this->writer->writeAttribute('attribute-id', $this->customAttribute->id);

            foreach ($values as $value) {
                $this->writer->writeElement('value', XmlFormatter::fromType($value));
            }

            $this->writer->endElement();
        } else {
            $this->writer->writeEmptyElementWithAttributes('custom-attribute', [
                'attribute-id' => $this->customAttribute->id,
            ]);
        }
    }
}
