<?php

/**
 *
 * 作者: 范圣帅(fanshengshuai@gmail.com)
 *
 * 创建: 2019-01-04 11:19:33
 * vim: set expandtab sw=4 ts=4 sts=4 *
 *
 */

class FEvent
{
    protected static $_listens = array();

    /**
     * return array
     */
    public static function getEvents()
    {
        return self::$_listens;
    }

    public static function listen($eventName, $callback, $once = false)
    {
        // if (!is_callable($callback)) {
        //     throw new Exception("callback is not callable!");
        // }

        if (class_exists($callback)) {
            $callback = new $callback;
        }

        self::$_listens[$eventName][] = array('callback' => $callback, 'once' => $once);
        return true;
    }

    public static function one($event, $callback)
    {
        return self::listen($event, $callback, true);
    }

    public static function remove($event, $index = null)
    {
        if (is_null($index)) {
            unset(self::$_listens[$event]);
        } else {
            unset(self::$_listens[$event][$index]);
        }

    }

    public static function trigger()
    {
        if (!func_num_args()) {
            return;
        }

        $args  = func_get_args();
        $event = array_shift($args);
        if (!isset(self::$_listens[$event])) {
            return false;
        }

        foreach ((array)self::$_listens[$event] as $index => $listen) {
            $callback = $listen['callback'];
            $listen['once'] && self::remove($event, $index);
            call_user_func_array($callback, $args);
        }
    }
}

/*
// 增加监听walk事件
FEvent::listen('walk', function(){
echo "I am walking...n";
});
// 增加监听walk一次性事件
FEvent::listen('walk', function(){
echo "I am listening...n";
}, true);
// 触发walk事件
FEvent::trigger('walk');
I am walking...
I am listening...
FEvent::trigger('walk');
I am walking...

FEvent::one('say', function ($name = '') {
echo "I am {$name}n";
});

FEvent::trigger('say', 'deeka'); // 输出 I am deeka
FEvent::trigger('say', 'deeka'); // not run

class Foo
{
public function bar()
{
echo "Foo::bar() is calledn";
}

public function test()
{
echo "Foo::foo() is called, agrs:" . json_encode(func_get_args()) . "n";
}
}

$foo = new Foo;

FEvent::listen('bar', array($foo, 'bar'));
FEvent::trigger('bar');

FEvent::listen('test', array($foo, 'test'));
FEvent::trigger('test', 1, 2, 3);

class Bar
{
public static function foo()
{
echo "Bar::foo() is calledn";
}
}

FEvent::listen('bar1', array('Bar', 'foo'));
FEvent::trigger('bar1');

FEvent::listen('bar2', 'Bar::foo');
FEvent::trigger('bar2');

function bar()
{
echo "bar() is calledn";
}

FEvent::listen('bar3', 'bar');
FEvent::trigger('bar3');
 */
