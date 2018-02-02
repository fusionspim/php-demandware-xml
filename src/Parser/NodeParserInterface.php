<?php
namespace DemandwareXml\Parser;

use XMLReader;

interface NodeParserInterface
{
    // Give the parser the current node.
    public function __construct(XMLReader $reader);

    // Determine if the give node is a match for parsing.
    public function isMatch(): bool;

    // Parse the given node into an array.
    public function parse(): array;
}
