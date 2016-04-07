<?php

/**
 *
 * 作者: 范圣帅(fanshengshuai@gmail.com)
 *
 * 创建: 2012-07-31 11:27:33
 * vim: set expandtab sw=4 ts=4 sts=4 *
 *
 * $Id: FlibHash.php 764 2015-04-14 15:09:06Z fanshengshuai $
 */
class FlibHash {

    public static function getHashPath($seed, $deep = 3) {

        $md5 = md5($seed);

        $dir = '';
        for ($i = 0; $i < $deep; $i++) {
            $dir .= $md5{$i};
            $i++;
            $dir .= $md5{$i} . '/';
        }

        return array('dir' => $dir, 'file' => $md5);
    }
}
