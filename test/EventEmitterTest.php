<?php

namespace DownShift\WordPress;

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

    public function testHasEventListenerReturnsFalseWhenNoListenerAdded()
    {
        $hasListener = $this->emitter->hasEventListener('foo');

        $this->assertFalse($hasListener);
    }

    public function testHasEventListenerReturnsTrueWhenListenerAdded()
    {
        $this->emitter->on('foo', 'phpinfo');

        $hasListener = $this->emitter->hasEventListener('foo');

        $this->assertTrue($hasListener);
    }

    public function testHasFilterReturnsFalseWhenNoFilterAdded()
    {
        $hasFilter = $this->emitter->hasFilter('foo');

        $this->assertFalse($hasFilter);
    }

    public function testHasFilterReturnsTrueWhenFilterAdded()
    {
        $this->emitter->filter('foo', 'strtoupper');

        $hasFilter = $this->emitter->hasFilter('foo');

        $this->assertTrue($hasFilter);
    }

    public function testHasEventListenerReturnsFalseWhenStringFunctionToCheckDoesNotMatch()
    {
        $this->emitter->on('foo', 'phpinfo');

        $hasListener = $this->emitter->hasEventListener('foo', 'some_other_func');

        $this->assertFalse($hasListener);
    }

    public function testHasEventListenerReturnsTrueWhenStringFunctionToCheckMatches()
    {
        $this->emitter->on('foo', 'phpinfo');

        $hasListener = $this->emitter->hasEventListener('foo', 'phpinfo');

        $this->assertTrue($hasListener);
    }

    public function testHasEventListenerReturnsTrueWhenClosuresSame()
    {
        $saySomething = function () {
            echo 'something';
        };

        $this->emitter->on('foo', $saySomething);

        $hasListener = $this->emitter->hasEventListener('foo', $saySomething);

        $this->assertTrue($hasListener);
    }

    public function testHasEventListenerReturnsFalseWhenComparingDifferentClosures()
    {
        $this->emitter->on('foo', function () {
            echo 'foo';
        });

        $hasListener = $this->emitter->hasEventListener('foo', function () {
            echo 'foo';
        });

        $this->assertFalse($hasListener);
    }

    public function testHasEventListenerReturnsTrueWhenPassedSameArrayCallable()
    {
        $this->emitter->on('foo', array($this, 'listener1'));

        $hasListener = $this->emitter->hasEventListener('foo', array($this, 'listener1'));

        $this->assertTrue($hasListener);
    }

    public function testHasEventListenerReturnsFalseWhenPassedDifferentArrayCallable()
    {
        $this->emitter->on('foo', array($this, 'listener1'));

        $hasListener = $this->emitter->hasEventListener('foo', array($this, 'listener2'));

        $this->assertFalse($hasListener);
    }

    /**
     * @runInSeparateProcess
     */
    public function testWhenWordpressApplyFiltersExistsReturnsResultOfCallingThatFunction()
    {
        eval('function apply_filters() { return "foobar"; }');

        $filtered = $this->emitter->applyFilters('some_filter', 'jimjam');

        $this->assertSame('foobar', $filtered);
    }

    /**
     * @runInSeparateProcess
     */
    public function testCorrectArgsPassedToWordpressFunctionWhenPresent()
    {
        eval('function apply_filters() { return func_get_args(); }');

        $filtered = $this->emitter->applyFilters('foo', 'bar');

        $this->assertSame(array('foo', 'bar'), $filtered);
    }

    public function testOffRemovesAllListeners()
    {
        $function = function() {};
        $this->emitter->on('foo', $function);

        $this->emitter->off('foo', $function);

        $this->assertFalse($this->emitter->hasEventListener('foo', $function));
    }

    public function listener1()
    {
        // do a thing
    }

    public function listener2()
    {
        // do another thing
    }
}
