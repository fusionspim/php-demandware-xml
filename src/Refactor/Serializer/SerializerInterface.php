<?php
namespace DemandwareXml\Refactor\Serializer;

use DemandwareXml\Refactor\Entity\EntityInterface;
use DemandwareXml\Refactor\Xml\Writer;

interface SerializerInterface
{
    public function __construct(Writer $writer, EntityInterface $entity);
    public function serialize(): void;
}
