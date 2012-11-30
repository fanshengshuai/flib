<?php
/**
 *
 *      作者:范圣帅(fanshengshuai@comsenz.com)
 *  创建时间:2011-03-05 12:49:56
 *  修改记录:
 *  $Id$
 */

class Factory {
	private static $_instance;

	public function get($class) {

		static $factory;

		if (!$factory) {
			$factory = new self();
		}

		$class = explode('.', $class);

		$className = ucfirst($class[0]);
		$classFile = dirname(dirname(__FILE__)) . '/services/';

		$classCount = count($class);
		for ($i = 1; $i < $classCount; $i ++) {
			$className .= '_' . ucfirst($class[$i]);

			if ($i == $classCount - 1) {
				$classFile .= '/' . ucfirst($class[$i]);
				continue;
			}
			$classFile .= '/' . $class[$i];
		}
		$classFile .= '.php';


		$className =  str_replace('M_', 'Service_', $className);

		if ($factory->_instance[$className]) {
			return $this->_instance[$className];
		} else {
			if (file_exists($classFile)) {
				require_once($classFile);
				$factory->_instance[$className] = new $className;

				return $factory->_instance[$className];
			} else {
				return null;
			}
		}
	}
}
