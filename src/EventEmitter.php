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

        return $this->invokeListeners($hook, $args);
    }


    /**
     * {@inheritdoc}
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

        $this->invokeListeners($tag, $arguments);

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
     * @param $listeners
     * @param array $argument
     */
    protected function invokeListeners($hook, array $arguments)
    {
        $value = '';
        $listeners = $this->listeners($hook);
        foreach ($listeners as $key => $set) {
            foreach ($set as $listener) {
                $value = call_user_func_array($listener, $arguments);
                $arguments[0] = $value;
            }
        }
        return $value;
    }
}
