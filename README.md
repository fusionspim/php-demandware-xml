PHP library for working with Demandware XML files
===

Helpers for working with Demandware XML files.

- Exporting: supports category/product/variant/assignment files, and allows elements to be added in the order that makes sense for your code - they'll be exported in the sequence specified by the XSD automatically, and custom attribute elements are ordered by attribute ids for consistency between exports
- Validating: against included XSD schemas (done automatically when exporting)
- Parsing: *to-do*
- Comparing: *in-progress* reports on different ids/elements/attributes, regardless of how they're ordered in the files (i.e. higher-level than diff) which is useful when different systems/processes have exported the files


Install
---

Via Composer:

``` bash
$ composer require fusionspim/php-demandware-xml
```

Usage
---

See `test.php` for usage, and `/examples` for output examples.


Future plans
---

- Export all root elements sorted by their first attribute value (similar to custom attributes) to ease manual comparison/diffs
- Test a large export to better understand how scales and memory usage
- Convert nested elements currently implemented with string concatenation/loops to nodes (they work, but would be nice for consistency/robustness) & deal with the `setClassification()`/`catalog-id` hack ;-)
