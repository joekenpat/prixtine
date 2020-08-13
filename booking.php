<?php
session_start();
use Yabacon\Paystack;
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

// load user data
$result = $db_handle->runQuery($query);
$phone = $result[0]['phone'];
$address = $result[0]['address'];
$role = $result[0]['role'];
$created_at = $result[0]['created_at'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    function validateDate($date, $format = 'Y-m-d H:i:s')
    {
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) === $date;
    }
    $errors = array();
    if (!empty($_POST['token'])) {
        if (!hash_equals($_SESSION['token'], $_POST['token'])) {
            $error = "Server Timeout: Page Expired";
            unset($_POST);
            header("Location: ./registration?error={$error}");
        }
        foreach (['booking_location', 'booking_datetime', 'booking_type', 'booking_size', 'booking_duration', 'booking_price'] as $value) {
            if (empty($_POST[$value])) {
                $_POST[$value] = preg_replace('/\s+/', '', $_POST[$value]);
                $errors[$value] = "{$value} is required.";
            }
        }
        if (preg_match("/.{4,15}/", $_POST['booking_location']) === 0) {
            $errors['booking_location'] = "Location must be between 4 & 15 Characters.";
        }
        if (!validateDate($_POST['booking_datetime'])) {
            $errors['booking_datetime'] = "Booking Date and time be in this format: YYYY-M-D H:I:S";
        }
        if (!in_array($_POST['booking_size'], [1, 2, 3, 4, 5])) {
            $errors['booking_size'] = "Select a valid room size";
        }

        if (!in_array($_POST['booking_duration'], ["once", "bi-monthly", "weekly/monthly"])) {
            $errors['booking_duration'] = "Select a valid duration";
        }
        if (!filter_var($_POST['booking_price'], FILTER_VALIDATE_INT) && !filter_var($_POST['booking_price'], FILTER_VALIDATE_FLOAT)) {
            $errors["booking_price"] = "Invalid price type";
        }
        if ($_POST['booking_price'] < 5000 || $_POST['booking_price'] < 0 ) {
            $errors["booking_price"] = "Invalid price value";
        }

        if (count($errors) === 0) {
            $booking_location = mysqli_real_escape_string($db_handle->connectDB(), $_POST['booking_location']);
            $booking_type = mysqli_real_escape_string($db_handle->connectDB(), $_POST['booking_type']);
            $booking_size = mysqli_real_escape_string($db_handle->connectDB(), $_POST['booking_size']);
            $booking_duration = mysqli_real_escape_string($db_handle->connectDB(), $_POST['booking_duration']);
            $booking_price = mysqli_real_escape_string($db_handle->connectDB(), $_POST['booking_price']);
            $paystack = new Paystack("sk_test_a3325c11078d7c0c2e24bf3eed312413ea8b4e1f");
            $token = token_code_gen();
            //check if job id is used already
            $booking_job_id = job_id_gen();
            $query = "SELECT * FROM jobs WHERE job_id = '{$booking_job_id}'";
            $count = $db_handle->numRows($query);
            while ($count > 0) {
                $booking_job_id = job_id_gen();
                $query = "SELECT * FROM jobs WHERE job_id = '{$booking_job_id}'";
                $count = $db_handle->numRows($query);
            }
            if (count($errors) === 0) {
                $query = "INSERT INTO
              jobs
              (type,size,duration,price,location,status,ordered_by,created_at,updated_at,confirmed_at,job_id)
              VALUES
              ('$booking_type','$booking_size','$booking_duration','$booking_price','$address','pending','$email',CURRENT_TIMESTAMP,NULL,NULL,'$booking_job_id');";
            }
            $result = $db_handle->insertQuery($query);
            if (!empty($result)) {
                try {
                    $tranx = $paystack->transaction->initialize([
                        "reference" => $booking_job_id,
                        "amount" => ($booking_price * 100),
                        "email" => $email,
                    ]);
                } catch (\Yabacon\Paystack\Exception\ApiException $e) {
                    print_r($e->getResponseObject());
                    die($e->getMessage());
                }

                try{
                $query ="INSERT INTO paystack_refs 
                (job_id, reference, user, created_at)
                VALUES
                ('$booking_job_id','{$tranx->data->reference}','$email',CURRENT_TIMESTAMP);";
                $result = $db_handle->insertQuery($query);
                }catch(\Exception $e){
                    print_r($e->getMessage());
                }

                // redirect to page so User can pay
                header('Location: ' . $tranx->data->authorization_url);
            } else {
                $error = "Sever Error: problem inserting value into database.";
                unset($_POST);
                header("Location: ./Booking?error={$error}");
            }
        }
    } else {
        $error = "Server Timeout: Page Expired";
        unset($_POST);
        header("Location: ./booking?error={$error}");
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
    <link rel="stylesheet" href="css/tail.datetime-default-blue.min.css">
</head>

<body>


    <?php sideBarType($role); ?>
    <?php require_once('include/header.php'); ?>
    <!--=============================-->
    <div id="timepicker_modal" class="uk-flex-top" uk-modal>
        <div id="timepicker_holder" class="uk-modal-dialog uk-modal-body uk-margin-auto-vertical">
            <button class="uk-modal-close-default" type="button" uk-close></button>
        </div>
    </div>
    <div class="uk-container uk-padding-small content">
        <div uk-grid>
            <div class="uk-width-1-1 ">
                <div class="uk-card uk-card-default uk-card-body">
                    <form method="POST" action="./booking" class="uk-form-stacked">
                        <input type="hidden" id="token" name="token" value="<?php echo $_SESSION['token']; ?>">
                        <div class="uk-margin">
                            <label class="uk-form-label" for="form-stacked-text">Location</label>
                            <div class="uk-inline">
                                <textarea id="booking_location" name="booking_location" class="uk-textarea  uk-form-width-large"><?php echo $address; ?></textarea>
                            </div>
                                <?php if (isset($errors['booking_location'])) {
                                echo "<span class=\"uk-text-danger\">{$errors['booking_location']}</span>";
                            } ?>
                        </div>
                        <div class="uk-margin">
                            <label class="uk-form-label" for="form-stacked-text">Date & Time</label>
                            <div class="uk-inline">
                                <span class="uk-form-icon uk-form-icon" uk-icon="icon: calendar"></span>
                                <input id="booking_datetime" name="booking_datetime" onfocus="show_timepicker()" class="uk-input  uk-form-width-large" type="text">
                            </div>
                            <?php if (isset($errors['booking_datetime'])) {
                                echo "<span class=\"uk-text-danger\">{$errors['booking_datetime']}</span>";
                            } ?>
                        </div>
                        <div class="uk-margin">
                            <label class="uk-form-label" for="form-stacked-text">Type</label>
                            <div class="uk-inline">
                                <span class="uk-form-icon uk-form-icon" uk-icon="icon: list"></span>
                                <select id="booking_type" onchange="load_booking_size(this.value)" name="booking_type" class="uk-select uk-input  uk-form-width-large">
                                </select>
                            </div>
                            <?php if (isset($errors['booking_type'])) {
                                echo "<span class=\"uk-text-danger\">{$errors['booking_type']}</span>";
                            } ?>
                        </div>
                        <div class="uk-margin">
                            <label class="uk-form-label" for="form-stacked-text">House Size</label>
                            <div class="uk-inline">
                                <span class="uk-form-icon uk-form-icon" uk-icon="icon: home"></span>
                                <select id="booking_size" onchange="load_booking_duration(this.value)" name="booking_size" class="uk-select uk-input  uk-form-width-large">
                                </select>
                            </div>
                            <?php if (isset($errors['booking_size'])) {
                                echo "<span class=\"uk-text-danger\">{$errors['booking_size']}</span>";
                            } ?>
                        </div>
                        <div class="uk-margin">
                            <label class="uk-form-label" for="form-stacked-text">Duration</label>
                            <div class="uk-inline">
                                <span class="uk-form-icon uk-form-icon" uk-icon="icon: clock"></span>
                                <select id="booking_duration" onchange="load_booking_price(this.value)" name="booking_duration" class="uk-select uk-input  uk-form-width-large">
                                </select>
                            </div>
                            <?php if (isset($errors['booking_duration'])) {
                                echo "<span class=\"uk-text-danger\">{$errors['booking_duration']}</span>";
                            } ?>
                        </div>
                        <div class="uk-margin">
                            <label class="uk-form-label" for="form-stacked-text">Price</label>
                            <div class="uk-inline">
                                <span class="uk-form-icon uk-form-icon" uk-icon="icon: cart"></span>
                                <input readonly="readonly" id="booking_price" type="text" name="booking_price" class="uk-input uk-form-width-large uk-disabled">
                            </div>
                            <?php if (isset($errors['booking_price'])) {
                                echo "<span class=\"uk-text-danger\">{$errors['booking_price']}</span>";
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
        <script src="js/tail.datetime-full.min.js"></script>
        <script>
            function removeOptions(selectElement) {
                var i, L = selectElement.options.length - 1;
                for (i = L; i >= 0; i--) {
                    selectElement.remove(i);
                }
            }
            var booking_data = {
                "regular": {
                    "1": {
                        "once": 5590.00,
                        "bi-monthly": 8600.00,
                        "weekly/monthly": 17200.00
                    },
                    "2": {
                        "once": 6987.50,
                        "bi-monthly": 9406.25,
                        "weekly/monthly": 18812.50
                    },
                    "3": {
                        "once": 8385.00,
                        "bi-monthly": 9943.75,
                        "weekly/monthly": 19887.50
                    },
                    "4": {
                        "once": 11180.00,
                        "bi-monthly": 15910.00,
                        "weekly/monthly": 31820.00
                    },
                    "5": {
                        "once": 13975.00,
                        "bi-monthly": 19887.50,
                        "weekly/monthly": 39775.00
                    }
                },
                "deep": {
                    "1": {
                        "once": 15000.00
                    },
                    "2": {
                        "once": 25000.00
                    },
                    "3": {
                        "once": 35000.00
                    },
                    "4": {
                        "once": 45000.00
                    },
                    "5": {
                        "once": 55000.00
                    }
                },
                "post": {
                    "1": {
                        "once": 25000.00
                    },
                    "2": {
                        "once": 35000.00
                    },
                    "3": {
                        "once": 45000.00
                    },
                    "4": {
                        "once": 55000.00
                    },
                    "5": {
                        "once": 65000.00
                    }
                },
                "fumigation": {
                    "1": {
                        "once": 34937.5
                    },
                    "2": {
                        "once": 34937.5
                    },
                    "3": {
                        "once": 53750.0
                    },
                    "4": {
                        "once": 53750.0
                    },
                    "5": {
                        "once": 53750.0
                    },
                    "6": {
                        "once": 53750.0
                    }
                }
            }
            var booking_type_input = document.getElementById('booking_type');
            var booking_size_input = document.getElementById('booking_size');
            var booking_duration_input = document.getElementById('booking_duration');
            var booking_price_input = document.getElementById('booking_price');

            function load_booking_type() {
                var default_option = document.createElement("option")
                default_option.value = ''
                default_option.text = 'Select Cleaning Type'
                booking_type_input.appendChild(default_option)
                var booking_types = Object.keys(booking_data)
                booking_types.map(function(types) {
                    var option = document.createElement("option")
                    option.value = types
                    option.text = types
                    booking_type_input.appendChild(option)
                })
            }

            function load_booking_size(param) {
                removeOptions(booking_size_input)
                removeOptions(booking_duration_input)
                booking_price_input.value = 0
                var default_option = document.createElement("option")
                default_option.value = ''
                default_option.text = 'Select Apartment Size'
                booking_size_input.appendChild(default_option)
                var booking_sizes = Object.keys(booking_data[`${param}`])
                booking_sizes.map(function(size) {
                    var option = document.createElement("option")
                    option.value = size
                    option.text = `${size} Bedroom`
                    booking_size_input.appendChild(option)
                })
            }

            function load_booking_duration(param) {
                removeOptions(booking_duration_input)
                booking_price_input.value = 0
                var default_option = document.createElement("option")
                default_option.value = ''
                default_option.text = 'Select Duration'
                booking_duration_input.appendChild(default_option)
                var booking_durations = Object.keys(booking_data[booking_type_input.value][`${param}`])
                booking_durations.map(function(duration) {
                    var option = document.createElement("option")
                    option.value = duration
                    option.text = duration
                    booking_duration_input.appendChild(option)
                })
            }

            function load_booking_price(param) {
                var booking_price = booking_data[booking_type_input.value][booking_size_input.value][`${param}`]
                booking_price_input.value = booking_price
            }

            function show_timepicker() {
                tail.DateTime('#booking_datetime', {
                    position: "#timepicker_holder",
                    startOpen: true,
                    stayOpen: true,
                    time12h: true,
                })
                UIkit.modal('#timepicker_modal').show()
            }
            document.addEventListener('DOMContentLoaded', function() {
                load_booking_type()
            })
        </script>
</body>

</html