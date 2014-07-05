<?php

/**
 *
 *      作者: 范圣帅(fanshengshuai@gmail.com)
 *  创建时间: 2012-02-21 15:19:43
 *
 *  vim: set expandtab sw=4 ts=4 sts=4
 *
 *  $Id: EMail.php 11 2012-07-24 03:42:35Z fanshengshuai $
 */
class EMail {

    public $user_smtp = true;

    public function send($recs, $title, $contents, $from = array()) {
        if ($this->user_smtp) {
            $mail = new Mail_SMTP;
            $mail->send($recs, $title, $contents);
        } else {
            $this->_send($recs, $title, $contents);
        }
    }

    public function _send($recs, $title, $contents, $from = array('name' => '游本 OA', 'mail' => 'ferris@upnb.com')) {

        $recs = explode(',', $recs);

        //var_dump($recs);exit;

        $mailtos = array();

        foreach ($recs as $item) {
            if (Strpos($item, '@') === false) {
                continue;
            }

            if (strpos($item, '<')) {
                $name = trim(substr($item, 0, strpos($item, '<')));
                $name = base64_encode($name);

                $email = substr($item, strpos($item, '<') + 1);
                $email = trim(str_replace('>', '', $email));

                $mailtos[] .= "=?UTF-8?B?{$name}?= <{$email}>";
            } else {

                $name = trim(substr($item, 0, strpos($item, '@')));
                $email = trim($item);

                $mailtos[] .= "{$name} <{$email}>";
            }
        }

        $mailtos = join(',', $mailtos);

        $title = "=?UTF-8?B?" . base64_encode($title) . "?="; //防止标题变乱码

        $headers = "From: {$from['name']} <{$from['mail']}> \n";
        $headers .= "X-Sender: \n";
        $headers .= "X-Mailer: PHP\n";
        $headers .= "X-Priority: 1\n";
        $headers .= "Return-Path: \n";
        $headers .= "Content-Type: text/html; charset=utf-8\r\n";

        mail($mailtos, $title, $contents, $headers);
    }
}
