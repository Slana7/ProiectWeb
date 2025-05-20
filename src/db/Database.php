<?php
class Database {
    private static $conn;

    public static function connect() {
        if (!self::$conn) {
            $env = parse_ini_file(__DIR__ . '/../../.env');
            $dsn = "pgsql:host={$env['DB_HOST']};port={$env['DB_PORT']};dbname={$env['DB_NAME']}";
            self::$conn = new PDO($dsn, $env['DB_USER'], $env['DB_PASS']);
            self::$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
        return self::$conn;
    }
}
