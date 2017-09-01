# WP EventEmitter

An object oriented event emitter for WordPress actions

## Motivation

A familiar event interface that delegates to the global `add_action` and `do_action` functions of WordPress. It also presents
a much more testable interface as it only uses the WP functions if they are available.

## Methods

### on

Delegate to WordPress' [add_action](https://codex.wordpress.org/Function_Reference/add_action) function. In test environments a local
collection of listeners will be used.

### emit

Delegate to WordPress' [do_action](https://codex.wordpress.org/Function_Reference/do_action) function. In test environments a local
collection of listeners will be used.

### filter

Delegate to WordPress' [add_filter](https://codex.wordpress.org/Function_Reference/add_filter) function. In test environments a local
collection of listeners will be used.

### applyFilters

Delegate to WordPress' [apply_filters](https://codex.wordpress.org/Function_Reference/apply_filters) function. In test environments a
local collection of listeners will be used.

## Tests

Tests use PHPUnit

```
$ vendor/bin/phpunit
```

