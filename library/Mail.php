<?php

/**
 *
 * 作者: 范圣帅(fanshengshuai@gmail.com)
 * 时间: 2012-07-02 01:22:49
 *
 * vim: set expandtab sw=4 ts=4 sts=4
 * $Id: Mail.php 11 2012-07-24 03:42:35Z fanshengshuai $
 */

class EMail {

    var $headers;
    var $body;
    var $multipart;
    var $mime;
    var $html;
    var $html_text;
    var $html_images = array();
    var $cids = array();
    var $do_html;
    var $parts = array();

    /***************************************
     ** Constructor function. Sets the headers
     ** if supplied.
     ***************************************/
    function EMail($headers = ''){
        $this->headers = $headers;
    }


    /***************************************
     ** Adds a html part to the mail.
     ** Also replaces image names with
     ** content-id's.
     ***************************************/
    function add_html($html, $text){
        $this->do_html = 1;
        $this->html = $html;
        $this->html_text = $text;
        if(is_array($this->html_images) AND count($this->html_images) > 0){
            for($i=0; $i<count($this->html_images); $i++){
                $this->html = ereg_replace($this->html_images[$i]['name'], 'cid:'.$this->html_images[$i]['cid'], $this->html);
            }
        }
    }

    /***************************************
     ** Builds html part of email.
     ***************************************/
    function build_html($orig_boundary){
        $sec_boundary = '=_'.md5(uniqid(time()));
        $thr_boundary = '=_'.md5(uniqid(time()));

        if(!is_array($this->html_images)){
            $this->multipart.= '--'.$orig_boundary."\r\n";
            $this->multipart.= 'Content-Type: multipart/alternative; boundary="'.$sec_boundary."\"\r\n\r\n\r\n";

            $this->multipart.= '--'.$sec_boundary."\r\n";
            $this->multipart.= 'Content-Type: text/plain'."\r\n";
            $this->multipart.= 'Content-Transfer-Encoding: 7bit'."\r\n\r\n";
            $this->multipart.= $this->html_text."\r\n\r\n";

            $this->multipart.= '--'.$sec_boundary."\r\n";
            $this->multipart.= 'Content-Type: text/html'."\r\n";
            $this->multipart.= 'Content-Transfer-Encoding: 7bit'."\r\n\r\n";
            $this->multipart.= $this->html."\r\n\r\n";
            $this->multipart.= '--'.$sec_boundary."--\r\n\r\n";
        }else{
            $this->multipart.= '--'.$orig_boundary."\r\n";
            $this->multipart.= 'Content-Type: multipart/related; boundary="'.$sec_boundary."\"\r\n\r\n\r\n";

            $this->multipart.= '--'.$sec_boundary."\r\n";
            $this->multipart.= 'Content-Type: multipart/alternative; boundary="'.$thr_boundary."\"\r\n\r\n\r\n";

            $this->multipart.= '--'.$thr_boundary."\r\n";
            $this->multipart.= 'Content-Type: text/plain'."\r\n";
            $this->multipart.= 'Content-Transfer-Encoding: 7bit'."\r\n\r\n";
            $this->multipart.= $this->html_text."\r\n\r\n";

            $this->multipart.= '--'.$thr_boundary."\r\n";
            $this->multipart.= 'Content-Type: text/html'."\r\n";
            $this->multipart.= 'Content-Transfer-Encoding: 7bit'."\r\n\r\n";
            $this->multipart.= $this->html."\r\n\r\n";
            $this->multipart.= '--'.$thr_boundary."--\r\n\r\n";

            for($i=0; $i<count($this->html_images); $i++){
                $this->multipart.= '--'.$sec_boundary."\r\n";
                $this->build_html_image($i);
            }

            $this->multipart.= "--".$sec_boundary."--\r\n\r\n";
        }
    }
    /***************************************
     ** Adds an image to the list of embedded
     ** images.
     ***************************************/
    function add_html_image($file, $name = '', $c_type='application/octet-stream'){
        $this->html_images[] = array( 'body' => $file,
                                      'name' => $name,
                                      'c_type' => $c_type,
                                      'cid' => md5(uniqid(time())) );
    }


    /***************************************
     ** Adds a file to the list of attachments.
     ***************************************/
    function add_attachment($file, $name = '', $c_type='application/octet-stream'){
        $this->parts[] = array( 'body' => $file,
                                'name' => $name,
                                'c_type' => $c_type );
    }

    /***************************************
     ** Builds an embedded image part of an
     ** html mail.
     ***************************************/
    function build_html_image($i){
        $this->multipart.= 'Content-Type: '.$this->html_images[$i]['c_type'];

        if($this->html_images[$i]['name'] != '') $this->multipart .= '; name="'.$this->html_images[$i]['name']."\"\r\n";
        else $this->multipart .= "\r\n";

        $this->multipart.= 'Content-ID: <'.$this->html_images[$i]['cid'].">\r\n";
        $this->multipart.= 'Content-Transfer-Encoding: base64'."\r\n\r\n";
        $this->multipart.= chunk_split(base64_encode($this->html_images[$i]['body']))."\r\n";
    }

    /***************************************
     ** Builds a single part of a multipart
     ** message.
     ***************************************/
    function build_part($i){
        $message_part = '';
        $message_part.= 'Content-Type: '.$this->parts[$i]['c_type'];
        if($this->parts[$i]['name'] != '')
            $message_part .= '; name="'.$this->parts[$i]['name']."\"\r\n";
        else
            $message_part .= "\r\n";

        // Determine content encoding.
        if($this->parts[$i]['c_type'] == 'text/plain'){
            $message_part.= 'Content-Transfer-Encoding: 7bit'."\r\n\r\n";
            $message_part.= $this->parts[$i]['body']."\r\n";
        }else{
            $message_part.= 'Content-Transfer-Encoding: base64'."\r\n";
            $message_part.= 'Content-Disposition: attachment; filename="'.$this->parts[$i]['name']."\"\r\n\r\n";
            $message_part.= chunk_split(base64_encode($this->parts[$i]['body']))."\r\n";
        }

        return $message_part;
    }

    /***************************************
     ** Builds the multipart message from the
     ** list ($this->parts).
     ***************************************/
    function build_message(){
        $boundary = '=_'.md5(uniqid(time()));

        $this->headers.= "MIME-Version: 1.0\r\n";
        $this->headers.= "Content-Type: multipart/mixed; boundary=\"".$boundary."\"\r\n";
        $this->multipart = '';
        $this->multipart.= "This is a MIME encoded message.\r\nCreated by html_mime_mail.class.\r\nSee http://www.heyes-computing.net/&#115;cripts/ for a copy.\r\n\r\n";

        if(isset($this->do_html) AND $this->do_html == 1) $this->build_html($boundary);
        if(isset($this->body) AND $this->body != '') $this->parts[] = array('body' => $this->body, 'name' => '', 'c_type' => 'text/plain');

        for($i=(count($this->parts)-1); $i>=0; $i--){
            $this->multipart.= '--'.$boundary."\r\n".$this->build_part($i);
        }

        $this->mime = $this->multipart."--".$boundary."--\r\n";
    }

    /***************************************
     ** Sends the mail.
     ***************************************/
    function send($to_name, $to_addr, $from_name, $from_addr, $subject = '', $headers = ''){

        if($to_name != '') $to = '"'.$to_name.'" <'.$to_addr.'>';
        else $to = $to_addr;

        if($from_name != '') {
            $from = '"'.$from_name.'" <'.$from_addr.'>';
        } else {
            $from = $from_addr;
        }

        $this->headers.= 'From: '.$from."\r\n";
        //$this->headers.= $headers;
        mail($to, $subject, $this->mime, $this->headers);
    }

    /***************************************
     ** Use this method to deliver using direct
     ** smtp connection. Relies upon Manuel Lemos'
     ** smtp mail delivery class available at:
     ** http://phpclasses.upperdesign.com
     **
     ** void smtp_send( string *Name* of smtp object,
     ** string From address,
     ** array To addresses,
     ** array Headers,
     ** string The body)
     ***************************************/
    function smtp_send($smtp_obj, $from_addr, $to_addr){
        global $$smtp_obj;
        $smtp_obj = $$smtp_obj;

        if(substr($this->headers, -2) == "\r\n") $this->headers = substr($this->headers,0,-2);
        $this->headers = explode("\r\n", $this->headers);

        $smtp_obj->sendmessage($from_addr, $to_addr, $this->headers, $this->mime);
    }

}
}
