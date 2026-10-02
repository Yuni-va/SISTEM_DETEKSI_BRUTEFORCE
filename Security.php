<?php

if(session_status() === PHP_SESSION_NONE){
    session_start();
}

function csrf_token()
{
    if(empty($_SESSION['csrf_token'])){
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function csrf_field()
{
    return '<input type="hidden" name="csrf_token" value="'.htmlspecialchars(csrf_token(),ENT_QUOTES,'UTF-8').'">';
}

function verify_csrf_token($token)
{
    return isset($_SESSION['csrf_token']) && is_string($token) && hash_equals($_SESSION['csrf_token'],$token);
}

function require_post()
{
    if($_SERVER['REQUEST_METHOD'] !== "POST"){
        http_response_code(405);
        exit("Method Not Allowed");
    }
}

function require_csrf()
{
    $token = $_POST['csrf_token'] ?? "";

    if(!verify_csrf_token($token)){
        http_response_code(419);
        exit("CSRF token tidak valid.");
    }
}

function redirect_if_guest($loginPath)
{
    if(empty($_SESSION['id'])){
        header("Location: ".$loginPath);
        exit;
    }
}

function redirect_if_not_admin($loginPath)
{
    redirect_if_guest($loginPath);

    $role = $_SESSION['role'] ?? "";

    if($role !== "Administrator" && $role !== "admin"){
        http_response_code(403);
        exit("Akses ditolak.");
    }
}

function input_string($source,$key,$maxLength=255)
{
    $value = trim((string) ($source[$key] ?? ""));

    if(strlen($value) > $maxLength){
        $value = substr($value,0,$maxLength);
    }

    return $value;
}

function valid_choice($value,$allowed,$default)
{
    return in_array($value,$allowed,true) ? $value : $default;
}

function api_json($data,$statusCode=200)
{
    http_response_code($statusCode);
    header("Content-Type: application/json; charset=UTF-8");
    echo json_encode($data,JSON_PRETTY_PRINT);
    exit;
}

function api_input()
{
    $raw = file_get_contents("php://input");
    $json = json_decode($raw,true);

    if(is_array($json)){
        return $json;
    }

    return $_POST;
}

function api_token_from_request()
{
    $headers = function_exists("getallheaders") ? getallheaders() : [];

    foreach($headers as $key => $value){
        if(strtolower($key) === "x-api-token"){
            return trim($value);
        }
    }

    return trim($_GET['token'] ?? $_POST['token'] ?? "");
}

function require_api_token($expectedToken)
{
    $providedToken = api_token_from_request();

    if($expectedToken === "" || !hash_equals($expectedToken,$providedToken)){
        api_json(["success" => false, "message" => "Token API tidak valid."],401);
    }
}

