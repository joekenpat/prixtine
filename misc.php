<?php
if(empty($_SESSION['token'])){
    $_SESSION['token'] = bin2hex(random_bytes(32));
}
$token = $_SESSION['token'];
function ipAddress()
{
    //whether ip is from share internet
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ipAddress = $_SERVER['HTTP_CLIENT_IP'];
    }
    //whether is from proxy
    elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ipAddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
    }
    //whether is from remote address
    else {
        $ipAddress = $_SERVER['REMOTE_ADDR'];
    }
    return $ipAddress;
}

$user_ipAddress = ipAddress();

function url_origin($s, $use_forwarded_host = false)
{
    $ssl      = (!empty($s['HTTPS']) && $s['HTTPS'] == 'on');
    $sp       = strtolower($s['SERVER_PROTOCOL']);
    $protocol = substr($sp, 0, strpos($sp, '/')) . (($ssl) ? 's' : '');
    $port     = $s['SERVER_PORT'];
    $port     = ((!$ssl && $port == '80') || ($ssl && $port == '443')) ? '' : ':' . $port;
    $host     = ($use_forwarded_host && isset($s['HTTP_X_FORWARDED_HOST'])) ? $s['HTTP_X_FORWARDED_HOST'] : (isset($s['HTTP_HOST']) ? $s['HTTP_HOST'] : null);
    $host     = isset($host) ? $host : $s['SERVER_NAME'] . $port;
    return $protocol . '://' . $host;
}

function full_url($s, $use_forwarded_host = false)
{
    return url_origin($s, $use_forwarded_host) . $s['REQUEST_URI'];
}

function token_code_gen()
{
    return strtoupper(substr(str_shuffle(md5(time())), 0, 8));
}
function job_id_gen()
{
    return strtoupper(substr(str_shuffle(md5(time())), 0, 10));
}
function message_id_gen()
{
    return strtoupper(substr(str_shuffle(md5(time())), 0, 11));
}
function greeting_msg()
{
    $Hour = date('G');
    $morning_greeting = array('Morning!', 'Good Morning!', 'Beautiful Morning!', 'What new day!');
    $afternoon_greeting = array('Afternoon!', 'Good Afternoon!', 'Wonderful Afternoon!');
    $evening_greeting = array('Evening!', 'Good Evening!', 'Lovely Evening!', 'Peacefull Evening!');
    if ($Hour >= 5 && $Hour <= 11) {
        return $morning_greeting[random_int(0, count($morning_greeting) - 1)];
    } else if ($Hour >= 12 && $Hour <= 18) {
        return $afternoon_greeting[random_int(0, count($afternoon_greeting) - 1)];
    } else if ($Hour >= 19 || $Hour <= 4) {
        return $evening_greeting[random_int(0, count($evening_greeting) - 1)];
    }
}
require './vendor/autoload.php';
function sideBarType($user_role ='user'){
    if($user_role == 'user')
        return require_once('include/user_sidebar.html');
    elseif($user_role == 'admin')
        return require_once('include/admin_sidebar.html');

}