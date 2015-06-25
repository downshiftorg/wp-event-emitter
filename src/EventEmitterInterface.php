<?php

namespace NetRivet\WordPress;

interface EventEmitterInterface
{
    /**
     * Register a function with a hook. TODO support $accepted_args
     *
     * @param $hook
     * @param $function_to_add
     * @param $int $priority
     */
    public function on($hook, $function_to_add, $priority = 10);

    /**
     * Register a filter with a hook. TODO support $accepted args
     *
     * @param $hook
     * @param $function_to_add
     * @param $priority
     * @return $this
     */
    public function filter($hook, $function_to_add, $priority = 10);

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
     * @param $tag
     */
    public function emit($tag /** ... args */);
}
