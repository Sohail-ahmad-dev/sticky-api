<?php
session_start();
require_once 'api_const.php';

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
// original start

function checkout_process($formData)
{
    $data = [];
    $credentials = base64_encode(USERNAME . ":" . PASSWORD);

    $firstname = !empty($formData['first_name']) ? $formData['first_name'] : "test fname";
    $lastname = !empty($formData['last_name']) ? $formData['last_name'] : "last fname";
    $email = $formData['email'];
    $phone = !empty($formData['phone']) ? $formData['phone'] : "923000000000";
    $address = !empty($formData['shipping_address']) ? $formData['shipping_address'] : " test address";
    $city = !empty($formData['shipping_city']) ? $formData['shipping_city'] : "test country";
    $state = !empty($formData['shipping_state']) ? $formData['shipping_state'] : "US";
    $country = !empty($formData['billing_country']) ? $formData['billing_country'] : "United States";
    $cardnumber = $formData['credit_card_number'];
    $expdatem = $formData['credit_card_expiry_month'];
    $expdatey = $formData['credit_card_expiry_year'];
    $expdate = $expdatem . substr($expdatey, -2);
    $cvv = $formData['cvc'];
    $zip = $formData['billing_zip'];
    $qty = "1";
    $cardType = 'visa';

    $billing_model_id = isset($formData['audiobook_check_mobile']) ? billing_model_id_sub : billing_model_id;

    // end validation
    $ip_server = get_client_ip();
    $curl = curl_init();
    $product = array(
        [
            'offer_id' => offer_id,
            'product_id' => product_id,
            'billing_model_id' => $billing_model_id,
            'quantity' => $qty,
            "trial" => ["product_id" => product_id]
        ]
    );

    $params = array(
        'firstName' => $firstname,
        'lastName' => $lastname,
        'billingFirstName' => $firstname,
        'billingLastName' => $lastname,
        'billingAddress1' => $address,
        //'billingAddress2'=> 'FL 7',
        'billingCity' => $city,
        'billingState' => 'CA',
        'billingZip' => $zip,
        'billingCountry' => "US",
        'phone' => $phone,
        'email' => $email,
        'creditCardType' => $cardType,
        'creditCardNumber' => $cardnumber,
        'expirationDate' => $expdate,
        'CVV' => $cvv,
        'shippingId' => '2',
        'tranType' => 'Sale',
        'ipAddress' =>  $ip_server,
        'campaignId' => campaignId,
        'offers' => $product,
        'billingSameAsShipping' => 'YES',
        'shippingAddress1' => $address,
        // 'shippingAddress2'=>'APT 7',
        'shippingCity' => $city,
        'shippingState' => "CA",
        // 'shippingState' => $state,
        'shippingZip' => $zip,
        'shippingCountry' => "US"
        // 'shippingCountry' => $country
    );
    // echo "<pre>";
    // print_r($params);
    // exit;

    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://' . API_HOST . '.sticky.io/api/v1/new_order',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => json_encode($params),
        CURLOPT_HTTPHEADER => array(
            "Content-Type: application/json",
            "Authorization: Basic " . $credentials
        ),
    ));

    $response = curl_exec($curl);
    //print_r($response);exit;
    $result = json_decode($response, true);
    // echo "<pre>";
    // echo "hhhh";
    // print_r($response);
    // echo "<br>";
    // print_r($result);
    // exit;
    $data['response'] = 'false';
    if (!empty($result['response_code']) && ($result['response_code'] == "100" || $result['response_code'] == 100)) {
        $_SESSION['audiobook_check_mobile'] =
            isset($formData['audiobook_check_mobile']) ? billing_model_id_sub : billing_model_id;
        $_SESSION['payment_type'] = 'credits';
        $data['response'] = 'true';
        $_SESSION['order_id'] = $result['order_id'];
    } else {
        $data['error'] = $result['error_message'];
        $_SESSION['respAPI'] = $data;
        header("location: " . BASE_URL . "checkout.php");
    }

    header("location: " . BASE_URL . "upsell.php");


    // echo json_encode($data);
    // exit;
}

// upsell credit card process start
function upsell_process($formData)
{
    $data = [];
    $data['response'] = false;
    $order_id = !empty($_SESSION['order_id']) ? $_SESSION['order_id'] : "";
    // Print the order ID
    $credentials = base64_encode(USERNAME . ":" . PASSWORD);
    $ip_server = get_client_ip();
    $curl = curl_init();

    $billing_model_id = !empty($_SESSION['audiobook_check_mobile']) ? $_SESSION['audiobook_check_mobile'] : "";

    $product = array(
        [
            'offer_id' => offer_id,
            'product_id' => $formData['upsell_id'],
            'billing_model_id' => $billing_model_id,
            'quantity' => "1",
            "trial" => ["product_id" => $formData['upsell_id']]
        ]
    );

    // echo "<pre>";
    // print_r($product);
    // exit;


    $params = array(
        'previousOrderId' => $order_id,
        'shippingId' => '2',
        'tranType' => 'Sale',
        'ipAddress' =>  $ip_server,
        'campaignId' => campaignId,
        'offers' => $product,
        // 'promoCode' =>$coupon,
        // 'AFFID'=>!empty($_SESSION['aff'])?$_SESSION['aff']:""
    );
    // echo "<pre>";
    // print_r($params);
    // exit;
    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://' . API_HOST . '.sticky.io/api/v1/new_upsell',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => json_encode($params),
        CURLOPT_HTTPHEADER => array(
            "Content-Type: application/json",
            "Authorization: Basic " . $credentials
        ),
    ));

    $response = curl_exec($curl);

    $result = json_decode($response, true);
    // echo "<pre>";
    // print_r($result);
    // exit;
    curl_close($curl);

    if (isset($formData['is_step'])) {
        header("Location: " . $formData['redirectTo']);
        exit;
    }
    //  else {
    //     header("Location: " . BASE_URL . $formData['redirectTo']);
    //     exit;
    // }

    if (!empty($result['response_code']) && $result['response_code'] == "100") {
        $_SESSION['order_id'] = $result['order_id'];
        header("Location: " . BASE_URL . $formData['redirectTo']);
    } else {
        // print_r($response);
        // print_r($params);
        $data['error'] = $result['error_message'];
        $_SESSION['respAPI'] = $data;
        header("location: " . BASE_URL . $formData['page']);
    }
    echo json_encode($data);
}

// upsell process end


// paypal checkout process start


function paypal_checkout_process($formData)
{
    $data = [];
    $resp = array();
    $data['response'] = false;
    $credentials = base64_encode(USERNAME . ":" . PASSWORD);
    $ip_server = get_client_ip();
    $curl = curl_init();
    $qty = 1;
    $qty = "1";


    // $product = array( 
    //     [
    //     'offer_id'=> '2',
    //     'product_id'=> '3',
    //     'billing_model_id'=> '3',
    //     'quantity'=> $qty,
    //     'step_num'=>'2']
    // );

    $product = array(
        [
            'offer_id' => '2',
            'product_id' => '2',
            'billing_model_id' => '3',
            'quantity' => 1,
            "trial" => ["product_id" => "2"]

            //'step_num'=>'2'
        ]
    );

    // $params = array(
    //     'shippingId'=> '2',
    //     'tranType'=> 'Sale',
    //     'ipAddress'=>  $ip_server,
    //     'campaignId'=> '3',
    //     'offers' => $product,
    //     'creditCardType'=>'paypal',
    //     'alt_pay_return_url'=>ALT_PAY_RETURN_URL
    // );


    $params = array(
        'shippingId' => '2',
        'tranType' => 'Sale',
        'ipAddress' =>  $ip_server,
        'campaignId' => '3',
        'offers' => $product,
        'creditCardType' => 'paypal',
        'alt_pay_return_url' => ALT_PAY_RETURN_URL
    );

    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://' . API_HOST . '.sticky.io/api/v1/new_order',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => json_encode($params),
        CURLOPT_HTTPHEADER => array(
            "Content-Type: application/json",
            "Authorization: Basic " . $credentials
        ),
    ));

    $response = curl_exec($curl);
    // $info = curl_getinfo($curl);

    // $parts = parse_url($info['url']);
    // parse_str($parts['query'], $query);
    // $order_id =  $query['id'];
    // $_SESSION['order_id'] = $order_id;
    $_SESSION['payment_type'] = 'paypal';
    // saveOrder($order_id);
    echo $response;
    exit;
}
// upsell paypal process start
function paypal_upsell($formData)
{
    $data = [];
    $resp = array();
    $data['response'] = false;
    $credentials = base64_encode(USERNAME . ":" . PASSWORD);
    $ip_server = get_client_ip();
    $curl = curl_init();
    $qty = "1";

    // $product = array( 
    //     [
    //     'offer_id'=> '1',
    //     'product_id'=> '4',
    //     'billing_model_id'=> '2',
    //     'quantity'=> $qty,
    //     'step_num'=>'2'],
    //     [
    //         'offer_id'=> '1',
    //         'product_id'=> '5' ,
    //         'billing_model_id'=> '3',
    //         'quantity'=> $qty,
    //         'step_num'=>'2'
    //         ]
    // );

    $product = array(
        [
            'offer_id' => '2',
            'product_id' => '4',
            'billing_model_id' => '3',
            'quantity' => 1,
            "trial" => ["product_id" => "4"]

            //'step_num'=>'2'
        ]
    );


    $params = array(
        'shippingId' => '2',
        'tranType' => 'Sale',
        'ipAddress' =>  $ip_server,
        'campaignId' => '3',
        'offers' => $product,
        'creditCardType' => 'paypal',
        'alt_pay_return_url' => ALT_PAY_RETURN_URL2
    );
    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://' . API_HOST . '.sticky.io/api/v1/new_order',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => json_encode($params),
        CURLOPT_HTTPHEADER => array(
            "Content-Type: application/json",
            "Authorization: Basic " . $credentials
        ),
    ));

    $response = curl_exec($curl);
    // $info = curl_getinfo($curl);

    // $parts = parse_url($info['url']);
    // parse_str($parts['query'], $query);
    // $order_id =  $query['id'];
    // $_SESSION['order_id'] = $order_id;
    $_SESSION['payment_type'] = 'paypal';
    // saveOrder($order_id);
    echo $response;
    exit;
}


// paypal checkout process End






function get_client_ip()
{
    $ipaddress = '';
    if (isset($_SERVER['HTTP_CLIENT_IP']))
        $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
    else if (isset($_SERVER['HTTP_X_FORWARDED_FOR']))
        $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
    else if (isset($_SERVER['HTTP_X_FORWARDED']))
        $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
    else if (isset($_SERVER['HTTP_FORWARDED_FOR']))
        $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
    else if (isset($_SERVER['HTTP_FORWARDED']))
        $ipaddress = $_SERVER['HTTP_FORWARDED'];
    else if (isset($_SERVER['REMOTE_ADDR']))
        $ipaddress = $_SERVER['REMOTE_ADDR'];
    else
        $ipaddress = 'UNKNOWN';
    return $ipaddress;
}
