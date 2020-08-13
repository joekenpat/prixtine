<?php
session_start();
require_once("./misc.php");
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
            $error = "Server Timeout: token is invalid";
            unset($_POST);
            header("Location: ./registration?error={$error}");
        }
        foreach (['email', 'password', 'c_password', 'phone', 'address'] as $value) {
            if (empty($_POST[$value])) {
                $errors[$value] = "{$value} is required.";
            }
        }
        $_POST['email'] = preg_replace('/\s+/', '', $_POST['email']);
        if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = "Invalid Email Address";
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
        $_POST['phone'] = preg_replace('/\s+/', '', $_POST['phone']);
        if (!preg_match("/^[0]\d{10}$/", $_POST['phone']) === 0 || !!preg_match("/^234[0-9]{10}$/", $_POST['phone']) === 0) {
            $errors["phone"] = "Invalid phone number";
        }
        if (preg_match("/.{4,15}/", $_POST['address']) === 0) {
            $errors['address'] = "Address must be between 4 & 15 Characters.";
        }
        if (count($errors) === 0) {
            $email = mysqli_real_escape_string($db_handle->connectDB(), $_POST['email']);
            $phone = mysqli_real_escape_string($db_handle->connectDB(), $_POST['phone']);
            $password = mysqli_real_escape_string($db_handle->connectDB(), $_POST['password']);
            $address = mysqli_real_escape_string($db_handle->connectDB(), $_POST['address']);
            $hash_pwd = password_hash($password, PASSWORD_BCRYPT);
            $ip_address = ipAddress();
            $token = token_code_gen();
            //check if email is used already
            $query = "SELECT * FROM users WHERE email = '{$email}'";
            $count = $db_handle->numRows($query);
            if ($count >= 1) {
                $errors['email'] = "Email Address is already used, Please try another";
            }
            if (count($errors) === 0) {
                $query = "INSERT INTO
              users
              (email,password,address,phone,role,ip_address,created_at,updated_at,token)
              VALUES
              ('$email','$hash_pwd','$address','$phone','user','$ip_address',CURRENT_TIMESTAMP,NULL,'$token');";
            }
            $result = $db_handle->insertQuery($query);
            if (!empty($result)) {
                $success = "Registration Successful";
                unset($_POST);
                header("Location: ./login?success={$success}");
            } else {
                $error = "Sever Error: problem inserting value into database.";
                unset($_POST);
                header("Location: ./registration?error={$error}");
            }
        }
    } else {
        $error = "Server Timeout: token is null";
        unset($_POST);
        header("Location: ./registration?error={$error}");
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Registration</title>
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
                    <form method="POST" action="./registration" class="uk-form-stacked">
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
                                <input value="<?php if (isset($_POST['email'])) {
                                                    echo $_POST['email'];
                                                } ?>" id="email" name="email" class="uk-input  uk-form-width-large" type="email">
                            </div>
                            <?php if (isset($errors['email'])) {
                                echo "<span class=\"uk-text-danger\">{$errors['email']}</span>";
                            } ?>
                        </div>
                        <div class="uk-margin">
                            <label class="uk-form-label" for="form-stacked-text">Phone</label>
                            <div class="uk-inline">
                                <span class="uk-form-icon" uk-icon="icon: phone"></span>
                                <input value="<?php if (isset($_POST['phone'])) {
                                                    echo $_POST['phone'];
                                                } ?>" id="phone" name="phone" class="uk-input  uk-form-width-large" type="number">
                            </div>
                            <?php if (isset($errors['phone'])) {
                                echo "<span class=\"uk-text-danger\">{$errors['phone']}</span>";
                            } ?>
                        </div>
                        <div class="uk-margin">
                            <label class="uk-form-label" for="form-stacked-text">Password</label>
                            <div class="uk-inline">
                                <span class="uk-form-icon" uk-icon="icon: lock"></span>
                                <input id="password" name="password" class="uk-input  uk-form-width-large" type="password">
                            </div>
                            <?php if (isset($errors['password'])) {

                                echo "<span class=\"uk-text-danger\">{$errors['password']}</span>";
                            } ?>
                        </div>
                        <div class="uk-margin">
                            <label class="uk-form-label" for="form-stacked-text">Comfirm Password</label>
                            <div class="uk-inline">
                                <span class="uk-form-icon uk-form-icon" uk-icon="icon: lock"></span>
                                <input id="c_password" name="c_password" class="uk-input  uk-form-width-large" type="password">
                            </div>
                            <?php if (isset($errors['c_password'])) {
                                echo "<span class=\"uk-text-danger\">{$errors['c_password']}</span>";
                            } ?>
                        </div>
                        <div class="uk-margin">
                            <label class="uk-form-label" for="form-stacked-text">Address</label>
                            <div class="uk-inline">
                                <textarea id="address" name="address" class="uk-textarea  uk-form-width-large"><?php if (isset($_POST['address'])) {
                                                                                                                    echo $_POST['address'];
                                                                                                                } ?></textarea>
                            </div>
                            <?php if (isset($errors['address'])) {
                                echo "<span class=\"uk-text-danger\">{$errors['address']}</span>";
                            } ?>
                        </div>
                        <div class="uk-width-1-1">
                            <button class="uk-button uk-button-primary">Submit</button>
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