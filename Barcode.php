<?php

/**
 *
 * 作者: 范圣帅(fanshengshuai@gmail.com)
 * 时间: 2012-07-02 01:19:41
 *
 * vim: set expandtab sw=4 ts=4 sts=4
 * $Id: Barcode.php 11 2012-07-24 03:42:35Z fanshengshuai $
 */

//$code = $_GET['code'];

//$barcode = new Barcode;
//$barcode->ean13($code);

class Barcode {

    public function __construct() {
        //error_reporting(0);

        header ("Content-type: image/png");
        // Date in the past
        header ("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
        // always modified
        header ("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
        // HTTP/1.1
        header ("Cache-Control: no-cache, must-revalidate");
        // HTTP/1.0
        header ("Pragma: no-cache");
    }

    public function ean13($code = '000000000000', $w = 210, $h = 60) {

        // Check validity of $code
        if (strlen($code) != 12) {

            $im = @ImageCreate ($w, $h) or die ("Cannot Initialize new GD image stream");
            $bg = ImageColorAllocate ($im, 255, 255, 255);
            $fg = ImageColorAllocate ($im, 0, 0, 0);
            ImageString ($im, 5, 3, 10, "Code $code is not valid", $fg);
            ImageString ($im, 5, 3, 30, "12 digits?", $fg);
            ImagePng ($im);
            break;
        }


        for ($i = 1; $i <= 12; $i++) {
            if ((substr($code, $i-1, 1) <= 0) && ((substr($code, $i-1, 1) >= 9))) {

                $im = @ImageCreate ($w, $h) or die ("Cannot Initialize new GD image stream");
                $bg = ImageColorAllocate ($im, 255, 255, 255);
                $fg = ImageColorAllocate ($im, 0, 0, 0);
                ImageString ($im, 5, 3, 10, "Code $code is not valid", $fg);
                ImageString ($im, 5, 3, 30, "only digits!", $fg);
                ImagePng ($im);
                break;
            }
        }


        // Define bitcode for Numbers
        $left[0]['O'] = "0001101";
        $left[0]['E'] = "0100111";
        $left[1]['O'] = "0011001";
        $left[1]['E'] = "0110011";
        $left[2]['O'] = "0010011";
        $left[2]['E'] = "0011011";
        $left[3]['O'] = "0111101";
        $left[3]['E'] = "0100001";
        $left[4]['O'] = "0100011";
        $left[4]['E'] = "0011101";
        $left[5]['O'] = "0110001";
        $left[5]['E'] = "0111001";
        $left[6]['O'] = "0101111";
        $left[6]['E'] = "0000101";
        $left[7]['O'] = "0111011";
        $left[7]['E'] = "0010001";
        $left[8]['O'] = "0110111";
        $left[8]['E'] = "0001001";
        $left[9]['O'] = "0001011";
        $left[9]['E'] = "0010111";
        $right[0] = "1110010";
        $right[1] = "1100110";
        $right[2] = "1101100";
        $right[3] = "1000010";
        $right[4] = "1011100";
        $right[5] = "1001110";
        $right[6] = "1010000";
        $right[7] = "1000100";
        $right[8] = "1001000";
        $right[9] = "1110100";


        // Calculate Checksum
        $oddeven = 1;
        $extsum = 0;

        for ($i = 1; $i <= 12; $i++) {
            $num = substr($code, $i-1, 1);
            if ($oddeven == 1)
            {
                $intsum = $num * $oddeven;
                $extsum = $extsum + $intsum;
                $oddeven = 3;
            }
            else
            {
                $intsum = $num * $oddeven;
                $extsum = $extsum + $intsum;
                $oddeven = 1;
            }
        }

        $check = (floor($extsum/10)*10+10) - $extsum;

        if ($check == 10) {
            $check = 0;
        }
        $code = $code . $check;

        // Build Array from $code string
        for ($i = 1; $i <= 13; $i++) {

            $c[$i] = substr($code, $i-1, 1);
        }

        // Set parity
        if ($c[1] == 0) {
            $parity = "OOOOO";
        } else if ($c[1] == 1) {
            $parity = "OEOEE";
        } else if ($c[1] == 2) {
            $parity = "OEEOE";
        } else if ($c[1] == 3) {
            $parity = "OEEEO";
        } else if ($c[1] == 4) {
            $parity = "EOOEE";
        } else if ($c[1] == 5) {
            $parity = "EEOOEE";
        } else if ($c[1] == 6) {
            $parity = "EEEOO";
        } else if ($c[1] == 7) {
            $parity = "EOEOE";
        } else if ($c[1] == 8) {
            $parity = "EOEEO";
        } else if ($c[1] == 9) {
            $parity = "EEOEO";
        }

        // Start generating bitcode for barcode
        //// Startguard
        $barbit = "101";

        // 2nd char is always odd
        $barbit = $barbit . $left[$c[2]]['O'];

        // generate first 5 digits with parity in bitcode
        for ($i = 3; $i <= 7; $i++) {

            $par = substr($parity, $i - 3, 1);
            $barbit = $barbit . $left[$c[$i]][$par];
        }

        // Middleguard
        $barbit = $barbit . "01010";

        // generate bitcode for 5 digits and 1 checksum
        for ($i = 8; $i <= 13; $i++) {
            $barbit = $barbit . $right[$c[$i]];
        }

        // Endguard
        $barbit = $barbit . "101";

        // Create Image
        $im = @ImageCreate ($w, $h) or die ("Cannot Initialize new GD image stream");
        $bg = ImageColorAllocate ($im, 255, 255, 255);
        $fg = ImageColorAllocate ($im, 0, 0, 0);

        $start = 14;
        for ($i = 1; $i <= 95; $i++) {

            $end = $start + 2;
            $bit = substr($barbit, $i-1, 1);
            if ($bit == 0) {
                Imagefilledrectangle ($im, $start, 0, $end, $h, $bg);
            } else {
                Imagefilledrectangle ($im, $start, 0, $end, $h, $fg);
            }

            $start = $end;
        }

        Imagefilledrectangle ($im, 0, ($h - 10), $w, $h, $bg);
        Imagefilledrectangle ($im, 20, ($h - 20), 104, $h, $bg);
        Imagefilledrectangle ($im, 112, ($h - 20), 195, $h, $bg);
        //Imagefilledrectangle ($im, 0, ($h - 8), 304, $h, $bg);

        ImageString ($im, 5, 3, ($h - 18), $c[1], $fg);
        ImageString ($im, 5, 40, ($h - 18), "$c[2]$c[3]$c[4]$c[5]$c[6]$c[7]", $fg);
        ImageString ($im, 5, 130, ($h - 18), "$c[8]$c[9]$c[10]$c[11]$c[12]$c[13]", $fg);
        ImagePng ($im);
    }
}