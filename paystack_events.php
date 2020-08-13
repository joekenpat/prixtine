<?php

use Yabacon\Paystack;
use Yabacon\Paystack\Event;
use Yabacon\Paystack\Exception\ApiException;

include("./misc.php");
require("./dbcontroller.php");
$db_handle = new DBcontroller();

$event = Event::capture();
http_response_code(200);

/* It is a important to log all events received. Add code *
     * here to log the signature and body to db or file       */
openlog('MyPaystackEvents', LOG_CONS | LOG_NDELAY | LOG_PID, LOG_USER | LOG_PERROR);
syslog(LOG_INFO, $event->raw);
closelog();

/* Verify that the signature matches one of your keys*/
$my_keys = [
    'test' => 'sk_test_a3325c11078d7c0c2e24bf3eed312413ea8b4e1f',
];
$owner = $event->discoverOwner($my_keys);
if (!$owner) {
    // None of the keys matched the event's signature
    die();
}

// Do something with $event->obj
// Give value to your customer but don't give any output
// Remember that this is a call from Paystack's servers and
// Your customer is not seeing the response here at all
switch ($event->obj->event) {
        // charge.success
    case 'charge.success':
        if ('success' === $event->obj->data->status) {
            // TIP: you may still verify the transaction
            // via an API call before giving value.
            // initiate the Library's Paystack Object
            $paystack = new Paystack('sk_test_a3325c11078d7c0c2e24bf3eed312413ea8b4e1f');
            try {
                // verify using the library
                $tranx = $paystack->transaction->verify([
                    'reference' => $event->obj->data->reference, // unique to transactions
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
                        $paid_at = new DateTime($event->obj->data->paid_at);
                        $c_paid_at = $paid_at->format('Y-m-d H:i:s');
                        $query = "UPDATE jobs SET status = 'confirmed', confirmed_at = '{$c_paid_at}' WHERE job_id ='{$event->obj->data->reference}'";
                        try {
                            $result = $db_handle->updateQuery($query);
                            header("Location: ./transaction_complete");
                        } catch (\Exception $e) {
                            print_r($e->getMessage());
                            die($e->getMessage());
                        }
                    } else {
                        die();
                    }
                } else {
                    die();
                }
            }
            break;
        } elseif ('failed' === $tranx->data->status) {
            header("Location: ./transaction_failed");
        }
}
