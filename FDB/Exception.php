<?php

class FDB_Exception extends PDOException {

    public function __construct($message, $code = 0) {
        if (is_a($message, 'Exception')) {
            parent::__construct($message, intval($message->getCode()));
        } else {
            parent::__construct($message, intval($code));
        }
    }

}
