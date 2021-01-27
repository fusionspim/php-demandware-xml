<?php
namespace DemandwareXml\Writer\Xml;

class NilEmptyWriter
{
    private $writer;

    public function __construct(XmlWriter $writer)
    {
        $this->writer = $writer;
    }

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
        } else {
            $this->writer->text($content);
        }

        $this->writer->endElement();

        return true;
    }

    public function writeEmptyElementWithAttributes($name, array $attributes = []): bool
    {
        $this->writer->startElement($name);

        $this->writeAttribute('xsi:nil', 'true');

        foreach ($attributes as $attrName => $attrContent) {
            $this->writer->writeAttribute($attrName, $attrContent);
        }

        $this->writer->endElement();

        return true;
    }
}
