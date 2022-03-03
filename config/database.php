<?php
    class Database {

        private $connection = null;

        public static function connect() {
            if (is_null($this->connection)) {
                $this->connection = new PDO(getenv("JAWSDB_URL"));
            }
            return $this->connection;
        }

        public static function reset() {
            $this->connection = null;
        }

    }