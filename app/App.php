<?php

namespace Keith\D6assesment;

class App
{
    public function run()
    {
        try {
            $db = Database::getInstance();
            $connection = $db->getConnection();
            
            if ($connection) {
                echo "DB Connected";
            }
        } catch (\PDOException $e) {
            http_response_code(500);
            echo "Database connection failed: " . $e->getMessage();
        }
    }
}
?>
