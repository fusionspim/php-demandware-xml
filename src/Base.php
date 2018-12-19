<?php
namespace DemandwareXml;

abstract class Base
{
    protected $attributes = [];
    protected $element;
    protected $elements   = [];

    /**
     * Populates the name of the product/set/bundle/category for the <display-name xml:lang="x-default"> element
     */
    public function setName(string $name): void
    {
        $this->elements['display-name'] = $name;
    }

    /**
     * Populates flag elements - only `$online` is required.
     * $available is deprecated according to the XSD, but used so we support it
     */
    public function setFlags(bool $online, bool $searchable = null, bool $available = null, bool $both = null): void
    {
        $this->elements['online-flag'] = $online;

        if (! is_null($searchable)) {
            $this->elements['searchable-flag'] = $searchable;
        }

        if (! is_null($available)) {
            $this->elements['available-flag'] = $available;
        }

        if (! is_null($both)) {
            $this->elements['searchable-if-unavailable-flag'] = $both;
        }
    }

    /**
     * Populates <online-from> and <online-to> dates in Demandware format, if provided
     */
    public function setDates(string $from = null, string $to = null): void
    {
        if (! $this->isEmptyDate($from)) {
            $this->elements['online-from'] = str_replace(' ', 'T', $from);
        }

        if (! $this->isEmptyDate($to)) {
            $this->elements['online-to'] = str_replace(' ', 'T', $to);
        }
    }

    private function isEmptyDate(string $value = null): bool
    {
        return empty($value) || '0000-00-00' === mb_substr($value, 0, 10);
    }

    /**
     * Populates sitemap elements and can be called without parameters to use the sensible defaults
     * Pass null for any parameter to exclude the element
     *
     * @throws XmlException
     */
    public function setSitemap(?float $priority = null, ?bool $included = true, ?string $frequency = 'weekly'): void
    {
        if ($priority > 1) {
            throw new XmlException('Sitemap priority must be 1.0 or less');
        }

        if (! is_null($included)) {
            $this->elements['sitemap-included-flag'] = $included;
        }

        if (! is_null($frequency)) {
            $this->elements['sitemap-changefrequency'] = $frequency;
        }

        if (! is_null($priority)) {
            $this->elements['sitemap-priority'] = number_format($priority, 1);
        }
    }

    /**
     * Populates <page-attributes> child elements, all of which will be given a `xml:lang="x-default"` attribute
     */
    public function setPageAttributes(string $title = null, string $description = null, string $keywords = null, string $url = null): void
    {
        $elements = [
            'page-title'       => $title,
            'page-description' => $description,
            'page-keywords'    => $keywords,
            'page-url'         => $url,
        ];

        $xml = '';

        foreach ($elements as $key => $value) {
            $xml .= '<' . $key . ' xml:lang="x-default">' . Xml::escape($value) . '</' . $key . '>' . PHP_EOL;
        }

        $this->elements['page-attributes'] = $xml;
    }

    /**
     * Populates the <template> element used for categories and sets
     */
    public function setTemplate(string $value): void
    {
        $this->elements['template'] = $value;
    }

    /**
     * Populates <custom-attributes> child elements, from a mapping of attribute ids to values
     * Values can be empty and still exported, however attributes with null values aren't exported
     * Attributes are exported sorted alphabetically by id for consistency and ease of diffing exports...
     */
    public function setCustomAttributes(array $map): void
    {
        ksort($map);

        $xml = '';

        foreach ($map as $key => $value) {
            if (is_null($value)) {
                continue;
            }

            $xml .= '<custom-attribute attribute-id="' . Xml::escape($key) . '">';

            if (is_array($value)) {
                foreach ($value as $individual) {
                    if (is_null($individual)) {
                        continue;
                    }

                    $xml .= '<value>' . Xml::escape($individual) . '</value>' . PHP_EOL;
                }
            } else {
                $xml .= Xml::escape($value);
            }

            $xml .= '</custom-attribute>' . PHP_EOL;
        }

        $this->elements['custom-attributes'] = $xml;
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function getElement()
    {
        return $this->element;
    }

    public function getElements(): array
    {
        return $this->elements;
    }
}
