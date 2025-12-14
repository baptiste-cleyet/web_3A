<?php
class Database {
    private static ?Database $instance = null;
    private PDO $connection;
    private function __construct() {
        $dsn = 'mysql:host=localhost;dbname=projet_db;charset=utf8mb4';
        $user = 'root';
        $pass = '';
        $this->connection = new PDO($dsn, $user, $pass);
        $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
    public static function getInstance(): Database {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }
    public function getConnection(): PDO {
        return $this->connection;
    }
    private function __clone() {}
    public function __wakeup() {}
}