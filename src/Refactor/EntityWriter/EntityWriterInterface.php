<?php
namespace DemandwareXml\Refactor\EntityWriter;

use DemandwareXml\Refactor\Xml\XmlWriter;

interface EntityWriterInterface
{
    public function writeXml(XmlWriter $writer): void;
}
