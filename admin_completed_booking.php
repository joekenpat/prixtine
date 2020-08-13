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
	header("Location: ./user_completed_booking");
}

//load total completed user created jobs counts
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? $_GET['page'] : 1;
$no_of_records_per_page = 10;
$offset = ($page - 1) * $no_of_records_per_page;
$query = "SELECT * FROM (SELECT j.`#`, j.type, j.size,j.duration, j.price,j.ordered_by, j.completed_at, j.status, j.location,j.job_id, u.`#` as user_id, u.email, u.phone
FROM jobs AS j
JOIN users AS u ON j.ordered_by = u.email
ORDER BY j.completed_at ASC) AS a 
WHERE status = 'completed'
GROUP BY `#`;";
$count = $db_handle->numRows($query);
$total_rows = $count;
$total_pages = ceil($total_rows / $no_of_records_per_page);

$query = "SELECT * FROM (SELECT j.`#`, j.type, j.size,j.duration, j.price,j.ordered_by, j.completed_at, j.status, j.location,j.job_id, u.`#` as user_id, u.email, u.phone
FROM jobs AS j
JOIN users AS u ON j.ordered_by = u.email
ORDER BY j.completed_at ASC) AS a 
WHERE status = 'completed'
GROUP BY `#`LIMIT $offset, $no_of_records_per_page";
$result = $db_handle->runQuery($query);
$completed_jobs = $result;

?>
<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<meta http-equiv="X-UA-Compatible" content="ie=edge" />
	<title>Completed Bookings</title>
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
				<h3 class="uk-card-title">Completed Bookings</h3>
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
							<th>Type</th>
							<th>Rooms</th>
							<th>Duration</th>
							<th>Price</th>
							<th>Completed</th>
							<th>User Phone</th>
							<th>Location</th>
							<th>Action</th>

						</tr>
					</thead>
					<tbody>
						<?php
						if (!empty($completed_jobs)) {
							foreach ($completed_jobs as $key => $job) {
								$iter = $key + 1;
								$monied = number_format($job['price'], 2);
								echo "
						<tr>
							<td>{$iter}</td>
							<td>{$job['type']}</td>
							<td>{$job['size']}</td>
							<td>{$job['duration']}</td>
							<td>{$monied}</td>
							<td>{$job['completed_at']}</td>
							<td>{$job['phone']}</td>
							<td>{$job['location']}</td>
							<td>
              <div class=\"uk-button-group\">
              <a onclick=\"confirm_action(event, this)\" text class=\"uk-button uk-button-small uk-button-danger\"
                title=\"Delete Booking\" href=\"./delete_booking?job_id={$job['job_id']}&token={$token}&redirect=admin_completed_booking\"
                key=\"del_btn_{$job['job_id']}\" id=\"del_btn_{$job['job_id']}\" uk-icon=\"icon: trash\">
              </a>
            </div>
              </td>
							<tr>";
							}
						} else {
							echo "
						<tr>
							<td class='uk-text-center' colspan='9'>No Data to Display</td>
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