<?php
    class DB
    {
        protected static $instance = null;

        final private function __construct() {}
        final private function __clone() {}

        /**
         * @return PDO
         */
        public static function instance() {
            if (self::$instance === null) {
                try {
                    self::$instance = new PDO(
                        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME,
                        DB_USER,
                        DB_PASS
                    );
                    self::$instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                }
                catch (PDOException $e) {
                    die('Database connection could not be established.');
                }
            }

            return self::$instance;
        }

        /**
         * @return PDOStatement
         */
        public static function q($query) {
            if (func_num_args() == 1) {
                return self::instance()->query($query);
            }

            $args = func_get_args();
            return self::instance()->query(self::autoQuote(array_shift($args), $args));
        }

        public static function x($query) {
            if (func_num_args() == 1) {
                return self::instance()->exec($query);
            }

            $args = func_get_args();
            return self::instance()->exec(self::autoQuote(array_shift($args), $args));
        }

        public static function autoQuote($query, array $args) {
            $i = strlen($query) - 1;
            $c = count($args);

            while ($i--) {
                if ('?' === $query[$i] && false !== $type = strpos('sia', $query[$i + 1])) {
                    if (--$c < 0) {
                        throw new InvalidArgumentException('Too little parameters.');
                    }

                    if (0 === $type) {
                        $replace = self::instance()->quote($args[$c]);
                    } elseif (1 === $type) {
                        $replace = intval($args[$c]);
                    } elseif (2 === $type) {
                        foreach ($args[$c] as &$value) {
                            $value = self::instance()->quote($value);
                        }
                        $replace = '(' . implode(',', $args[$c]) . ')';
                    }

                    $query = substr_replace($query, $replace, $i, 2);
                }
            }

            if ($c > 0) {
                throw new InvalidArgumentException('Too many parameters.');
            }

            return $query;
        }

        public static function beginTransaction() {
            return self::instance()->beginTransaction();
        }
        public static function commit() {
            return self::instance()->commit();
        }
        public static function errorCode() {
            return self::instance()->errorCode();
        }
        public static function errorInfo() {
            return self::instance()->errorInfo();
        }
        public static function exec($statement) {
            return self::instance()->exec($statement);
        }
        public static function getAttribute($attribute) {
            return self::instance()->getAttribute($attribute);
        }
        public static function getAvailableDrivers() {
            return self::instance()->getAvailableDrivers();
        }
        public static function inTransaction() {
            return self::instance()->inTransaction();
        }
        public static function lastInsertId($name = NULL) {
            return self::instance()->lastInsertId($name);
        }
        public static function prepare($statement, $driver_options = array()) {
            return self::instance()->prepare($statement, $driver_options);
        }
        public static function query() {
            $arguments = func_get_args();
            return call_user_func_array(array(self::instance(), 'query'), $arguments);
        }
        public static function quote($string, $parameter_type = PDO::PARAM_STR) {
            return self::instance()->quote($string, $parameter_type);
        }
        public static function rollBack() {
            return self::instance()->rollBack();
        }
        public static function setAttribute($attribute, $value) {
            return self::instance()->setAttribute($attribute, $value);
        }
    }