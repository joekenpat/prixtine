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


//load unseen message
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? $_GET['page'] : 1;
$no_of_records_per_page = 10;
$offset = ($page - 1) * $no_of_records_per_page;
$query = "SELECT * FROM messages WHERE created_by ='$email' AND read_at is NULL;";
$count = $db_handle->numRows($query);
$total_rows = $count;
$total_pages = ceil($total_rows / $no_of_records_per_page);

$query = "SELECT * FROM messages WHERE created_by ='$email' AND read_at is NULL LIMIT $offset, $no_of_records_per_page";
$result = $db_handle->runQuery($query);
$unseen_messages = $result;
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta http-equiv="X-UA-Compatible" content="ie=edge" />
  <title>Unseen Messages</title>
  <link rel="stylesheet" href="//cdn.jsdelivr.net/npm/uikit@3.3.7/dist/css/uikit.min.css" />
  <link rel="stylesheet" href="app.css" />
  <link rel="stylesheet" href="//cdn.materialdesignicons.com/5.0.45/css/materialdesignicons.min.css" />
</head>

<body>

  <?php sideBarType($role); ?>
  <?php require_once('include/header.php'); ?>
  <!--=======================-->
  <div class="uk-container">
    <div class="uk-card uk-card-small  uk-card-default">
      <div class="uk-card-header">
        <h3 class="uk-card-title">Unseen Messages
        </h3>
      </div>
      <div class="uk-card-body uk-padding-remove">
        <table class="uk-table uk-table-middle uk-table-responsive uk-table-divider uk-table-hover">
          <thead>
            <tr>
              <th>#</th>
              <th>tag</th>
              <th>Message</th>
              <th>Time</th>
            </tr>
          </thead>
          <tbody>
            <?php
            if (!empty($unseen_messages)) {
              foreach ($unseen_messages as $key => $message) {
                $iter = $key + 1;
                if($message['title'] == 'Information'){
                  $tag ="<span class='uk-label uk-label-success'>{$message['title']}</span>";
                }elseif($message['title'] == 'Complaint'){
                  $tag ="<span class='uk-label uk-label-danger'>{$message['title']}</span>";
                }elseif($message['title'] == 'Request'){
                  $tag ="<span class='uk-label uk-label-warning'>{$message['title']}</span>";
                }
                echo "
						<tr>
							<td>{$iter}</td>
							<td>{$tag}</td>
							<td>{$message['content']}</td>
              <td>{$message['created_at']}</td>
							<tr>";
              }
            } else {
              echo "
						<tr>
							<td class='uk-text-center' colspan='4'>No Data to Display</td>
							<tr>";
            }
            ?>
          </tbody>
        </table>
      </div>
      <div class="uk-card-footer">
				<ul class="uk-pagination uk-flex-center">
					<li><a href="?page=1">First</a></li>
					<li class="<?php if ($page <= 1) {
												echo 'uk-disabled';
											} ?>">
						<a href="<?php if ($page <= 1) {
												echo '#';
											} else {
												echo "?page=" . ($page - 1);
											} ?>">Prev</a>
					</li>
					<li class="<?php if ($page >= $total_pages) {
												echo 'uk-disabled';
											} ?>">
						<a href="<?php if ($page >= $total_pages) {
												echo '#';
											} else {
												echo "?page=" . ($page + 1);
											} ?>">Next</a>
					</li>
					<li><a href="?page=<?php echo $total_pages; ?>">Last</a></li>
				</ul>
			</div>
    </div>
  </div>
  <!--=======================-->
  <script src="//cdn.jsdelivr.net/npm/uikit@3.3.7/dist/js/uikit.min.js"></script>
  <script src="//cdn.jsdelivr.net/npm/uikit@3.3.7/dist/js/uikit-icons.min.js"></script>
</body>

</html>