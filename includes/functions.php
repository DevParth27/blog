<?php
session_start();
require_once __DIR__ . '/../config/database.php';

// Clean input data
function clean($data) {
    global $conn;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    $data = $conn->real_escape_string($data);
    return $data;
}

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Check if user is admin
function isAdmin() {
    return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1;
}

// Redirect to a specific page
function redirect($page) {
    header("Location: $page");
    exit();
}

// Upload image
// Upload image
function uploadImage($file) {
    $uploadDirRelative = "/blog/assets/uploads/";
    $uploadDirAbsolute = __DIR__ . '/../assets/uploads/';

    // Create directory if not exists
    if (!is_dir($uploadDirAbsolute)) {
        mkdir($uploadDirAbsolute, 0755, true);
    }

    $timestamp = time();
    $fileName = $timestamp . "_" . basename($file["name"]);
    $targetFile = $uploadDirAbsolute . $fileName;
    $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

    $check = getimagesize($file["tmp_name"]);
    if ($check === false) {
        return ["success" => false, "message" => "File is not an image."];
    }

    if ($file["size"] > 5000000) {
        return ["success" => false, "message" => "File too large."];
    }

    if (!in_array($imageFileType, ["jpg", "jpeg", "png", "gif"])) {
        return ["success" => false, "message" => "Invalid file format."];
    }

    if (move_uploaded_file($file["tmp_name"], $targetFile)) {
        return ["success" => true, "file_path" => $uploadDirRelative . $fileName];
    } else {
        return ["success" => false, "message" => "Upload failed."];
    }
}



// Get all posts
function getPosts() {
    global $conn;
    $sql = "SELECT p.*, u.username FROM posts p JOIN users u ON p.user_id = u.id ORDER BY p.created_at DESC";
    $result = $conn->query($sql);
    $posts = [];
    
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $posts[] = $row;
        }
    }
    
    return $posts;
}

// Get single post by ID
function getPost($id) {
    global $conn;
    $id = clean($id);
    $sql = "SELECT p.*, u.username FROM posts p JOIN users u ON p.user_id = u.id WHERE p.id = '$id'";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    
    return null;
}

// Get comments for a post
function getComments($post_id) {
    global $conn;
    $post_id = clean($post_id);
    $sql = "SELECT c.*, u.username FROM comments c JOIN users u ON c.user_id = u.id WHERE c.post_id = '$post_id' ORDER BY c.created_at DESC";
    $result = $conn->query($sql);
    $comments = [];
    
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $comments[] = $row;
        }
    }
    
    return $comments;
}

// Get user by ID
function getUser($id) {
    global $conn;
    $id = clean($id);
    $sql = "SELECT * FROM users WHERE id = '$id'";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    
    return null;
}

// Get all users
function getUsers() {
    global $conn;
    $sql = "SELECT * FROM users ORDER BY created_at DESC";
    $result = $conn->query($sql);
    $users = [];
    
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
    }
    
    return $users;
}
?>