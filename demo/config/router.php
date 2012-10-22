<?php

$_config['router'] = array(
    '/' => array('controller' => 'Index', 'action' => 'default'),
    '/login' => array('controller' => 'Auth', 'action' => 'login'),
    '/admin' => array('controller' => 'Admin', 'action' => 'default'),
);
