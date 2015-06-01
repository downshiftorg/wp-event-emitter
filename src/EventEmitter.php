<?php

namespace NetRivet\WordPress;

use Evenement\EventEmitter as CoreEmitter;

class EventEmitter extends CoreEmitter
{

    /**
     * Register a function with a hook. TODO support $accepted_args
     *
     * @param $hook
     * @param $function_to_add
     * @param $int $priority
     */
    public function on($hook, $function_to_add, $priority = 10)
    {
        if (function_exists('add_action')) {
            \add_action($hook, $function_to_add, $priority);
            return $this;
        }

        if (!is_callable($function_to_add)) {
            throw new \InvalidArgumentException('The provided listener was not a valid callable.');
        }

        if (!isset($this->listeners[$hook])) {
            $this->listeners[$hook] = array();
        }

        $this->listeners[$hook][$priority] = $function_to_add;
        krsort($this->listeners[$hook]);

        return $this;
    }

    /**
     * This function invokes all functions attached to action hook $tag
     *
     * @param $tag
     */
    public function emit($tag, array $arguments = array())
    {
        $args = func_get_args();
        $args = array_slice($args, 1);

        if (function_exists('do_action')) {
            call_user_func_array('do_action', $args);
            return $this;
        }

        parent::emit($tag, $args);

        return $this;
    }
}
