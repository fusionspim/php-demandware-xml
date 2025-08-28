<?php

namespace DemandwareXml\Writer\Entity;

use DemandwareXml\Writer\Xml\XmlWriter;

interface WriteableEntityInterface
{
    public function write(XmlWriter $writer): void;
}
