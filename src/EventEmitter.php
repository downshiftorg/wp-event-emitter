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
     * @param $hook
     * @param $function_to_add
     * @param $int $priority
     */
    public function on($hook, $function_to_add, $priority = 10)
    {
        if (function_exists('add_action')) {
            add_action($hook, $function_to_add, $priority);
            return $this;
        }

        if (!is_callable($function_to_add)) {
            throw new \InvalidArgumentException('The provided listener was not a valid callable.');
        }

        $this->addListener($hook, $function_to_add, $priority);
        krsort($this->listeners[$hook]);

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @param $hook
     * @param $function_to_add
     * @param $priority
     * @return $this
     */
    public function filter($hook, $function_to_add, $priority = 10)
    {
        if (function_exists('add_filter')) {
            add_filter($hook, $function_to_add, $priority);
            return $this;
        }

        if (! function_exists('add_action')) {
            $this->on($hook, $function_to_add, $priority);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @param $hook
     * @param $value
     * @return $this|mixed
     */
    public function applyFilters($hook, $value /** ...args */)
    {
        $args = func_get_args();
        $args = array_slice($args, 1);

        if (function_exists('apply_filters')) {
            call_user_func_array('apply_filters', $args);
            return $this;
        }

        return $this->invokeHook($hook, $args, 'filter');
    }


    /**
     * {@inheritdoc}
     *
     * @param $tag
     */
    public function emit($tag /** ... args */)
    {
        $args = func_get_args();
        $rest = array_slice($args, 1);

        if (function_exists('do_action')) {
            call_user_func_array('do_action', $args);
            return $this;
        }

        $this->invokeHook($tag, $rest, 'action');

        return $this;
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
     */
    protected function invokeHook($hook, array $arguments, $type)
    {
        $value = '';
        $listeners = $this->listeners($hook);

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
