<?php

namespace DemandwareXml\Writer\Xml;

class NilEmptyWriter
{
    public function __construct(private XmlWriter $writer) {}

    public function writeElement($name, $content = null): bool
    {
        if (XmlFormatter::isEmptyValue($content)) {
            return $this->writeElementWithAttributes($name);
        }

        return $this->writer->writeElement($name, $content);
    }

    public function writeAttribute($name, $value): bool
    {
        return $this->writer->writeAttribute($name, $value);
    }

    public function writeElementWithAttributes($name, $content = null, array $attributes = []): bool
    {
        $this->writer->startElement($name);

        foreach ($attributes as $attrName => $attrContent) {
            $this->writeAttribute($attrName, $attrContent);
        }

        if (XmlFormatter::isEmptyValue($content)) {
            $this->writeAttribute('xsi:nil', 'true');
            $this->writeAttribute('xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
        } else {
            $this->writer->text($content);
        }

        $this->writer->endElement();

        return true;
    }
}
