<?php
namespace DemandwareXml\Parser;

use XMLReader;

interface NodeParserInterface
{
    public function __construct(XMLReader $reader);
    public function isMatch(): bool;
    public function parse(): array;
}
