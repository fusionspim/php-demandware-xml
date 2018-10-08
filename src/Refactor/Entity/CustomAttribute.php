<?php
namespace DemandwareXml\Refactor\Entity;

use DemandwareXml\Refactor\EntityWriter\CustomAttributeWriter;
use DemandwareXml\Refactor\EntityWriter\EntityWriterInterface;
use DemandwareXml\Refactor\Xml\XmlWriter;

class CustomAttribute implements EntityWriterInterface
{
    public $id;
    public $value;

    public function __construct(string $id, $value = null)
    {
        $this->id    = $id;
        $this->value = $value;
    }

    public function writeXml(XmlWriter $writer): void
    {
        (new CustomAttributeWriter($writer, $this))->write();
    }
}
