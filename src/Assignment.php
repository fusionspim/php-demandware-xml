<?php
namespace FusionsPIM\DemandwareXml;

class Assignment extends Base
{
    protected $element = 'category-assignment';

    /**
     * Create a new <category-assignment> element, for the product/category ids
     *
     * @param null $objectId
     * @param null $categoryId
     */
    public function __construct($objectId = null, $categoryId = null)
    {
        // although passed as `product-id`, can actually represent a product/variation/set/bundle
        $this->attributes = ['product-id' => $objectId, 'category-id' => $categoryId];
    }

    /**
     * Sets the `mode` attribute to "delete"
     */
    public function setDeleted()
    {
        $this->attributes['mode'] = 'delete';
    }

    /**
     * Adds a <primary-flag> element with true/false value
     *
     * @param $primary
     */
    public function setPrimary($primary)
    {
        $this->elements['primary-flag'] = $primary;
    }
}
