<?php

use Yabacon\Paystack;
use Yabacon\Paystack\Exception\ApiException;

include("./misc.php");
require("./dbcontroller.php");
$db_handle = new DBcontroller();

$reference = isset($_GET['reference']) ? $_GET['reference'] : '';
if (!$reference) {
  die('No reference supplied');
}

// initiate the Library's Paystack Object
$paystack = new Paystack('sk_test_a3325c11078d7c0c2e24bf3eed312413ea8b4e1f');
try {
  // verify using the library
  $tranx = $paystack->transaction->verify([
    'reference' => $reference, // unique to transactions
  ]);
} catch (ApiException $e) {
  print_r($e->getResponseObject());
  die($e->getMessage());
}

if ('success' === $tranx->data->status) {
  // transaction was successful...
  // please check other things like whether you already gave value for this ref
  // if the email matches the customer who owns the product etc
  // Give value
  $query = "SELECT status FROM jobs WHERE job_id ='$reference'";
  $result = $db_handle->runQuery($query);
  if (!empty($result)) {
    if ($result[0]['status'] == 'pending') {
      $paid_at = new DateTime($tranx->data->paid_at);
      $c_paid_at = $paid_at->format('Y-m-d H:i:s');
      $query = "UPDATE jobs SET status = 'confirmed', confirmed_at = '{$c_paid_at}' WHERE job_id ='$reference'";
      try {
        $result = $db_handle->updateQuery($query);
        header("Location: ./transaction_complete");
      } catch (\Exception $e) {
        print_r($e->getMessage());
        die($e->getMessage());
      }
    }
  }
}elseif('failed' === $tranx->data->status){
  header("Location: ./transaction_failed");
}
