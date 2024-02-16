<?php

namespace DemandwareXml\Writer\Entity;

use DemandwareXml\Writer\EntityWriter\CustomAttributeWriter;
use DemandwareXml\Writer\Xml\XmlWriter;

class CustomAttribute implements WriteableEntityInteface
{
    public function __construct(public string $id, public $value = null)
    {
        // If more than 200 values are sent, Demandware will error and not update the product.
        if (is_array($this->value) && count($this->value) > 200) {
            $this->value = array_slice($this->value, 0, 200);
        }
    }

    public function write(XmlWriter $writer): void
    {
        (new CustomAttributeWriter($writer, $this))->write();
    }
}
