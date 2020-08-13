<?php
session_start();
include("./misc.php");
require("./dbcontroller.php");
$db_handle = new DBcontroller();
if (!empty($_GET['success'])) {
  $success = mysqli_real_escape_string($db_handle->connectDB(), $_GET['success']);
} else {
  unset($_GET['success']);
}
if (!empty($_GET['error'])) {
  $error = mysqli_real_escape_string($db_handle->connectDB(), $_GET['error']);
} else {
  unset($_GET['error']);
}
if (isset($_SESSION['email'])) {
  $email = $_SESSION['email'];
} else {
  $error = "Login First!";
  header("Location: ./login?error={$error}");
}
$query = "SELECT * FROM users WHERE email = '$email'";
$count = $db_handle->numRows($query);
if ($count == 0) {
  $error = "Login First!";
  header("Location: ./login?error={$error}");
}
// load user data
$result = $db_handle->runQuery($query);
$phone = $result[0]['phone'];
$address = $result[0]['address'];
$role = $result[0]['role'];
$created_at = $result[0]['created_at'];

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
  if (isset($_GET['email']) && isset($_GET['token']) && isset($_GET['redirect'])) {
    $deletable_email = mysqli_real_escape_string($db_handle->connectDB(), $_GET['email']);
    $token = mysqli_real_escape_string($db_handle->connectDB(), $_GET['token']);
    $redirect = mysqli_real_escape_string($db_handle->connectDB(), $_GET['redirect']);
    if (!empty($token)) {
      if (!hash_equals($_SESSION['token'], $token)) {
        $error = "Server Timeout: token is invalid";
        header("Location: ./{$redirect}?error={$error}");
        exit;
      } else {
        if ($role == 'admin') {
          $query = "DELETE FROM users WHERE email = '$deletable_email'";
          $result = $db_handle->deleteQuery($query);
          if (!empty($result)) {
            $success = "User Deleted";
            header("Location: ./{$redirect}?success={$success}");
            exit;
          } else {
            $error = "Request Invalid: Server Error.";
            header("Location: ./{$redirect}?error={$error}");
            exit;
          }
        } else {
          $error = "Request Invalid: Unauthorized";
          header("Location: ./{$redirect}?error={$error}");
          exit;
        }
      }
    } else {
      $error = "Server Timeout: token is null";
      header("Location: ./{$redirect}?error={$error}");
      exit;
    }
  } else {
    $error = "Request Invalid: Invalid request.";
    header("Location: ./{$_GET['redirect']}?error={$error}");
    exit;
  }
}
