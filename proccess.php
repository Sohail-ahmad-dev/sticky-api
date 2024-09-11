<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
// 4111111111111111
require_once 'insert.php';

if (isset($_POST['action']) && $_POST['action'] == 'create_order') {
  // echo "<pre>";
  // print_r($_POST);
  // print_r($_SESSION['respAPI']);
  // exit;
  checkout_process($_POST);
}

if (isset($_POST['action']) && $_POST['action'] == 'create_paypal_order') {
  paypal_checkout_process($_POST);
}


if (isset($_POST['action']) && $_POST['action'] == 'create_upsell_order') {
  upsell_process($_POST);
}



if (isset($_GET['action']) && $_GET['action'] == 'create_paypal_upsell') {
  paypal_upsell($_GET);
}
