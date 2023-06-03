<?php
$user = [
    "username" => "admin",
    "password" => "ii13d2"
];
if (!isset($_SERVER['PHP_AUTH_USER'])) {
    header('WWW-Authenticate: Basic realm="ChatGPT Realm"');
    header('HTTP/1.0 401 Unauthorized');
    echo 'Sign in first!';
    exit;
} else {
    if (
        ($_SERVER['PHP_AUTH_USER'] != $user["username"]) ||
        ($_SERVER['PHP_AUTH_PW'] != $user["password"])
    ) {
        unset($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']);
        session_unset();
        session_destroy();
        header('WWW-Authenticate: Basic realm="Auth"');
        header('HTTP/1.0 401 Unauthorized');
        exit;
    }
}