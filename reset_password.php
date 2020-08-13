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
  if (!empty($_POST['token'])) {
    if (!hash_equals($_SESSION['token'], $_POST['token'])) {
      $error = "Server Timeout: Page Expired";
      unset($_POST);
      header("Location: ./login?error={$error}");
      exit;
    }
    foreach (['email'] as $value) {
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
      header("Location: ./reset_password?error={$error}");
      exit;
    }
    $email = mysqli_real_escape_string($db_handle->connectDB(), $_POST['email']);
    $query = "SELECT email, role,token FROM users WHERE email = '$email'";
    $count =  $db_handle->numRows($query);
    if ($count == 0) {
      $error =  "No such account!";
      unset($_POST);
      header("Location: ./reset_password?error={$error}");
      exit;
    }
    $result = $db_handle->runQuery($query);
    $user_token = password_hash($result[0]['token'],PASSWORD_BCRYPT);
    $to = $email;
    $subject = "Prixtine | Password Reset";
    $headers = "From: helpdesk@app.prixtine.com.ng\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    $message = "<p>Somebody (hopefully you)  Requested a new password for the <a href='https://app.prixtine.com.ng'>prixtine.com.ng</a> account for <a href='mailto:{$email}'>{$email}</a>, no changes has been made to your account yet.</p>
                  <p>You can reset your password by clicking on the link below:
                    <br><a href='https://app.prixtine.com.ng/auth_new_password?clx={$email}&ut={$user_token}&st={$token}'>https://app.prixtine.com.ng/auth_new_password?clx={$email}&ut={$user_token}&st={$token}</a>.
                  </p>
                  <p> If you did not request a new password, please let us know by replying to this email.
                  <br>Yours,
                  <br>The Prixtine Team.</p>";
      mail($to, $subject, $message, $headers);
        $success = "A Password recovery mail was sent to {$email} <br>dont see it in your inbox check your <b>spam</b>";
        unset($_POST);
        header("Location: reset_password.php?clx={$email}&success={$success}&mail_sent=1");
  } else {
    $error = "Server Timeout: Page Expired";
    unset($_POST);
    header("Location: ./reset_password?error={$error}");
  }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>Reset Password</title>
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
          <form method="POST" action="./reset_password" class="uk-form-stacked">
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
            <div class="uk-width-1-1">
              <button class="uk-button uk-button-primary uk-padding-rigth-small">Send Reset Link</button>
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