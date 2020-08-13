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

if($role != 'admin'){
	header("Location: ./user_dashboard");
}

//load total user created jobs counts
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? $_GET['page'] : 1;
$no_of_records_per_page = 10;
$offset = ($page - 1) * $no_of_records_per_page;
$query = "SELECT * FROM users";
$count = $db_handle->numRows($query);
$total_rows = $count;
$total_pages = ceil($total_rows / $no_of_records_per_page);

$query = "SELECT * FROM users LIMIT $offset, $no_of_records_per_page";
$result = $db_handle->runQuery($query);
$total_users = $result;
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta http-equiv="X-UA-Compatible" content="ie=edge" />
  <title>Total Users</title>
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
        <h3 class="uk-card-title">Total Users</h3>
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
      <div class="uk-card-body uk-padding-remove">
        <table class="uk-table uk-table-middle uk-table-responsive uk-table-divider uk-table-hover">
          <thead>
            <tr>
              <th>#</th>
              <th>User Email</th>
              <th>User Phone</th>
              <th>Joined</th>
              <th>Location</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <?php
            if (!empty($total_users)) {
              foreach ($total_users as $key => $user) {
                $iter = $key + 1;
                echo "
						<tr>
							<td>{$iter}</td>
							<td>{$user['email']}</td>
							<td>{$user['phone']}</td>
							<td>{$user['created_at']}</td>
              <td>{$user['address']}</td>
              <td>
              <div class=\"uk-button-group\">
              <a onclick=\"confirm_action(event, this)\" text class=\"uk-button uk-button-small uk-button-danger\"
                title=\"Delete User\" href=\"./delete_user?email={$user['email']}&token={$token}&redirect=admin_total_users\"
                key=\"del_btn_{$user['token']}\" id=\"del_btn_{$user['token']}\" uk-icon=\"icon: trash\">
              </a>
            </div>
              </td>
							<tr>";
              }
            } else {
              echo "
						<tr>
							<td class='uk-text-center' colspan='6'>No Data to Display</td>
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
  <script>
    function confirm_action(e, t) {
      e.preventDefault();
      e.target.blur();
      var self_link = t.getAttribute('href')
      var self_action = t.getAttribute('title')
      UIkit.modal.confirm(`Do you want to ${self_action}!`).then(function() {
        e.isDefaultPrevented = function() {
          return false;
        }
        // retrigger with the exactly same event data
        location.href = self_link
      }, function() {});
    }
  </script>
</body>

</html>