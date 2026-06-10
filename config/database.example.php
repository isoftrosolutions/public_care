<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'public_care_ayurveda');

function getDB() {
    static $conn = null;
    if ($conn === null) {
        try {
            $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
            if ($conn->connect_error) {
                error_log("DB connection failed: " . $conn->connect_error);
                die("Service temporarily unavailable. Please try again later.");
            }
        } catch (mysqli_sql_exception $e) {
            error_log("DB connection exception: " . $e->getMessage());
            die("Service temporarily unavailable. Please try again later.");
        }
    }
    return $conn;
}
