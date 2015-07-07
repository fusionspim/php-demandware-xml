<?php
namespace FusionsPIM\DemandwareXml;

class XmlVariant extends XmlAbstract
{
    protected $element = 'variation-attribute';

    /**
     * Create a new <variation-attribute> element, with $id populating the `variation-attribute-id` and `attribute-id` attributes
     *
     * @param null $id
     */
    public function __construct($id = null)
    {
        $this->attributes = ['variation-attribute-id' => $id, 'attribute-id' => $id];
    }

    /**
     * Populates <variation-attribute-values> child elements, from a mapping of attribute values to names
     *
     * @param array $map
     */
    public function addTags($map = [])
    {
        $xml = '';

        foreach ($map as $id => $value) {
            $xml .= '<variation-attribute-value value="' . XmlDocument::escape($id) . '">
                        <display-value xml:lang="x-default">' . XmlDocument::escape($value) . '</display-value>
                    </variation-attribute-value>';
        }

        $this->elements['variation-attribute-values'] = $xml;
    }
}
