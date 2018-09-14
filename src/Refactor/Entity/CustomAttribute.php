<?php
namespace DemandwareXml\Refactor\Entity;

class CustomAttribute implements EntityInterface
{
    public $id;
    public $value;

    public function __construct(string $id, $value = null)
    {
        $this->id    = $id;
        $this->value = $value;
    }
}
