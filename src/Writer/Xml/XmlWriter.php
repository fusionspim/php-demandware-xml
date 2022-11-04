<?php

namespace DemandwareXml\Writer\Xml;

use DemandwareXml\Writer\Entity\WriteableEntityInteface;
use SplFileObject;
use XMLWriter as PhpXmlWriter;

// Enhances XMLWriter with additional functionality and Demandware specific formatting.
class XmlWriter extends PhpXmlWriter
{
    public const NAMESPACE    = 'http://www.demandware.com/xml/impex/catalog/2006-10-31';
    public const INDENT_SPACE = ' ';

    private NotEmptyWriter|null $notEmptyWriter = null;
    private NilEmptyWriter|null $nilEmptyWriter = null;
    private int $bufferLimit                    = 100;
    private int $entityCount                    = 0;

    public function openFile(string $filename): bool
    {
        new SplFileObject($filename, 'w');
        $this->openUri($filename);

        return true;
    }

    public function initialise(string $catalogId): void
    {
        $this->setIndentDefaults();
        $this->startDocument('1.0', 'UTF-8'); // @todo: Perhaps values should come from the XSD file?
        $this->startCatalog($catalogId);
    }

    public function finalise(): void
    {
        $this->endCatalog();
        $this->endDocument();
        $this->flush();
    }

    public function setBufferLimit(int $bufferLimit): void
    {
        $this->bufferLimit = $bufferLimit;
    }

    public function setIndentDefaults(): bool
    {
        $this->setIndent(true);
        $this->setIndentString(str_repeat(self::INDENT_SPACE, 2));

        return true;
    }

    public function startCatalog(string $catalogId): bool
    {
        $this->startElement('catalog');
        $this->writeAttribute('xmlns', self::NAMESPACE);
        $this->writeAttribute('catalog-id', $catalogId);

        return true;
    }

    public function endCatalog(): bool
    {
        $this->endElement();

        return true;
    }

    public function ifNotEmpty(): NotEmptyWriter
    {
        if (! $this->notEmptyWriter) {
            $this->notEmptyWriter = new NotEmptyWriter($this);
        }

        return $this->notEmptyWriter;
    }

    public function nilIfEmpty(): NilEmptyWriter
    {
        if (! $this->nilEmptyWriter) {
            $this->nilEmptyWriter = new NilEmptyWriter($this);
        }

        return $this->nilEmptyWriter;
    }

    public function writeElementWithAttributes($name, $content = null, array $attributes = []): bool
    {
        $this->startElement($name);

        foreach ($attributes as $attrName => $attrContent) {
            $this->writeAttribute($attrName, $attrContent);
        }

        $this->text($content);
        $this->endElement();

        return true;
    }

    public function writeEmptyElement($name): bool
    {
        $this->writeElement($name);

        return true;
    }

    public function writeEmptyElementWithAttributes($name, array $attributes = []): bool
    {
        $this->startElement($name);

        foreach ($attributes as $attrName => $attrContent) {
            $this->writeAttribute($attrName, $attrContent);
        }

        $this->endElement();

        return true;
    }

    public function writeEntity(WriteableEntityInteface $entity): void
    {
        $entity->write($this);
    }

    public function writeFlushableEntity(WriteableEntityInteface $entity): void
    {
        $this->writeEntity($entity);

        if (++$this->entityCount % $this->bufferLimit === 0) {
            $this->flush();
        }
    }
}
