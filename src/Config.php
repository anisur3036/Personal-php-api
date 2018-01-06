<?php
namespace Anis;

class Config {

	public static function get($path=null)
	{
		if($path) {
			$config = require('inc/env.php');
			// var_dump($config);
			$path = explode('/', $path);

			foreach ($path as $bit) {
				if(isset($config[$bit])) {
					$config = $config[$bit];
				}
			}

			return $config;
		}

		return false;
	}
}
