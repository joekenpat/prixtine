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
  if (isset($_GET['job_id']) && isset($_GET['token']) && isset($_GET['redirect'])) {
    $job_id = mysqli_real_escape_string($db_handle->connectDB(), $_GET['job_id']);
    $client_token = mysqli_real_escape_string($db_handle->connectDB(), $_GET['token']);
    $redirect = mysqli_real_escape_string($db_handle->connectDB(), $_GET['redirect']);
    if (!empty($token)) {
      if (!hash_equals($_SESSION['token'], $client_token)) {
        $error = "Server Timeout: token is invalid";
        header("Location: ./$redirect?error={$error}");
        exit;
      } else {
        if ($role == 'admin') {
          $query = "UPDATE jobs SET completed_at = CURRENT_TIMESTAMP,status = 'completed' WHERE job_id = '$job_id'";
          $result = $db_handle->updateQuery($query);
          if (!empty($result)) {
            $success = "Marked Booking as completed";
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
      $error = "Server Timeout: Token is null";
      header("Location: ./{$redirect}?error={$error}");
      exit;
    }
  } else {
    $error = "Server Timeout: Request is invalid";
    header("Location: ./{$_GET['redirect']}?error={$error}");
    exit;
  }
}
