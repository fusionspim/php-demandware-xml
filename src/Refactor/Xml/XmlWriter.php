<?php
namespace DemandwareXml\Refactor\Xml;

use DemandwareXml\Refactor\Entity\EntityInterface;
use DemandwareXml\Refactor\Serializer\SerializerInterface;
use InvalidArgumentException;
use SplFileObject;
use XMLWriter as PhpXmlWriter;

// Enhances XMLWriter with additional functionality and Demandware specific formatting.
class XmlWriter extends PhpXmlWriter
{
    const NAMESPACE    = 'http://www.demandware.com/xml/impex/catalog/2006-10-31';
    const INDENT_SPACE = ' ';

    public $entityMap = [];
    private $notEmptyWriter;

    public function openFile(string $filename): void
    {
        new SplFileObject($filename, 'w');
        $this->openUri($filename);
    }

    public function setIndentDefaults(): void
    {
        $this->setIndent(true);
        $this->setIndentString(str_repeat(self::INDENT_SPACE, 2));
    }

    public function startCatalog(string $catalogId): void
    {
        $this->startDocument('1.0', 'UTF-8');
        $this->startElement('catalog');
        $this->writeAttribute('xmlns', self::NAMESPACE);
        $this->writeAttribute('catalog-id', $catalogId);
    }

    public function endCatalog(): void
    {
        $this->endElement();
        $this->endDocument();
    }

    public function ifNotEmpty(): NotEmptyWriter
    {
        if (! $this->notEmptyWriter) {
            $this->notEmptyWriter = new NotEmptyWriter($this);
        }

        return $this->notEmptyWriter;
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

    public function writeEntity(EntityInterface $object): void
    {
        if (! isset($this->entityMap[get_class($object)])) {
            throw new InvalidArgumentException('The "' . get_class($object) . '" entity is not registered in the entity map.');
        }

        $class = $this->entityMap[get_class($object)];

        if (! is_subclass_of($class, SerializerInterface::class)) {
            throw new InvalidArgumentException('Entity serializer class "' . $class . '" must implement ' . SerializerInterface::class);
        }

        $serializer = new $class($this, $object);
        $serializer->serialize();
    }
}
