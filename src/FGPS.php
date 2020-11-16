<?php
define('EARTH_RADIUS', 6378.137);//地球半径
define('PI', 3.1415926);

class FGPS
{
  /**
   * @var $this
   */
  private static $__instance;

  /**
   * @return $this
   */
  public static function getInstance()
  {
    if (self::$__instance === null) {
      self::$__instance = new self;
    }

    return self::$__instance;
  }

  /**
   * GPS 转 百度
   *
   */
  public static function gps_2_baidu($lng, $lat)
  {
    $fGps = self::getInstance();

    return $fGps->_wgs_2_baidu($lat, $lng);
  }

  /**
   * gps坐标转百度坐标
   */
  public function _wgs_2_baidu($lat, $lon)
  {
    $wgs2gcj = $this->wgstogcj($lat, $lon);
    $gcj2bd = $this->gcjtobd($wgs2gcj[0], $wgs2gcj[1]);
    return $gcj2bd;
  }

  //wgs2gcj
  public function wgstogcj($lat, $lon)
  {
    $pi = 3.14159265358979324;
    $a = 6378245.0;
    $ee = 0.00669342162296594323;

    $dLat = $this->transformLat($lon - 105.0, $lat - 35.0);
    $dLon = $this->transformLon($lon - 105.0, $lat - 35.0);
    $radLat = $lat / 180.0 * $pi;
    $magic = sin($radLat);
    $magic = 1 - $ee * $magic * $magic;
    $sqrtMagic = sqrt($magic);
    $dLat = ($dLat * 180.0) / (($a * (1 - $ee)) / ($magic * $sqrtMagic) * $pi);
    $dLon = ($dLon * 180.0) / ($a / $sqrtMagic * cos($radLat) * $pi);
    $mgLat = $lat + $dLat;
    $mgLon = $lon + $dLon;
    $loc[] = $mgLat;
    $loc[] = $mgLon;
    return $loc;
  }

  //bd2gcj
  public function bdtogcj($lat, $lon)
  {
    $x_pi = 3.14159265358979324 * 3000.0 / 180.0;
    $x = $lon - 0.0065;
    $y = $lat - 0.006;
    $z = sqrt($x * $x + $y * $y) - 0.00002 * sin($y * $x_pi);
    $theta = atan2($y, $x) - 0.000003 * cos($x * $x_pi);
    $gg_lon = $z * cos($theta);
    $gg_lat = $z * sin($theta);
    $gcj[] = $gg_lat;
    $gcj[] = $gg_lon;
    return $gcj;
  }

  //gjc2bd
  public function gcjtobd($lat, $lon)
  {
    $x_pi = 3.14159265358979324 * 3000.0 / 180.0;
    $x = $lon;
    $y = $lat;
    $z = sqrt($x * $x + $y * $y) + 0.00002 * sin($y * $x_pi);
    $theta = atan2($y, $x) + 0.000003 * cos($x * $x_pi);
    $bd_lon = $z * cos($theta) + 0.0065;
    $bd_lat = $z * sin($theta) + 0.006;

    $baidu[] = $bd_lon;
    $baidu[] = $bd_lat;

    return $baidu;
  }

  //转换lat
  public function transformLat($lat, $lon)
  {
    $pi = 3.14159265358979324;
    $ret = -100.0 + 2.0 * $lat + 3.0 * $lon + 0.2 * $lon * $lon + 0.1 * $lat * $lon + 0.2 * sqrt(abs($lat));
    $ret += (20.0 * sin(6.0 * $lat * $pi) + 20.0 * sin(2.0 * $lat * $pi)) * 2.0 / 3.0;
    $ret += (20.0 * sin($lon * $pi) + 40.0 * sin($lon / 3.0 * $pi)) * 2.0 / 3.0;
    $ret += (160.0 * sin($lon / 12.0 * $pi) + 320 * sin($lon * $pi / 30.0)) * 2.0 / 3.0;
    return $ret;
  }

  //转换lon
  public function transformLon($lat, $lon)
  {
    $pi = 3.14159265358979324;
    $ret = 300.0 + $lat + 2.0 * $lon + 0.1 * $lat * $lat + 0.1 * $lat * $lon + 0.1 * sqrt(abs($lat));
    $ret += (20.0 * sin(6.0 * $lat * $pi) + 20.0 * sin(2.0 * $lat * $pi)) * 2.0 / 3.0;
    $ret += (20.0 * sin($lat * $pi) + 40.0 * sin($lat / 3.0 * $pi)) * 2.0 / 3.0;
    $ret += (150.0 * sin($lat / 12.0 * $pi) + 300.0 * sin($lat / 30.0 * $pi)) * 2.0 / 3.0;
    return $ret;
  }


  /**
   * 计算两组经纬度坐标 之间的距离
   * return m or km
   * @param float $lng1 经度1
   * @param float $lat1 纬度1
   * @param float $lng2 经度2
   * @param float $lat2 纬度2
   * @param int $len_type 1:m or 2:km
   * @param int $decimal 小数点
   * @return false|float
   */
  public static function getDistance($lng1, $lat1, $lng2, $lat2, $len_type = 2, $decimal = 2)
  {
    $radLat1 = $lat1 * PI / 180.0;
    $radLat2 = $lat2 * PI / 180.0;
    $a = $radLat1 - $radLat2;
    $b = ($lng1 * PI / 180.0) - ($lng2 * PI / 180.0);
    $s = 2 * asin(sqrt(pow(sin($a / 2), 2) + cos($radLat1) * cos($radLat2) * pow(sin($b / 2), 2)));
    $s = $s * EARTH_RADIUS;
    $s = round($s * 1000);
    if ($len_type > 1) {
      $s /= 1000;
    }
    return round($s, $decimal);
  }
}
