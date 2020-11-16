<?php
/**
 *
 * 作者: 范圣帅(fanshengshuai@gmail.com)
 *
 * 创建: 2019-01-04 11:19:33
 * vim: set expandtab sw=4 ts=4 sts=4 *
 *
 */

class FService
{
  /**
   * @var FMongo
   */
  var $mongo;
  static $_mongo;
  static $models = [];

  static public function getInstance()
  {
    if (!self::$_mongo) {
      self::$_mongo= FMongo::getInstance();
    }

    $name = get_called_class();
    if (!isset(self::$models[$name])) {
      self::$models[$name] = new $name();
      self::$models[$name]->mongo = self::$_mongo;
    }

    return self::$models[$name];
  }


  // /**
  //  * @var FTaskTracker
  //  */
  // protected static $_instance;
  //
  // /**
  //  * @return $this
  //  */
  // public static function getInstance()
  // {
  //   if (self::$_instance === null) {
  //     self::$_instance = new self;
  //   }
  //
  //   return self::$_instance;
  // }

  protected function getMongo()
  {
    if (!self::$mongo) {
      self::$mongo= FMongo::getInstance();
    }

    return self::$mongo;
  }
}
