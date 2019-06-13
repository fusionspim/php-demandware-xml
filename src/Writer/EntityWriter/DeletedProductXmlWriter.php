<?php
namespace DemandwareXml\Writer\EntityWriter;

use DemandwareXml\Writer\Entity\DeletedProduct;
use DemandwareXml\Writer\Xml\XmlWriter;

class DeletedProductXmlWriter
{
    private $writer;
    private $product;

    public function __construct(XmlWriter $writer, DeletedProduct $product)
    {
        $this->writer  = $writer;
        $this->product = $product;
    }

    public function write(): void
    {
        $this->writer->writeEmptyElementWithAttributes('product', [
            'product-id' => $this->product->id,
            'mode'       => 'delete',
        ]);
    }
}
