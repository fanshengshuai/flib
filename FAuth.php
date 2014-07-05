<?php

/**
 *
 * 作者: 范圣帅(fanshengshuai@gmail.com)
 * 时间: 2012-07-10 10:32:06
 *
 * vim: set expandtab sw=4 ts=4 sts=4
 * $Id: Auth.php 11 2012-07-24 03:42:35Z fanshengshuai $
 */
class FAuth {

    public function login($username, $password) {

        $password = md5($password);

        $table = new DB_Table('members');
        $user = $table->find("username = '$username' and password='$password'");

        ob_clean();
        if ($user) {
            setcookie('auth', $user['uid'] . '_' . md5($username . "_" . $password), time() + 10000000, '/');
            return true;
        } else {
            return false;
        }
    }

    public function logout() {
        setcookie('auth', '', 0, '/');
        return true;
    }

    public function checkAccount() {

        global $_F;

        $auth_raw = $_COOKIE['auth'];

        if ($auth_raw) {
            list($uid, $auth_str) = explode('_', $auth_raw);

            $table = new DB_Table('members');
            $user = $table->find('uid=' . $uid);

            if ($user) {
                if (md5($user['username'] . '_' . $user['password']) == $auth_str) {

                    $_F['uid'] = $uid;
                    $_F['username'] = $user['username'];
                    $_F['real_name'] = $user['real_name'];

                    return true;
                }
            }
        }

        return false;
    }

    public function checkLogin() {
    }

    public function isAdmin() {
    }
}