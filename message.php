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


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $errors = array();
    if (!empty($_POST['token'])) {
        if (!hash_equals($_SESSION['token'], $_POST['token'])) {
            $error = "Server Timeout: Page Expired";
            unset($_POST);
            header("Location: ./registration?error={$error}");
        }
        foreach (['title', 'content'] as $value) {
            if (empty($_POST[$value])) {
                $errors[$value] = "{$value} is required.";
            }
        }
        if (!in_array($_POST['title'], ['Information', 'Complaint', 'Request'])) {
            $errors['title'] = "Invalid Title";
        }
        if (preg_match("/.{4,15}/", $_POST['content']) === 0) {
            $errors['content'] = "COntent must be between 4 & 15 Characters.";
        }
        if (count($errors) === 0) {
            $title = mysqli_real_escape_string($db_handle->connectDB(), $_POST['title']);
            $content = mysqli_real_escape_string($db_handle->connectDB(), $_POST['content']);
            //check if message id is used already
            $token = message_id_gen();
            $query = "SELECT * FROM messages WHERE id = '{$token}'";
            $count = $db_handle->numRows($query);
            while ($count >= 1) {
                $token = message_id_gen();
                $query = "SELECT * FROM messages WHERE id = '{$token}'";
                $count = $db_handle->numRows($query);
            }
            if (count($errors) === 0) {
                $query = "INSERT INTO
              messages
              (id,title,content,created_at,updated_at,read_at,created_by,read_by)
              VALUES
              ('$token','$title','$content',CURRENT_TIMESTAMP,NULL,NULL,'$email',NULL);";
            }
            $result = $db_handle->insertQuery($query);
            if (!empty($result)) {
                $success = "Message Sent Successfully";
                unset($_POST);
                header("Location: ./message?success={$success}");
            } else {
                $error = "Sever Error: problem inserting value into database.";
                unset($_POST);
                header("Location: ./message?error={$error}");
            }
        }
    } else {
        $error = "Server Timeout: Page Expired";
        unset($_POST);
        header("Location: ./message?error={$error}");
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Message</title>
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
                    <form method="POST" action="./message" class="uk-form-stacked">
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
                            <label class="uk-form-label" for="form-stacked-text">Title</label>
                            <div class="uk-inline">
                                <select id="title" name="title" class="uk-input uk-select uk-form-width-large">
                                    <option value="">Choose A Tag</option>
                                    <option value="Information">Information</option>
                                    <option value="Complaint">Complaint</option>
                                    <option value="Request">Request</option>
                                </select>
                            </div>
                            <?php if (isset($errors['title'])) {
                                echo "<span class=\"uk-text-danger\">{$errors['title']}</span>";
                            } ?>
                        </div>
                        <div class="uk-margin">
                            <label class="uk-form-label" for="form-stacked-text">Message Content</label>
                            <div class="uk-inline">
                                <textarea id="content" name="content" class="uk-textarea uk-form-width-large" rows="5" placeholder="your message"></textarea>
                            </div>
                            <?php if (isset($errors['content'])) {
                                echo "<span class=\"uk-text-danger\">{$errors['content']}</span>";
                            } ?>
                        </div>
                        <div class="uk-width-1-1">
                            <button class="uk-button uk-button-primary">Send</button>
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