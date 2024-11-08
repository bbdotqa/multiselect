<?php
session_start();

// Include Composer's autoloader
require 'vendor/autoload.php';  // Make sure this path is correct based on your project structure

// Database configuration
$servername = "localhost";
$username = "root"; // Change to your DB username
$password = ""; // Change to your DB password
$dbname = "file_uploads"; // The name of your database

// Create connection using try-catch
try {
    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}

// Use PHPMailer namespace
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Function to insert file details
function insertFileDetails($conn, $filename, $fileType, $filePath) {
    $stmt = $conn->prepare("INSERT INTO uploaded_files (filename, file_type, file_path) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $filename, $fileType, $filePath);
    return $stmt->execute();
}

// Max file upload size (in bytes). Example: 50MB = 50 * 1024 * 1024
$maxFileSize = 50 * 1024 * 1024; // 50MB

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['files'])) {
    // Allowed file types
    $allowedTypes = ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx'];
    $uploadsDir = 'uploads/';
    $errors = [];
    $uploadedFiles = [];

    foreach ($_FILES['files']['name'] as $key => $filename) {
        $tmpName = $_FILES['files']['tmp_name'][$key];
        $fileType = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $targetPath = $uploadsDir . basename($filename);

        // Check file size
        if ($_FILES['files']['size'][$key] > $maxFileSize) {
            $errors[] = "File '$filename' is too large. Maximum allowed size is " . ($maxFileSize / 1024 / 1024) . " MB.";
            continue;
        }

        // Check file type
        if (!in_array($fileType, $allowedTypes)) {
            $errors[] = "File '$filename' is not allowed. Only the following types are allowed: " . implode(', ', $allowedTypes);
            continue;
        }

        // Attempt to move the file
        if (move_uploaded_file($tmpName, $targetPath) && insertFileDetails($conn, $filename, $fileType, $targetPath)) {
            $uploadedFiles[] = $filename;

            // Send an email notification for all allowed file types (not just images)
            $mail = new PHPMailer(true);
            try {
                // Set mailer to use SMTP
                $mail->isSMTP();
                $mail->Host = 'sandbox.smtp.mailtrap.io'; // Mailtrap SMTP server
                $mail->SMTPAuth = true;
                $mail->Username = '9f2f2ba62dabbe'; // Mailtrap Username
                $mail->Password = '6ce12c637c175d'; // Mailtrap Password
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 465; // SMTP Port

                // Recipients
                $mail->setFrom('no-reply@example.com', 'File Upload');
                $mail->addAddress('gettt@yopmail.com', 'Admin'); // Your email address

                // Content
                $mail->isHTML(true);
                $mail->Subject = 'New File Uploaded';
                $mail->Body    = "A new file has been uploaded: <b>$filename</b><br>File Type: $fileType<br>File Path: $targetPath";

                // Send the email
                $mail->send();
            } catch (Exception $e) {
                $errors[] = "Failed to send email for '$filename'. Error: {$mail->ErrorInfo}";
            }
        } else {
            $errors[] = "Failed to upload or save '$filename'.";
        }
    }

    // If there were no errors, set success message
    if (empty($errors)) {
        $_SESSION['success_message'] = 'Files uploaded successfully!';
    } else {
        $_SESSION['error_message'] = implode('<br>', $errors);
    }

    // Redirect to the main page
    header('Location: index.php'); // or index.php depending on your setup
    exit; // Ensure no further code is executed after the redirect
}

// Close the database connection
$conn->close();
?>
