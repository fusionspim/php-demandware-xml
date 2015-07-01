<?php
namespace FusionsPIM\DemandwareXml;

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
        $this->elements['sitemap-included-flag']   = $included;
        $this->elements['sitemap-changefrequency'] = $frequency;

        if (! is_null($priority)) {
            $this->elements['sitemap-priority'] = $priority;
        }
    }

    // @todo: dirty, but works and encapsulates the implementation within library for now...
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
            $xml .= '<' . $key . ' xml:lang="x-default">' . $this->escape($value) . '</' . $key . '>' . PHP_EOL;
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
     *
     * @todo: dirty, but works and encapsulates the implementation within library for now...
     */
    public function setCustomAttributes(array $map)
    {
        ksort($map);

        $xml = '';

        foreach ($map as $key => $value) {
            $xml .= '<custom-attribute attribute-id="' . $this->escape($key) . '">';

            if (is_array($value)) {
                foreach ($value as $individual) {
                    $xml .= '<value>' . $this->escape($individual) . '</value>' . PHP_EOL;
                }
            } else {
                $xml .= $this->escape($value);
            }

            $xml .= '</custom-attribute>' . PHP_EOL;
        }

        $this->elements['custom-attributes'] = $xml;
    }

    // @todo: dupe from XmlDocument to be eliminated once all string concat is removed!
    protected function escape($value)
    {
        if (is_bool($value)) {
            return ($value ? 'true' : 'false');
        } else {
            $value = html_entity_decode($value); // not sure why, other than back compat

            return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8', false);
        }
    }
}
