<?php
namespace DemandwareXml\Refactor\Xml;

use DemandwareXml\Refactor\Helper\Formatter;
use XMLWriter;

class NotEmptyWriter
{
    private $writer;

    public function __construct(XMLWriter $writer)
    {
        $this->writer = $writer;
    }

    public function writeElement($name, $content = null): bool
    {
        if (Formatter::isEmpty($content)) {
            return true;
        }

        return $this->writer->writeElement($name, $content);
    }

    public function writeAttribute($name, $value): bool
    {
        if (Formatter::isEmpty($value)) {
            return true;
        }

        return $this->writer->writeAttribute($name, $value);
    }

    public function writeElementWithAttributes($name, $content = null, array $attributes = []): bool
    {
        if (Formatter::isEmpty($content)) {
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
            return Formatter::isEmpty($value) === false;
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
