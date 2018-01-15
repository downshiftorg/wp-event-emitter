<?php

namespace DownShift\WordPress;

interface EventEmitterInterface
{
    /**
     * Register a function with a hook.
     *
     * @param $event
     * @param $function_to_add
     * @param int $priority
     * @param int $acceptedArgs
     */
    public function on($event, $function_to_add, $priority = 10, $acceptedArgs = 1);

    /**
     * Remove listeners from an event
     *
     * @param $event
     * @param $function_to_remove
     * @param int $priority
     */
    public function off($event, $function_to_remove, $priority = 10);

    /**
     * Register a filter with a hook. TODO support $accepted args
     *
     * @param string $name
     * @param string|array $function_to_add
     * @param string $priority
     * @param int $acceptedArgs
     * @return $this
     */
    public function filter($name, $function_to_add, $priority = 10, $acceptedArgs = 1);

    /**
     * This function invokes all functions for a hook and transforms the given value(s)
     *
     * @param $hook
     * @param $value
     * @return $this|mixed
     */
    public function applyFilters($hook, $value);

    /**
     * This function invokes all functions attached to action hook $tag
     *
     * @param string $event
     */
    public function emit($event /** ... args */);

    /**
     * Is an event listener registered for the given event
     *
     * Delegates to WordPress' `has_action` when present, or falls back
     * to internal listener queue for testing purposes.
     *
     * @param  string $event
     * @param  mixed  $function_to_check
     * @return boolean
     */
    public function hasEventListener($event, $function_to_check = false);

    /**
     * Has a filter function been registered for a given filter name
     *
     * Delegates to WordPress' `has_filter` when present, or falls back
     * to internal listener queue for testing purposes.
     *
     * @param  string $name
     * @param  mixed  $function_to_check
     * @return boolean
     */
    public function hasFilter($name, $function_to_check = false);
}
