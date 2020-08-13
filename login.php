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
if(isset($_SESSION['email']) && !empty($_SESSION['email'])){
  header("Location: ./user_dashboard");
}
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

  $errors = array();
  if (!empty($_POST['token'])) {
    if (!hash_equals($_SESSION['token'], $_POST['token'])) {
      $error = "Server Timeout: Page Expired";
      unset($_POST);
      header("Location: ./login?error={$error}");
      exit;
    }
    foreach (['email', 'password'] as $value) {
      if (empty($_POST[$value])) {
        $error = "{$value} is required.";
        unset($_POST);
        header("Location: ./login?error={$error}");
        exit;
      }
    }
    if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
      $error = "Invalid Email Address";
      unset($_POST);
      header("Location: ./login?error={$error}");
      exit;
    }
    if (preg_match("/.{4,15}/", $_POST['password']) === 0) {
      $error = "Invalid login credentials";
      unset($_POST);
      header("Location: ./login?error={$error}");
      exit;
    }
    $email = mysqli_real_escape_string($db_handle->connectDB(), $_POST['email']);
    $password = mysqli_real_escape_string($db_handle->connectDB(), $_POST['password']);
    $query = "SELECT email, password, role FROM users WHERE email = '$email'";
    $count =  $db_handle->numRows($query);
    if ($count == 0) {
      $error =  "Unregistered Email!";
      unset($_POST);
      header("Location: ./login?error={$error}");
      exit;
    }
    $result = $db_handle->runQuery($query);
    if (!empty($result[0]['password'])) {
      if (!password_verify($password, $result[0]['password'])) {
        $error = "Invalid login credentials";
        unset($_POST);
        header("Location: ./login?error={$error}");
        exit;
      } else {
        $_SESSION['email'] = $email;
        unset($_POST);
        if($result[0]['role'] == 'admin')
          header("Location: ./admin_dashboard");
        elseif($result[0]['role'] == 'user')
          header("Location: ./user_dashboard");
        exit;
      }
    }
  } else {
    $error = "Server Timeout: Page Expired";
    unset($_POST);
    header("Location: ./login?error={$error}");
  }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>Document</title>
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
          <form method="POST" action="./login" class="uk-form-stacked">
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
              <label class="uk-form-label" for="form-stacked-text">E-mail Address</label>
              <div class="uk-inline">
                <span class="uk-form-icon" uk-icon="icon: user"></span>
                <input id="email" name="email" class="uk-input  uk-form-width-large" type="email">
              </div>
            </div>

            <div class="uk-margin">
              <label class="uk-form-label" for="form-stacked-text">Password</label>
              <div class="uk-inline">
                <span class="uk-form-icon" uk-icon="icon: lock"></span>
                <input id="password" name="password" class="uk-input  uk-form-width-large" type="password">
              </div>
            </div>

            <div class="uk-width-1-1">
              <button class="uk-button uk-button-primary uk-padding-rigth-small">Login</button>
              <a href="./reset_password">Forget password?</a>
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