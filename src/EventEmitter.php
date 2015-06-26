<?php

namespace NetRivet\WordPress;

class EventEmitter implements EventEmitterInterface
{
    /**
     * @var array
     */
    protected $listeners = array();

    /**
     * {@inheritdoc}
     *
     * @param $event
     * @param $function_to_add
     * @param $int $priority
     */
    public function on($event, $function_to_add, $priority = 10)
    {
        if (function_exists('add_action')) {
            add_action($event, $function_to_add, $priority);
            return $this;
        }

        if (!is_callable($function_to_add)) {
            throw new \InvalidArgumentException('The provided listener was not a valid callable.');
        }

        $this->addListener($event, $function_to_add, $priority);
        krsort($this->listeners[$event]);

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @param string $name
     * @param mixed $function_to_add
     * @param int $priority
     * @return $this
     */
    public function filter($name, $function_to_add, $priority = 10)
    {
        if (function_exists('add_filter')) {
            add_filter($name, $function_to_add, $priority);
            return $this;
        }

        $this->on($name, $function_to_add, $priority);
        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @param $name
     * @param $value
     * @return $this|mixed
     */
    public function applyFilters($name, $value /** ...args */)
    {
        $args = func_get_args();
        $args = array_slice($args, 1);

        if (function_exists('apply_filters')) {
            call_user_func_array('apply_filters', $args);
            return $this;
        }

        return $this->invokeHook($name, $args, 'filter');
    }


    /**
     * {@inheritdoc}
     *
     * @param $tag
     */
    public function emit($event /** ... args */)
    {
        $args = func_get_args();
        $rest = array_slice($args, 1);

        if (function_exists('do_action')) {
            call_user_func_array('do_action', $args);
            return $this;
        }

        $this->invokeHook($event, $rest, 'action');

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @todo handle $function_to_check when using internal test listeners
     */
    public function hasEventListener($event, $function_to_check = false)
    {
        return $this->hasListener('action', $event, $function_to_check);
    }

    /**
     * {@inheritdoc}
     *
     * @todo handle $function_to_check when using internal test listeners
     */
    public function hasFilter($name, $function_to_check = false)
    {
        return $this->hasListener('filter', $name, $function_to_check);
    }

    /**
     * Check if listener exists for type, hook, and function
     *
     * Delegates to WordPress `has_action` and `has_filter` when present,
     * or falls back to internal listener queue for testing purposes
     *
     * @param  string  $type
     * @param  string  $hook
     * @param  mixed $function_to_check
     * @return boolean
     */
    protected function hasListener($type, $hook, $function_to_check = false)
    {
        $wp_has_listener = 'has_' . $type;

        if (function_exists($wp_has_listener)) {
            return call_user_func($wp_has_listener, $hook, $function_to_check);
        }

        return (bool) $this->listeners($hook);
    }

    /**
     * Return the listeners for a given hook
     *
     * @param $hook
     * @return array
     */
    protected function listeners($hook)
    {
        return isset($this->listeners[$hook]) ? $this->listeners[$hook] : array();
    }

    /**
     * Add a prioritized listener
     *
     * @param $hook
     * @param $function_to_add
     * @param $priority
     */
    protected function addListener($hook, $function_to_add, $priority)
    {
        if (!isset($this->listeners[$hook])) {
            $this->listeners[$hook] = array();
        }

        if (!isset($this->listeners[$hook][$priority])) {
            $this->listeners[$hook][$priority] = array();
        }

        $this->listeners[$hook][$priority][] = $function_to_add;
    }

    /**
     * Invoke all listeners for a given hook
     *
     * @param string $hook
     * @param array $argument
     * @param string $type
     * @return mixed
     */
    protected function invokeHook($hook, array $arguments, $type)
    {
        $listeners = $this->listeners($hook);

        $value = isset($arguments[0]) ? $arguments[0] : '';

        foreach ($listeners as $key => $set) {
            $value = $this->invokeListeners($set, $arguments, $type);
        }

        return $value;
    }

    /**
     * Invoke listeners for a given hook priority
     *
     * @param  array  $listeners
     * @param  array  $arguments
     * @param  string $type
     * @return mixed
     */
    protected function invokeListeners(array $listeners, array $arguments, $type)
    {
        $value = '';

        foreach ($listeners as $listener) {
            $value = call_user_func_array($listener, $arguments);
            if ($type === 'filter') {
                $arguments[0] = $value;
            }
        }

        return $value;
    }
}
