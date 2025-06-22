<?php
require_once __DIR__ . '/../config/config.php';

class Database {
    private static $conn;
    public static function connect() {
        if (!self::$conn) {
            $dsn = "pgsql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME;
            
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            
            if (DB_HOST !== 'localhost' && strpos(DB_HOST, 'rds.amazonaws.com') !== false) {
                $dsn .= ";sslmode=require";
            }
            
            self::$conn = new PDO($dsn, DB_USER, DB_PASS, $options);
        }
        return self::$conn;
    }
}
