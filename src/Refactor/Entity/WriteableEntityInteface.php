<?php
namespace DemandwareXml\Refactor\Entity;

use DemandwareXml\Refactor\Xml\XmlWriter;

interface WriteableEntityInteface
{
    public function write(XmlWriter $writer): void;
}
