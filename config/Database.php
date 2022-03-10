<?php
    class Database {

        private static $connection = null;

        public static function connect() {
            if (is_null(self::$connection)) {
                $url = getenv('JAWSDB_URL');
                $dbparts = parse_url(getenv("JAWSDB_URL"));
                $hostname = $dbparts['host'];
                $database = substr($dbparts['path'], 1);
                $username = $dbparts['user'];
                $password = $dbparts['pass'];
                self::$connection = new PDO("mysql:host=$hostname;dbname=$database", $username, $password);
                self::$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            }
            return self::$connection;
        }

        public static function reset() {
            self::$connection = null;
        }

    }