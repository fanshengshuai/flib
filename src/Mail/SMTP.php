<?php

class Mail_SMTP {
    var $mail = '';

    public function __construct() {
        require_once('PHPMailer/class.phpmailer.php');
        $this->mail = new PHPMailer();
        $this->setConfig();
    }

    public function setConfig($config = array()) {

        $mail = $this->mail;


        $mail->IsSMTP();
        //$mail->SMTPDebug  = 2;                     // enables SMTP debug information (for testing)
        $mail->SMTPAuth = true; // enable SMTP authentication
        $mail->Host = "smtp.qq.com"; // sets the SMTP server
        $mail->Port = 25; // set the SMTP port for the GMAIL server
        $mail->Username = ""; // SMTP account username
        $mail->Password = ""; // SMTP account password

        $mail->SetFrom($mail->Username, 'OA系统');
    }

    public function send($recs, $subject, $contents, $alt_contents) {
        $mail = $this->mail;

        $mail->Subject = $subject;
        $mail->AltBody = $alt_contents;
        $mail->MsgHTML($contents);

        foreach ($recs as $item) {
            $mail->AddAddress($item['email'], $item['name']);
        }

        //$mail->AddAttachment("images/phpmailer_mini.gif"); // attachment

        if (!$mail->Send()) {
            return false;
            //echo "Mailer Error: " . $mail->ErrorInfo;
        } else {
            return true;
        }
    }
}
