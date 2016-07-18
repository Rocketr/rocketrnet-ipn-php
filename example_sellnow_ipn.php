<?php
abstract class sellnowPaymentMethods
{
    const PAYPAL = 0;
    const BITCOIN = 1;
    const ETHEREUM = 2;
    const PERFECT_MONEY = 3;
    const STRIPE = 3;
}

if(!isset($_POST) || sizeof($_POST) === 0 || !isset($_SERVER['HTTP_IPN_HASH'])) {    
    die('Received Invalid IPN ');
}

$IPN_SECRET = ''; /* Please enter your IPN secret here*/

$hmac = hash_hmac("sha512", json_encode($_POST), trim($IPN_SECRET));
if ($hmac != $_SERVER['HTTP_IPN_HASH']) { 
    die('IPN Hash does not match'); 
}

$order_id = $_POST['order_id'];
$product_title = $_POST['product_title'];
$product_id = intval($_POST['product_id']);
$buyer_email = $_POST['buyer_email'];
$buyer_ip = $_POST['buyer_ip'];
$payment_method = intval($_POST['payment_method']);
$invoice_amount_usd = floatval($_POST['invoice_amount_usd']);
$quantity = intval($_POST['quantity']);
$purchased_at = $_POST['purchased_at'];

/**
 * You can process the IPN below.
 * For example:
 *  if($payment_method === sellnowPaymentMethods::PAYPAL) {
 *      sendBuyerEmail('Thanks for paying with Paypal');
 *  }
 */
 
?>