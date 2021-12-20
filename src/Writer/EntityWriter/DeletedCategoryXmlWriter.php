<?php
namespace DemandwareXml\Writer\EntityWriter;

use DemandwareXml\Writer\Entity\DeletedCategory;
use DemandwareXml\Writer\Xml\XmlWriter;

class DeletedCategoryXmlWriter
{
    public function __construct(private XmlWriter $writer, private DeletedCategory $category)
    {
    }

    public function write(): void
    {
        $this->writer->writeEmptyElementWithAttributes('category', [
            'category-id' => $this->category->id,
            'mode'        => 'delete',
        ]);
    }
}
