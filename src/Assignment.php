<?php
namespace DemandwareXml;

class Assignment extends Base
{
    protected $element = 'category-assignment';

    /**
     * Create a new <category-assignment> element, for the product/category ids
     */
    public function __construct(string $objectId, string $categoryId)
    {
        // although passed as `product-id`, can actually represent a product/variation/set/bundle
        $this->attributes = ['product-id' => $objectId, 'category-id' => $categoryId];
    }

    /**
     * Sets the `mode` attribute to "delete"
     */
    public function setDeleted(): void
    {
        $this->attributes['mode'] = 'delete';
    }

    /**
     * Adds a <primary-flag> element with true/false value
     */
    public function setPrimary(bool $primary): void
    {
        $this->elements['primary-flag'] = $primary;
    }
}
