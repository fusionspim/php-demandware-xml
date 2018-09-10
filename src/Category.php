<?php
namespace DemandwareXml;

class Category extends Base
{
    protected $element = 'category';

    /**
     * Create a new <category> element, with $id populating the `category-id` attribute
     */
    public function __construct(string $id = null)
    {
        $this->attributes = ['category-id' => $id];
    }

    /**
     * Relates to another category id
     */
    public function setParent(string $value): void
    {
        $this->elements['parent'] = $value;
    }

    /**
     * Sets the `mode` attribute to "delete"
     */
    public function setDeleted(): void
    {
        $this->attributes['mode'] = 'delete';
    }
}
