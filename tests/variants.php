<?php
use \DemandwareXml\Document;
use \DemandwareXml\Variant;
use \DemandwareXml\XmlException;

$document = new Document('TestCatalog');
$variants = ['colour' => ['red' => 'Red', 'blue' => 'Blue'], 'height' => ['H1' => 'Grande', 'H2' => 'Tall']];

foreach ($variants as $variant => $values) {
    $element = new Variant($variant);
    $element->addTags($values);

    $document->addObject($element);
}

try {
    $document->save('out/variants.xml');
} catch (XmlException $e) {
    echo $e->getMessage();
}
