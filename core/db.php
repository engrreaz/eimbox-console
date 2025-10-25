<?php

// Query counter + timer শুরু
$GLOBALS['queries_count'] = 0;
$GLOBALS['script_start'] = microtime(true);

function db_connect() {
    static $conn;

    if ($conn === null) {
        // Error ধরতে পারার জন্য রিপোর্ট মোড চালু
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

        // Extend করে mysqli ব্যবহার
        class MyDB extends mysqli {
            public function query(string $query, int $resultmode = MYSQLI_STORE_RESULT): mysqli_result|bool {
                $GLOBALS['queries_count']++;
                $GLOBALS['query_text'][] = $query;
                return parent::query($query, $resultmode);
            }

            public function prepare(string $query): mysqli_stmt|false {
                $GLOBALS['queries_count']++;
                $GLOBALS['query_text'][] = $query;
                return parent::prepare($query);
            }
        }

        try {
            $conn = new MyDB(DB_HOST, DB_USER, DB_PASS, DB_NAME);
            $conn->set_charset('utf8mb4');
        } catch (mysqli_sql_exception $e) {
            error_log("DB connection failed: " . $e->getMessage());
            echo "<script>alert('Database connection problem! Please try again later.');</script>";
            exit;
        }
    }

    return $conn;
}
$conn = db_connect();