<?php

class DB_Shard_Exception extends Exception {

    public function __construct($message, $code = 0) {
        if (is_a($message, 'Exception')) {
            parent::__construct($message->getMessage(), intval($message->getCode()));
        } else {
            parent::__construct($message, intval($code));
        }
    }
}
