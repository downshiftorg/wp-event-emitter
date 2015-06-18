<?php

namespace NetRivet\WordPress;

class EventEmitterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var EventEmitter
     */
    protected $emitter;

    public function setUp()
    {
        $this->emitter = new EventEmitter();
    }

    public function testEventPriority()
    {
        $result = null;

        $this->emitter->on('init', function () use (&$result) {
            $result = 'early';
        });

        $this->emitter->on('init', function () use (&$result) {
            $result = 'late';
        }, 11);

        $this->emitter->emit('init');

        $this->assertEquals('early', $result);
    }

    public function testFilters()
    {
        $this->emitter->filter('the_content', function ($content, $append) {
            return $content . ' ' . $append;
        });

        $this->emitter->filter('the_content', function ($content) {
            return $content . ' yolo';
        });

        $content = $this->emitter->applyFilters('the_content', 'ham', 'sandwich');

        $this->assertEquals('ham sandwich yolo', $content);
    }
}
