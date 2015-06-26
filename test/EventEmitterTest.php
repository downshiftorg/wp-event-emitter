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

    public function testEmitIsVariadicLikeWordPressDoAction()
    {
        $result = null;

        $this->emitter->on('foo', function () use (&$result) {
            $result = func_get_args();
        });

        $this->emitter->emit('foo', 'grace jones', 'andre the giant');

        $this->assertSame(array('grace jones', 'andre the giant'), $result);
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

    public function testPhpNoticesOrWarningsNotEmittedWhenApplyingFiltersToHookWithNoFilter()
    {
        $this->emitter->applyFilters('jim_jam', 'yolo neckbeard');
    }

    public function testMultipleEmits()
    {
        $test = null;

        $this->emitter->on('foo', function ($arg) use (&$test) {
            $test = $arg;
        });

        $this->emitter->on('foo', function ($arg) use (&$test) {
            $test = $arg;
        });

        $this->emitter->emit('foo', 'bar');

        $this->assertNotEmpty($test);
    }

    public function testCallingApplyFiltersWithNoFiltersAddedReturnsValue()
    {
        $toFilter = 'foobar';

        $filtered = $this->emitter->applyFilters('foo', $toFilter);

        $this->assertSame($toFilter, $filtered);
    }
}
