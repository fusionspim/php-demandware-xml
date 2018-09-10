<?php
namespace DemandwareXml\Parser;

use SimpleXMLElement;

trait CommonDetailsNodeParserTrait
{
    protected function commonDetails(SimpleXMLElement $element, bool $skipAttributes = false): array
    {
        $details = [];

        if (! $skipAttributes) {
            $details = [
                'attributes' => $this->customAttributes($element),
                'page'       => $this->pageAttributes($element),
            ];
        }

        $map = [
            'description'             => 'long-description',
            'name'                    => 'display-name',
            'start'                   => 'online-from',
            'classification'          => 'classification-category',
            'online'                  => 'online-flag',
            'searchable'              => 'searchable-flag',
            'parent'                  => 'parent',
            'tax'                     => 'tax-class-id',
            'brand'                   => 'brand',
            'sitemap-included-flag'   => 'sitemap-included-flag',
            'sitemap-changefrequency' => 'sitemap-changefrequency',
            'sitemap-priority'        => 'sitemap-priority',
        ];

        foreach ($map as $name => $source) {
            $cleansed = html_entity_decode(trim((string) $element->{$source}));

            if (mb_strlen($cleansed) > 0) {
                $details[$name] = $cleansed;
            }
        }

        // If they exist, online/searchable will always be a true/false string, so cast for ease of use.
        foreach (['online', 'searchable'] as $name) {
            if (isset($details[$name])) {
                $details[$name] = filter_var($details[$name], FILTER_VALIDATE_BOOLEAN);
            }
        }

        // Convert the tax string to a meaningful number.
        if (isset($details['tax'])) {
            $details['tax'] = (float) str_replace(['TAX_', '_'], ['', '.'], $details['tax']);
        }

        ksort($details);

        return $details;
    }

    protected function customAttributes(SimpleXMLElement $element): array
    {
        if (! isset($element->{'custom-attributes'}->{'custom-attribute'})) {
            return [];
        }

        $attributes = [];

        foreach ($element->{'custom-attributes'}->{'custom-attribute'} as $attribute) {
            if (isset($attribute->{'value'})) {
                $value = [];

                foreach ($attribute->{'value'} as $item) {
                    $value[] = trim((string) $item);
                }
            } else {
                $value = trim((string) $attribute);

                // Cast strings to booleans (only needed for single values, as multi-value booleans make no sense).
                if ('true' === $value || 'false' === $value) {
                    $value = ('true' === $value);
                }
            }

            $attributes[(string) $attribute['attribute-id']] = $value;
        }

        ksort($attributes);

        return $attributes;
    }

    protected function pageAttributes(SimpleXMLElement $element): array
    {
        $attributes = [];

        foreach (['title', 'description', 'keywords', 'url'] as $part) {
            $value = html_entity_decode(trim((string) $element->{'page-attributes'}->{'page-' . $part}));

            if (mb_strlen($value) > 0) {
                $attributes[$part] = $value;
            }
        }

        return $attributes;
    }
}
