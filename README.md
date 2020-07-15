# PHP Demandware XML

A PHP library for working with Demandware XML files.

**Exporting**: Supports category, product, variant and assignment files and allows elements to be added in the order that makes sense for your code - they'll be exported in the sequence specified by the XSD automatically, and custom attribute elements are ordered by attribute ids for consistency between exports.

**Validation**: Automatically validates files up to 1Gb (against the included XSD schemas) after exporting.

**Parsing**: Retrieve category, assignment, product, variation, set and bundle information from an XML file, either as an array, or yield using generators. Maps IDs to the more useful elements and, optionally, attributes.

## Installation

Run `composer require fusionspim/php-demandware-xml`.

## Usage

See tests for examples on how to use, along with the files within `tests/fixtures` for output examples.

## Future plans

- Export all root elements sorted by their first attribute value (similar to custom attributes) to ease manual comparison/diffs.
- Convert nested elements currently implemented with string concatenation/loops to nodes (they work, but would be nice for consistency/robustness) & deal with the `setClassification()`/`catalog-id` hack ;)
- [Canonicalise](https://en.wikipedia.org/wiki/Canonical_XML) two sets of XML files and report on different IDs, elements and attributes (useful when different systems/processes have exported the files)