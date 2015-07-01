<?php
namespace FusionsPIM\DemandwareXml;

class XmlCategory extends XmlAbstract
{
    public $element = 'category';

    public function __construct($id = null)
    {
        $this->attributes = ['category-id' => $id];
    }

    // relates to another category id
    public function setParent($value)
    {
        $this->elements['parent'] = $value;
    }
}
