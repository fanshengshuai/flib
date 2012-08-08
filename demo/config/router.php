<?php

$_config['router'] = array(
    '/' => array('controller' => 'Index', 'action' => 'default'),
    '/about' => array('controller' => 'About', 'action' => 'index'),
    '/about/news' => array('controller' => 'About', 'action' => 'news'),
    '/about/partner' => array('controller' => 'About', 'action' => 'partner'),
    '/about/join-us' => array('controller' => 'About', 'action' => 'join'),
    '/about/relation' => array('controller' => 'About', 'action' => 'relation'),
    '/teacher' => array('controller' => 'Teacher', 'action' => 'index'),
    '/course' => array('controller' => 'Course', 'action' => 'index'),
    '/environment' => array('controller' => 'Environment', 'action' => 'index'),
    // 学生作品
    '/student-works' => array('controller' => 'Student', 'action' => 'works'),
    // app外包
    '/app-develop' => array('controller' => 'App', 'action' => 'develop'),
    '/admin' => array('controller' => 'Admin', 'action' => 'default'),
);
