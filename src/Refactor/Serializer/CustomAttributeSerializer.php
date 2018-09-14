<?php
namespace DemandwareXml\Refactor\Serializer;

use DemandwareXml\Refactor\Entity\EntityInterface;
use DemandwareXml\Refactor\Helper\Formatter;
use DemandwareXml\Refactor\Xml\Writer;

class CustomAttributeSerializer implements SerializerInterface
{
    private $writer;
    private $customAttribute;

    public function __construct(Writer $writer, EntityInterface $customAttribute)
    {
        $this->writer          = $writer;
        $this->customAttribute = $customAttribute;
    }

    public function serialize(): void
    {
        if (is_array($this->customAttribute->value)) {
            $this->serializeMultiple();
        } else {
            $this->serializeSingle();
        }
    }

    private function serializeSingle()
    {
        $this->writer->ifNotEmpty()->writeElementWithAttributes('custom-attribute', Formatter::fromType($this->customAttribute->value), [
            'attribute-id' => $this->customAttribute->id
        ]);
    }

    private function serializeMultiple()
    {
        $values = Formatter::filterEmpty($this->customAttribute->value);

        if (count($values) === 0) {
            return;
        }

        $this->writer->startElement('custom-attribute');

        foreach ($values as $value) {
            $this->writer->writeElement('value', Formatter::fromType($value));
        }

        $this->writer->endElement();
    }
}
