# PHP Demandware XML
A PHP library for working with Demandware XML files.

**Exporting:** Supports category, product, variant and assignment files and allows elements to be added in the order that makes sense for your code - they'll be exported in the sequence specified by the XSD automatically, and custom attribute elements are ordered by attribute ids for consistency between exports

**Validation**: Validates against included XSD schemas (done automatically when exporting) but only for files up to 1Gb.

**Parsing:** Simple methods to retrieve category, assignment, product, variation, set and bundle information from an XML file, either as an array, or yield using generators. Maps IDs to the more useful elements and, optionally, attributes.

**Comparison**: *in-progress* Reports on different IDs, elements and attributes, regardless of how they're ordered in the files (i.e. higher-level than diff), which is useful when different systems/processes have exported the files.

## Install
Via Composer:

``` bash
$ composer require fusionspim/php-demandware-xml
```


## Usage
To run the tests run `php vendor/bin/phpunit` from the project directory. Look in `tests/fixtures` for output examples.


## Future plans

- Export all root elements sorted by their first attribute value (similar to custom attributes) to ease manual comparison/diffs.
- Convert nested elements currently implemented with string concatenation/loops to nodes (they work, but would be nice for consistency/robustness) & deal with the `setClassification()`/`catalog-id` hack ;)
