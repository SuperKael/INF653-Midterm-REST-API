<?php
    class Database {

        private static $connection = null;

        public static function connect() {
            if (is_null(self::$connection)) {
                self::$connection = new PDO(getenv("JAWSDB_URL"));
            }
            return self::$connection;
        }

        public static function reset() {
            self::$connection = null;
        }

    }