<?php

header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Headers: X-Requested-With, Content-Type, Authorization");
require_once("../logger.php");

$path = "..".DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR;
// HerikaServer conf not needed in minimal version
// Database not needed in minimal version
// Database not needed in minimal version
// Fix missing config.php warning
$pluginPath = "/var/www/html/HerikaServer/ext/minai_minimal";
if (!file_exists("$pluginPath/config.php")) {
    copy("$pluginPath/config.base.php", "$pluginPath/config.php");
}
require_once("..".DIRECTORY_SEPARATOR."config.php");
require_once("..".DIRECTORY_SEPARATOR."util.php");
// Database functionality disabled in minimal version
// require_once("..".DIRECTORY_SEPARATOR."importDataToDB.php");
// require_once("..".DIRECTORY_SEPARATOR."db_utils.php");
// InitiateDBTables();

$requestMethod = $_SERVER['REQUEST_METHOD'];
$data = json_decode(file_get_contents("php://input"), true);

// Determine the endpoint being accessed
$endpoint = isset($_GET['endpoint']) ? $_GET['endpoint'] : null;

switch ($requestMethod) {
    case 'GET':
        handleGetRequest($endpoint);
        break;
        
    case 'POST':
        handlePostRequest($endpoint, $data);
        break;
        
    case 'PUT':
        handlePutRequest($endpoint, $data);
        break;
        
    case 'DELETE':
        handleDeleteRequest($endpoint, $data);
        break;
        
    case 'OPTIONS':
        // Handle preflight request (CORS-related)
        http_response_code(200);
        exit();
        
    default:
        http_response_code(405); // Method Not Allowed
        echo json_encode(["message" => "Method Not Allowed"]);
        break;
}

// Handle GET requests
function handleGetRequest($endpoint) {
    if ($endpoint === 'index_payload') {
        // Example response for a GET request
        $nsfw = ((IsModEnabled("sexlab") || IsModEnabled("ostim")) && !$GLOBALS["disable_nsfw"]) ? "nsfw" : "sfw";
        echo json_encode(["message" => "GET request received", "data" => ["nsfw" => $nsfw]]);
    } else {
        echo json_encode(["message" => "GET endpoint not found"]);
    }
}

// Handle POST requests
function handlePostRequest($endpoint, $data) {
    // Database functionality disabled in minimal version
    if ($endpoint === 'reset_personalities') {
        echo json_encode(["message" => "Database features not available in minimal version"]);
    } elseif ($endpoint === 'reset_scenes') {
        echo json_encode(["message" => "Database features not available in minimal version"]);
    } else {
        echo json_encode(["message" => "POST endpoint not found"]);
    }
}

// Handle PUT requests
function handlePutRequest($endpoint, $data) {
    if ($endpoint === 'example') {
        // Example response for a PUT request
        echo json_encode(["message" => "PUT request received", "data" => $data]);
    } else {
        echo json_encode(["message" => "PUT endpoint not found"]);
    }
}

// Handle DELETE requests
function handleDeleteRequest($endpoint, $data) {
    if ($endpoint === 'example') {
        // Example response for a DELETE request
        echo json_encode(["message" => "DELETE request received", "data" => $data]);
    } else {
        echo json_encode(["message" => "DELETE endpoint not found"]);
    }
}

