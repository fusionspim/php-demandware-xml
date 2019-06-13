<?php
namespace DemandwareXml\Writer\Entity;

use DemandwareXml\Writer\Xml\XmlWriter;

interface WriteableEntityInteface
{
    public function write(XmlWriter $writer): void;
}
