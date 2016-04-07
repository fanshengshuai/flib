<?php

class FMisc {
    public static function getDistance($x, $y, $x2, $y2) {

        if (!$x || !$x2) {
            return -1;
        }

        $r = 6378.137; //地球半径

        //角度转为弧度
        $radLat1 = deg2rad($x);
        $radLat2 = deg2rad($x2);

        $radLng1 = deg2rad($y);
        $radLng2 = deg2rad($y2);

        //sqrt：平方根，pow(x, n)：x的n次方的幂，asin：反正弦，sin：正弦
        $s = 2 * asin(sqrt(pow(sin(($radLat1 - $radLat2) / 2), 2) + cos($radLat1) * cos($radLat2) * pow(sin(($radLng1 - $radLng2) / 2), 2))) * $r * 1000;
        return $s;
    }

    /**
     * 根据坐标，计算距离
     *
     * @param     $x
     * @param     $y
     * @param int $distance
     * @param int $distance_min
     *
     * @return array
     */
    public static function getAroundXY($x, $y, $distance = 1000, $distance_min = 100) {

        // lng X坐标 1000米范围系数 ±0.010520
        // lat Y坐标 1000米范围系数 ±0.009000

        $_x = round(0.000010520 * $distance, 6);
        $_y = round(0.000009000 * $distance, 6);

        $_x_min = round(0.000010520 * $distance_min, 6);
        $_y_min = round(0.000009000 * $distance_min, 6);

        // 角度转为弧度
        return array(
            array(round($x - $_x, 6), round($x + $_x, 6)), array(round($y - $_y, 6), round($y + $_y, 6)),
            array(round($x - $_x_min, 6), round($x + $_x_min, 6)), array(round($y - $_y_min, 6), round($y + $_y_min, 6))
        );
    }


    private static function getDecimalVal($ch) {
        if (is_numeric($ch)) {
            return intval(ord($ch) - ord('0'));
        } else {
            return intval(ord($ch) - ord('a') + 10);
        }
    }

    public static function convertMd5ToInt64($strMd5Val) {
        $intStrLen = strlen($strMd5Val);
        $arrMd5Val = array();
        for ($i = 0; $i < $intStrLen; ++$i) {
            $arrMd5Val[$i] = substr($strMd5Val, $i, 1);
        }
        $intStrHalfLen = $intStrLen / 2;
        $arrRes = array();
        $arrRes[0] = intval(0);
        $arrRes[1] = intval(0);
        for ($i = 0; $i < $intStrHalfLen; ++$i) {
            $arrRes[0] = intval((($arrRes[0] << 4) | self::getDecimalVal($arrMd5Val[$i])));
            $arrRes[1] = intval((($arrRes[1] << 4) | self::getDecimalVal($arrMd5Val[$intStrHalfLen + $i])));
        }
        return $arrRes;
    }

    public static function str2int($str) {
        $intStrLen = strlen($str);
        $var = 0;

        for ($i = 0; $i < $intStrLen; ++$i) {
            $var += ord($str[$i]);
        }

        return $var;
    }
}