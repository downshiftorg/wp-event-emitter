#WP EventEmitter

An object oriented event emitter for WordPress actions

## Motivation

A familiar event interface that delegates to the global `add_action` and `do_action` functions of WordPress. It also presents
a much more testable interface as it only uses the WP functions if they are available.

## Methods

Methods are the same methods provided by version 1.0 of the [Événement](https://github.com/igorw/evenement/tree/v1.0.0) library.

## Tests

Tests use PHPUnit

```
$ vendor/bin/phpunit
```

