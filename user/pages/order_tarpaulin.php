<?php
session_start();
require_once '../../config/db.php';

function showAlert($message) {
    echo "<script>alert('" . addslashes($message) . "'); window.location.href='../index.php';</script>";
    exit();
}

if (!isset($_SESSION['client_id'])) {
    showAlert('Please login first.');
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $client_id = $_SESSION['client_id'];
    $width = mysqli_real_escape_string($conn, $_POST['width']);
    $height = mysqli_real_escape_string($conn, $_POST['height']);
    $quantity = mysqli_real_escape_string($conn, $_POST['quantity']);
    $notes = mysqli_real_escape_string($conn, $_POST['notes']);

    $design_file = "";

    if (isset($_FILES['design_file']) && $_FILES['design_file']['error'] == 0) {
        $uploadDir = "/opt/lampp/htdocs/printing/assets/uploads/designs/";
        
        if (!file_exists($uploadDir)) {
            if (!mkdir($uploadDir, 0777, true)) {
                showAlert('Failed to create upload folder.');
            }
        }
        
        if (!is_writable($uploadDir)) {
            chmod($uploadDir, 0777);
            if (!is_writable($uploadDir)) {
                showAlert('Upload folder is not writable.');
            }
        }
        
        $maxFileSize = 5 * 1024 * 1024;
        if ($_FILES['design_file']['size'] > $maxFileSize) {
            showAlert('File too large. Maximum size is 5MB.');
        }
        
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf'];
        if (!in_array($_FILES['design_file']['type'], $allowedTypes)) {
            showAlert('Invalid file type. Allowed: JPG, PNG, GIF, PDF');
        }
        
        $ext = pathinfo($_FILES['design_file']['name'], PATHINFO_EXTENSION);
        $newFileName = uniqid("design_") . "." . $ext;
        $uploadPath = $uploadDir . $newFileName;

        if (move_uploaded_file($_FILES['design_file']['tmp_name'], $uploadPath)) {
            $design_file = $newFileName;
        } else {
            showAlert('Failed to upload file. Please try again.');
        }
    } else {
        $errorCode = isset($_FILES['design_file']['error']) ? $_FILES['design_file']['error'] : 4;
        $errorMessages = [
            1 => "File exceeds upload_max_filesize",
            2 => "File exceeds MAX_FILE_SIZE",
            3 => "File was only partially uploaded",
            4 => "No file was uploaded",
            6 => "Missing temporary folder",
            7 => "Failed to write file to disk",
            8 => "PHP extension stopped file upload"
        ];
        $errorMsg = isset($errorMessages[$errorCode]) ? $errorMessages[$errorCode] : "Unknown upload error";
        showAlert($errorMsg);
    }

    $sql = "INSERT INTO orders (client_id, product_type, design_file, width, height, quantity, notes, status)
            VALUES ('$client_id', 'Tarpaulin', '$design_file', '$width', '$height', '$quantity', '$notes', 'pending')";

    if (mysqli_query($conn, $sql)) {
        showAlert('Order submitted successfully! We will review your quotation soon.');
    } else {
        showAlert('Database error. Please try again.');
    }
} else {
    header("Location: ../index.php");
    exit();
}
?>