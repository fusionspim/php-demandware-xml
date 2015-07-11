<?php
use \FusionsPIM\DemandwareXml\Document;
use \FusionsPIM\DemandwareXml\Variant;

$document = new Document('TestCatalog');
$variants = ['colour' => ['red' => 'Red', 'blue' => 'Blue'], 'height' => ['H1' => 'Grande', 'H2' => 'Tall']];

foreach ($variants as $variant => $values) {
    $element = new Variant($variant);
    $element->addTags($values);

    $document->addObject($element);
}

$document->save('out/variants.xml');
