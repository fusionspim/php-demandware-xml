<?php
namespace FusionsPIM\DemandwareXml;

use \Exception;

abstract class XmlAbstract implements XmlInterface
{
    public $element;
    public $attributes = [];
    public $elements   = [];

    public function setName($name)
    {
        $this->elements['display-name'] = $name;
    }

    // only first required. available is deprecated in xsd, we shouldn't be using
    public function setFlags($online, $searchable = null, $available = null, $both = null)
    {
        $this->elements['online'] = $online;

        if (! is_null($searchable)) {
            $this->elements['searchable'] = $searchable;
        }

        if (! is_null($available)) {
            $this->elements['available'] = $available;
        }

        if (! is_null($both)) {
            $this->elements['searchable-if-unavailable'] = $both;
        }
    }

    // @todo: should $from be required if func called?
    public function setDates($from, $to = null)
    {
        $this->elements['online-from'] = str_replace(' ', 'T', $from);

        if (! empty($to)) {
            $this->elements['online-to'] = str_replace(' ', 'T', $to);
        }
    }

    // all have defaults so function optional, but can be called with null priority to exclude that element
    public function setSitemap($priority = 0.5, $included = true, $frequency = 'weekly')
    {
        if ($priority > 1) {
            throw new Exception('Sitemap priority must be 1.0 or less');
        }

        $this->elements['sitemap-included-flag']   = $included;
        $this->elements['sitemap-changefrequency'] = $frequency;

        if (! is_null($priority)) {
            $this->elements['sitemap-priority'] = $priority;
        }
    }

    public function setPageAttributes($title, $description, $keywords, $url)
    {
        $elements = [
            'page-title'       => $title,
            'page-description' => $description,
            'page-keywords'    => $keywords,
            'page-url'         => $url
        ];

        $xml = '';

        foreach ($elements as $key => $value) {
            $xml .= '<' . $key . ' xml:lang="x-default">' . XmlDocument::escape($value) . '</' . $key . '>' . PHP_EOL;
        }

        $this->elements['page-attributes'] = $xml;
    }

    public function setTemplate($value)
    {
        $this->elements['template'] = $value;
    }

    /**
     * Map custom attribute ids to values
     * Values can be empty, up to other side to decide if that's ok
     * Attributes are exported sorted alphabetically by id for consistency and ease of diffing exports...
     */
    public function setCustomAttributes(array $map)
    {
        ksort($map);

        $xml = '';

        foreach ($map as $key => $value) {
            $xml .= '<custom-attribute attribute-id="' . XmlDocument::escape($key) . '">';

            if (is_array($value)) {
                foreach ($value as $individual) {
                    $xml .= '<value>' . XmlDocument::escape($individual) . '</value>' . PHP_EOL;
                }
            } else {
                $xml .= XmlDocument::escape($value);
            }

            $xml .= '</custom-attribute>' . PHP_EOL;
        }

        $this->elements['custom-attributes'] = $xml;
    }
}
