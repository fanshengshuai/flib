<?php

/**
 *
 * 作者: 范圣帅(fanshengshuai@gmail.com)
 *
 * 创建: 2019-01-04 11:19:33
 * vim: set expandtab sw=4 ts=4 sts=4 *
 *
 * $Id: FAutoTask.php 764 2015-04-14 15:09:06Z fanshengshuai $
 */

class FAutoTask
{
    /**
     * @var Factory
     */
    private static $__instance;

    protected $_taskName;

    public function getTaskName()
    {
        return $this->_taskName;
    }

    /**
     * @return FAutoTask
     */
    public static function getInstance()
    {
        if (self::$__instance === null) {
            self::$__instance = new self;
        }

        return self::$__instance;
    }
}
