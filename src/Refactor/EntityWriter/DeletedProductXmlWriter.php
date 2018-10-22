<?php
namespace DemandwareXml\Refactor\EntityWriter;

use DemandwareXml\Refactor\Entity\DeletedProduct;
use DemandwareXml\Refactor\Xml\XmlWriter;

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
            'mode'       => 'delete',
            'product-id' => $this->product->id,
        ]);
    }
}
