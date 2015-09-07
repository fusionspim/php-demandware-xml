PHP library for working with Demandware XML files
===

Helpers for working with Demandware XML files.

- Exporting: supports category/product/variant/assignment files, and allows elements to be added in the order that makes sense for your code - they'll be exported in the sequence specified by the XSD automatically, and custom attribute elements are ordered by attribute ids for consistency between exports
- Validating: against included XSD schemas (done automatically when exporting) but only for files up to 1Gb
- Parsing: simple methods to retrieve category/assignment/product/variation/set/bundle arrays from an XML file, mapping ids to the more useful elements and, optionally, attributes
- Comparing: *in-progress* reports on different ids/elements/attributes, regardless of how they're ordered in the files (i.e. higher-level than diff) which is useful when different systems/processes have exported the files


Install
---

Via Composer:

``` bash
$ composer require fusionspim/php-demandware-xml
```


Usage
---

To run the tests run `php vendor/bin/phpunit` from the project directory. Look in `tests/fixtures` for output examples.


Benchmarks
---

Very crude figures, but a 2015 Macbook takes approximately:

- 40s to parse 105Mb categories file


Future plans
---

- Export all root elements sorted by their first attribute value (similar to custom attributes) to ease manual comparison/diffs
- Test a large export to better understand how scales and memory usage
- Convert nested elements currently implemented with string concatenation/loops to nodes (they work, but would be nice for consistency/robustness) & deal with the `setClassification()`/`catalog-id` hack ;-)
