<?php
    header('Access-Control-Allow-Origin: *');
    header('Content-Type: application/json');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
    header('Access-Control-Allow-Headers: Access-Control-Allow-Headers,Content-Type,Access-Control-Allow-Methods, Authorization, X-Requested-With');

    include_once '../../config/Database.php';

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

    $input = json_decode(file_get_contents("php://input"), true);

    switch ($_SERVER['REQUEST_METHOD']) {
        case 'GET':
            $params = array_values(array_filter([ 'id', 'author' ], function($key) { return isset($_GET[$key]); }));
            $query = 'SELECT id, author FROM authors';
            if (count($params) > 0) {
                $query .= ' WHERE ' . join(' AND ', array_map(function($key) { return $key . ' = ?'; }, $params));
            }
            $stmt = $db->prepare($query);
            $stmt->execute(array_map(function($key) { return $_GET[$key]; }, $params));
            $rowCount = $stmt->rowCount();
            if ($rowCount > 0) {
                $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                http_response_code(200);
                if (isset($_GET['random']) && $_GET['random'] == 'true') {
                    echo json_encode($rows[rand(0, $rowCount - 1)]);
                } else {
                    echo json_encode(!isset($_GET['id']) ? $rows : $rows[0]);
                }
            } else {
                http_response_code(404);
                echo json_encode(
                    array('message' => "authorId Not Found")
                );
            }
            break;
        case 'POST':
            $acceptParams = [ 'author' ];
            foreach ($acceptParams as $param) {
                if (!isset($input[$param])) {
                    http_response_code(400);
                    echo json_encode(
                        array('message' => "Missing Required Parameters")
                    );
                    return;
                }
            }
            $query = 'INSERT INTO authors (author) VALUES (:author)';
            $stmt = $db->prepare($query);
            try {
                $stmt->execute(array_filter($input, function($key) use($acceptParams) { return in_array($key, $acceptParams); }, ARRAY_FILTER_USE_KEY));
            } catch (PDOException $ex) {
                http_response_code(400);
                throw $ex;
            }
            http_response_code(201);
            echo json_encode([
                'id' => $db->lastInsertId(),
                'author' => $input['author']
            ]);
            break;
        case 'PUT':
            $acceptParams = [ 'id', 'author' ];
            foreach ($acceptParams as $param) {
                if (!isset($input[$param])) {
                    http_response_code(400);
                    echo json_encode(
                        array('message' => "Missing Required Parameters")
                    );
                    return;
                }
            }
            $query = 'UPDATE authors SET author = :author WHERE id = :id';
            $stmt = $db->prepare($query);
            try {
                $stmt->execute(array_filter($input, function($key) use($acceptParams) { return in_array($key, $acceptParams); }, ARRAY_FILTER_USE_KEY));
            } catch (PDOException $ex) {
                http_response_code(400);
                throw $ex;
            }
            if ($stmt->rowCount() > 0) {
                http_response_code(200);
                echo json_encode([
                    'id' => $input['id'],
                    'author' => $input['author']
                ]);
            } else {
                http_response_code(400);
                echo json_encode(
                    array('message' => "No Authors Found")
                );
            }
            break;
        case 'DELETE':
            $acceptParams = [ 'id' ];
            foreach ($acceptParams as $param) {
                if (!isset($input[$param])) {
                    http_response_code(400);
                    echo json_encode(
                        array('message' => "Missing Required Parameters")
                    );
                    return;
                }
            }
            $query = 'DELETE FROM authors WHERE id = :id';
            $stmt = $db->prepare($query);
            try {
                $stmt->execute(array_filter($input, function($key) use($acceptParams) { return in_array($key, $acceptParams); }, ARRAY_FILTER_USE_KEY));
            } catch (PDOException $ex) {
                http_response_code(400);
                $message = $ex->getMessage();
                if (strpos($message, 'foreign key') !== false) {
                    echo json_encode(
                        array('message' => "Author cannot be deleted because it is required by one or more quotes")
                    );
                } else {
                    throw $ex;
                }
                return;
            }
            if ($stmt->rowCount() > 0) {
                http_response_code(200);
                echo json_encode([
                    'id' => $input['id']
                ]);
            } else {
                http_response_code(400);
                echo json_encode(
                    array('message' => "No Authors Found")
                );
            }
            break;
    }

