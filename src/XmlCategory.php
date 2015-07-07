<?php
namespace FusionsPIM\DemandwareXml;

class XmlCategory extends XmlAbstract
{
    protected $element = 'category';

    /**
     * Create a new <category> element, with $id populating the `category-id` attribute
     *
     * @param null $id
     */
    public function __construct($id = null)
    {
        $this->attributes = ['category-id' => $id];
    }

    /**
     * Relates to another category id
     *
     * @param $value
     */
    public function setParent($value)
    {
        $this->elements['parent'] = $value;
    }
}
