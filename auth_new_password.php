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
if (isset($_SESSION['email']) && !empty($_SESSION['email'])) {
  header("Location: ./user_dashboard");
}
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

  $errors = array();
  if (!empty($_POST['token']) && isset($_GET['st']) && isset($_GET['ut']) && isset($_GET['clx'])) {
    if (!hash_equals($_SESSION['token'], $_POST['token'])) {
      $error = "Server Timeout: Page Expired";
      unset($_POST);
      header("Location: ./reset_password?error={$error}");
      exit;
    } elseif (!hash_equals($_SESSION['token'], $_GET['st'])) {
      $error = "Server Timeout: Page Expired";
      unset($_POST);
      header("Location: ./reset_password?error={$error}");
      exit;
    }
    foreach (['c_password', 'password'] as $value) {
      if (empty($_POST[$value])) {
        $error = "{$value} is required.";
        unset($_POST);
        header("Location: ./auth_new_password?error={$error}");
        exit;
      }
    }
    if (!filter_var($_GET['clx'], FILTER_VALIDATE_EMAIL)) {
      $error = "Error: Invalid Request";
      unset($_POST);
      header("Location: ./reset_password?error={$error}");
      exit;
    }
    if (preg_match("/.{4,15}/", $_POST['password']) === 0) {
      $errors['password'] = "Password Must be between 4 & 15 Characters.";
    }
    if (preg_match("/.{4,15}/", $_POST['c_password']) === 0) {
      $errors['c_password'] = "Confirm Password Must be between 4 & 15 Characters.";
    }
    if ($_POST['password'] != $_POST['c_password']) {
      $errors['c_password'] = "Passwords do not match";
    }
    $password = password_hash(mysqli_real_escape_string($db_handle->connectDB(), $_POST['password']), PASSWORD_BCRYPT);
    $email = mysqli_real_escape_string($db_handle->connectDB(), $_GET['clx']);
    $ut = mysqli_real_escape_string($db_handle->connectDB(), $_GET['ut']);
    $query = "SELECT email,token FROM users WHERE email = '$email'";
    $count =  $db_handle->numRows($query);
    if ($count == 0) {
      $error = "Error: Invalid Request";
      unset($_POST);
      header("Location: ./reset_password?error={$error}");
      exit;
    }
    $result = $db_handle->runQuery($query);
    if (password_verify($result[0]['token'], $ut)) {
      $new_token = token_code_gen();
      $query = "UPDATE users set password = '$password', token = '$new_token' WHERE email = '$email'";
      $result = $db_handle->updateQuery($query);
      if (!empty($result)) {
        $success =  "Password Changed Successfully!";
        unset($_POST);
        header("Location: ./login?success={$success}");
        exit;
      } else {
        $error = "Server Error: Cannot complete request";
        unset($_POST);
        header("Location: ./login?error={$error}");
        exit;
      }
    } else {
      $error = "Error: Invalid Request";
      unset($_POST);
      header("Location: ./reset_password?error={$error}");
      exit;
    }
  } else {
    $error = "Server Timeout: Page Expired";
    unset($_POST);
    header("Location: ./login?error={$error}");
    exit;
  }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>New Password</title>
  <link rel="stylesheet" href="//cdn.jsdelivr.net/npm/uikit@3.3.7/dist/css/uikit.min.css" />
  <link rel="stylesheet" href="app.css" />
  <link rel="stylesheet" href="//cdn.materialdesignicons.com/5.0.45/css/materialdesignicons.min.css" />
</head>

<body>

  <?php sideBarType($role); ?>
  <?php require_once('include/header.php'); ?>

  <!--=============================-->
  <div class="uk-container uk-padding-small content">
    <div uk-grid>
      <div class="uk-width-1-1 ">
        <div class="uk-card uk-card-default uk-card-body">
          <form method="POST" action="<?php
                                      if (isset($_GET['st']) && isset($_GET['ut']) && isset($_GET['clx'])){
                                        echo "./auth_new_password?st={$_GET['st']}&ut={$_GET['ut']}&clx={$_GET['clx']}";
                                      }else{
                                        echo "./auth_new_password";
                                      }
                                      ?>" class="uk-form-stacked">
            <input type="hidden" id="token" name="token" value="<?php echo $_SESSION['token']; ?>">
            <div class="uk-margin">
              <?php if ($error) {
                echo "<div class=\"uk-alert-danger uk-margin-remove-bottom\" uk-alert>
                                <a class=\"uk-alert-close\" uk-close></a>
                                <p>{$error}</p>
                            </div>";
              } elseif ($success) {
                echo "<div class=\"uk-alert-success uk-margin-remove-bottom\" uk-alert>
                                <a class=\"uk-alert-close\" uk-close></a>
                                <p>{$success}</p>
                            </div>";
              }
              ?>
            </div>

            <div class="uk-margin">
              <label class="uk-form-label" for="form-stacked-text">New Password</label>
              <div class="uk-inline">
                <span class="uk-form-icon" uk-icon="icon: lock"></span>
                <input id="password" name="password" class="uk-input  uk-form-width-large" type="password">
              </div>
            </div>

            <div class="uk-margin">
              <label class="uk-form-label" for="form-stacked-text">Confirm Password</label>
              <div class="uk-inline">
                <span class="uk-form-icon" uk-icon="icon: lock"></span>
                <input id="c_password" name="c_password" class="uk-input  uk-form-width-large" type="password">
              </div>
            </div>

            <div class="uk-width-1-1">
              <button class="uk-button uk-button-primary uk-padding-rigth-small">Change Password</button>
            </div>
          </form>
        </div>

      </div>
    </div>
    <!--=============================-->
    <script src="//cdn.jsdelivr.net/npm/uikit@3.3.7/dist/js/uikit.min.js"></script>
    <script src="//cdn.jsdelivr.net/npm/uikit@3.3.7/dist/js/uikit-icons.min.js"></script>
</body>

</html