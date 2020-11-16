<?php

/**
 *
 * 作者: 范圣帅(fanshengshuai@gmail.com)
 *
 * 创建: 2019-01-04 11:19:33
 * vim: set expandtab sw=4 ts=4 sts=4 *
 *
 * $Id: FTaskTracker.php 764 2015-04-14 15:09:06Z fanshengshuai $
 */

class FTaskTracker
{
    /**
     * @var FTaskTracker
     */
    private static $__instance;

    protected static $_tasks = array();

    public static function registerTask($task)
    {
        array_push(self::$_tasks, $task);
    }

    public static function start()
    {
        print("AutoTask starting ...\n");

        while (true):
            // self::listJobs();

            foreach (self::$_tasks as $key => $task) {
                print($task->getTaskName . "\n");
                $task->run();
            }
            ob_get_flush();
            sleep(10);
        endwhile;
    }

    /**
     * @return FTaskTracker
     */
    public static function getInstance()
    {
        if (self::$__instance === null) {
            self::$__instance = new self;
        }

        return self::$__instance;
    }
}
