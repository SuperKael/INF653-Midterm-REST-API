<?php
    header('Access-Control-Allow-Origin: *');
    header('Content-Type: application/json');

    include_once '../../config/database.php';

    try {
        $db = Database::connect();
    } catch (PDOException $e) {
        error_log($e);
        http_response_code(500);
        echo json_encode(
            array('message' => "Database connection failed")
        );
        return;
    }

    switch ($_SERVER['REQUEST_METHOD']) {
        case 'GET':
            $params = array_values(array_filter([ "id" ], function($key) { return isset($_GET[$key]); }));
            $query = 'SELECT id, author FROM authors';
            if (count($params) > 0) {
                $query .= ' WHERE ' . join(' AND ', array_map(function($key) { return $key . ' = ?'; }, $params));
            }
            $stmt = $db->prepare($query);
            $stmt->execute(array_map(function($key) { return $_GET[$key]; }, $params));
            if ($stmt->rowCount() > 0) {
                $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                http_response_code(200);
                echo json_encode($rows);
            } else {
                http_response_code(404);
                echo json_encode(
                    array('message' => "authorId Not Found")
                );
            }
        case 'POST':
            
            break;
        case 'PUT':
            
            break;
        case 'DELETE':
            
            break;
    }

