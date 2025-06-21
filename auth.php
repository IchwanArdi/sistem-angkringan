<?php
// auth.php
session_start();
require_once 'config/database.php';

function login($username, $password) {
    $database = new Database();
    $db = $database->getConnection();
    
    $query = "SELECT id, username, password, nama FROM admin WHERE username = ?";
    $stmt = $db->prepare($query);
    $stmt->bindParam(1, $username);
    $stmt->execute();
    
    if($stmt->rowCount() > 0) {
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if(password_verify($password, $row['password'])) {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['nama'] = $row['nama'];
            return true;
        }
    }
    return false;
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function logout() {
    session_destroy();
}

function requireLogin() {
    if(!isLoggedIn()) {
        header("Location: login.php");
        exit();
    }
}
?>