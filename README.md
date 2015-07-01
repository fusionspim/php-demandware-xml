PHP library for working with Demandware XML files
===

Helpers for working with Demandware XML files.

- Exporting: supports category/product/variant/assignment files, allow elements to be added in the order that makes sense for your code - they'll be exported in the sequence specified by the XSD automatically, and multiple elements in a group ordered by attribute ids for consistency between exports
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

See `test.php` for examples.