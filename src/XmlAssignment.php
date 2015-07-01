<?php
namespace FusionsPIM\DemandwareXml;

class XmlAssignment extends XmlAbstract
{
    public $element = 'category-assignment';

    public function __construct($objectId = null, $categoryId = null)
    {
        // although passed as `product-id`, can actually represent a product/variation/set/bundle
        $this->attributes = ['product-id' => $objectId, 'category-id' => $categoryId];
    }

    public function setDeleted()
    {
        $this->attributes['mode'] = 'delete';
    }

    public function setPrimary($primary)
    {
        $this->elements['primary-flag'] = $primary;
    }
}
