<?php
session_start();
include("./misc.php");
require("./dbcontroller.php");
$db_handle = new DBcontroller();
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

//load total user created jobs counts
$query = "SELECT job_id FROM jobs WHERE ordered_by = '$email' AND (status = 'confirmed' OR status ='completed')";
$count = $db_handle->numRows($query);
$total_jobs = $count;

//load total completed user created jobs counts
$query = "SELECT job_id FROM jobs WHERE ordered_by = '$email' AND status = 'completed'";
$count = $db_handle->numRows($query);
$completed_jobs = $count;

//load total completed user created jobs counts
$query = "SELECT job_id FROM jobs WHERE ordered_by = '$email' AND status = 'confirmed'";
$count = $db_handle->numRows($query);
$pending_jobs = $count;

//load total user message sent counts
$query = "SELECT id FROM messages WHERE created_by = '$email'";
$count = $db_handle->numRows($query);
$total_messages = $count;

//load total user message unseen counts
$query = "SELECT id FROM messages WHERE created_by = '$email' AND read_by is NULL";
$count = $db_handle->numRows($query);
$unseen_messages = $count;

//load total user message read counts
$query = "SELECT id FROM messages WHERE created_by = '$email' AND read_by is NOT NULL";
$count = $db_handle->numRows($query);
$read_messages = $count;
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
	<div class="uk-container uk-padding-small">
		<div class="uk-child-width-1-2@s uk-child-width-1-3@m uk-grid-match" uk-grid>
			<div class="uk-text-center">
				<div class="uk-card uk-card-default uk-card-body  uk-text-center">
					<h3 class="uk-card-title">TOTAL BOOKING</h3>
					<div class="uk-flex uk-flex-center">

						<p class="uk-width-1-3 uk-border-circle  uk-padding-medium count"><?php echo $total_jobs ?></p>
					</div>
				</div>
			</div>
			<div class="uk-text-center">
				<div class="uk-card uk-card-default uk-card-body  uk-text-center">
					<h3 class="uk-card-title">COMPLETED BOOKING</h3>
					<div class="uk-flex uk-flex-center">

						<p class="uk-width-1-3 uk-border-circle  uk-padding-medium count"><?php echo $completed_jobs ?></p>
					</div>
				</div>
			</div>
			<div class="uk-text-center">
				<div class="uk-card uk-card-default uk-card-body  uk-text-center">
					<h3 class="uk-card-title">PENDING BOOKING</h3>
					<div class="uk-flex uk-flex-center">

						<p class="uk-width-1-3 uk-border-circle  uk-padding-medium count"><?php echo $pending_jobs ?></p>
					</div>
				</div>
			</div>
			<div class="uk-text-center">
				<div class="uk-card uk-card-default uk-card-body  uk-text-center">
					<h3 class="uk-card-title">TOTAL MESSAGES</h3>
					<div class="uk-flex uk-flex-center">

						<p class="uk-width-1-3 uk-border-circle  uk-padding-medium count"><?php echo $total_messages ?></p>
					</div>
				</div>
			</div>
			<div class="uk-text-center">
				<div class="uk-card uk-card-default uk-card-body  uk-text-center">
					<h3 class="uk-card-title">UNSEEN MESSAGES</h3>
					<div class="uk-flex uk-flex-center">

						<p class="uk-width-1-3 uk-border-circle  uk-padding-medium count"><?php echo $unseen_messages ?></p>
					</div>
				</div>
			</div>
			<div class="uk-text-center">
				<div class="uk-card uk-card-default uk-card-body  uk-text-center">
					<h3 class="uk-card-title">SEEN MESSAGES</h3>
					<div class="uk-flex uk-flex-center">

						<p class="uk-width-1-3 uk-border-circle  uk-padding-medium count"><?php echo $read_messages ?></p>
					</div>
				</div>
			</div>
		</div>
	</div>
	<!--=============================-->




    <script src="//cdn.jsdelivr.net/npm/uikit@3.3.7/dist/js/uikit.min.js"></script>
	<script src="//cdn.jsdelivr.net/npm/uikit@3.3.7/dist/js/uikit-icons.min.js"></script>
</body>
</html