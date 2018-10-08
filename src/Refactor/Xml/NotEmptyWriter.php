<?php
namespace DemandwareXml\Refactor\Xml;

class NotEmptyWriter
{
    private $writer;

    public function __construct(XmlWriter $writer)
    {
        $this->writer = $writer;
    }

    public function writeElement($name, $content = null): bool
    {
        if (XmlFormatter::isEmptyValue($content)) {
            return true;
        }

        return $this->writer->writeElement($name, $content);
    }

    public function writeAttribute($name, $value): bool
    {
        if (XmlFormatter::isEmptyValue($value)) {
            return true;
        }

        return $this->writer->writeAttribute($name, $value);
    }

    public function writeElementWithAttributes($name, $content = null, array $attributes = []): bool
    {
        if (XmlFormatter::isEmptyValue($content)) {
            return true;
        }

        $this->writer->startElement($name);

        foreach ($attributes as $attrName => $attrContent) {
            $this->writeAttribute($attrName, $attrContent);
        }

        $this->writer->text($content);
        $this->writer->endElement();

        return true;
    }

    public function writeEmptyElementWithAttributes($name, array $attributes = []): bool
    {
        $attributes = array_filter($attributes, function ($value) {
            return XmlFormatter::isEmptyValue($value) === false;
        });

        if (count($attributes) === 0) {
            return true;
        }

        $this->writer->startElement($name);

        foreach ($attributes as $attrName => $attrContent) {
            $this->writer->writeAttribute($attrName, $attrContent);
        }

        $this->writer->endElement();

        return true;
    }
}
