<?php
	class DB
	{
		private static $instance = null;
		
		final private function __construct() {}
		final private function __clone() {}
		
		public static function instance() {
			if (self::$instance === null) {
				self::$instance = new PDO(
					'mysql:host='.DB_HOST.';dbname='.DB_NAME,
					DB_USER,
					DB_PASS,
					array(
						PDO::ATTR_PERSISTENT => true,
						PDO::MYSQL_ATTR_INIT_COMMAND => 'SET CHARACTER SET utf8',
					)
				);
				self::$instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			}
			
			return self::$instance;
		}
		
		public static function q() {
			$args = func_get_args();
			$c = count($args);
			
			if ($c == 0) {
				throw new InvalidArgumentException('No Query given!');
			}
			
			if ($c == 1) {
				return self::instance()->query($args[0]);
			}
			
			return self::instance()->query(self::autoQuote(array_shift($args), $c == 2 && is_array($args[0]) ? $args[0] : $args));
		}
		
		public static function x() {
			$args = func_get_args();
			$c = count($args);
			
			if ($c == 0) {
				throw new InvalidArgumentException('No Query given!');
			}
			
			if ($c == 1) {
				return self::instance()->exec($args[0]);
			}
			
			return self::instance()->exec(self::autoQuote(array_shift($args), $c == 2 && is_array($args[0]) ? $args[0] : $args));
		}
		
		public static function autoQuote($query, $args) {
			$c = count($args); // num of args
			$l = strlen($query); // length of query
			$i = 0; // position in string
			while (
				   $c-- // there still are args
				&& false !== ($i = strpos($query, '?', $i)) // needle still exists
				&& $i < $l // not near end
			) {
				// $i+1 is the quote-r
				if ($i+1 >= $l || false === $type = strpos('si', $query[$i+1])) {
					// no or unsupported quote-r given
					// => direct insert
					$replace = array_shift($args);
					$query = substr_replace($query, $replace, $i, 1);
					$i += strlen($replace);
					$l += strlen($replace);
					continue;
				}
				
				if ($type == 0) {
					$replace = '\''.addslashes(array_shift($args)).'\'';
				}
				elseif ($type == 1) {
					$replace = intval(array_shift($args));
				}
				
				$query = substr_replace($query, $replace, $i, 2);
				$i += strlen($replace)-1; // -1 due to
				$l += strlen($replace)-1; // removal of quote-r
			}
			
			return $query;
		}
		
		public static function __callStatic($method, $args) {
			return call_user_func_array(array(self::instance(), $method), $args);
		}
	}
?>