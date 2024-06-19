<?php

namespace DemandwareXml\Writer\EntityWriter;

use DemandwareXml\Writer\Entity\DeletedProduct;
use DemandwareXml\Writer\Xml\XmlWriter;

class DeletedProductXmlWriter
{
    public function __construct(private XmlWriter $writer, private DeletedProduct $product) {}

    public function write(): void
    {
        $this->writer->writeEmptyElementWithAttributes('product', [
            'product-id' => $this->product->id,
            'mode'       => 'delete',
        ]);
    }
}
