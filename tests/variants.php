<?php
use \FusionsPIM\DemandwareXml\XmlDocument;
use \FusionsPIM\DemandwareXml\XmlVariant;

$document = new XmlDocument('TestCatalog');
$variants = ['colour' => ['red' => 'Red', 'blue' => 'Blue'], 'height' => ['H1' => 'Grande', 'H2' => 'Tall']];

foreach ($variants as $variant => $values) {
    $element = new XmlVariant($variant);
    $element->addTags($values);

    $document->addObject($element);
}

$document->save('variants.xml');
