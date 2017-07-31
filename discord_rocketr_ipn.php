<?php
/**
 * You can use this script if you want to get notifications on your discord server whenever a purchase is made.
 * 
 * To setup, please follow the steps here: https://rocketr.net/blog/knowledgebase/get-order-notifications-on-discord/
 * 
 * E-mail support@rocketr.net with any questions or concerns
 */ 


$IPN_SECRET = 'PASTE_IPN_SECRET_HERE'; /* Please enter your IPN secret here*/
$DISCORD_URL = 'PASTE_DISCORD_URL_HERE'; /* Please enter your discord URL here*/



abstract class rocketrPaymentMethods
{
    const PAYPAL = 0;
    const BITCOIN = 1;
    const ETHEREUM = 2;
    const PERFECT_MONEY = 3;
    const STRIPE = 4;
    
    public static function getName($id) {
		$class = new ReflectionClass(get_class($this));
	    $name = array_search($id, $class->getConstants(), TRUE);   
	    return $name;
    }
}

abstract class rocketrOrderStatus {
    const TIMED_OUT = -1; //This means the buyer did not pay
    const NEW_ORDER = 0; //Order was just created, the buyer may or may not pay
    const WAITING_FOR_PAYMENT = 1; //This is exclusive for cryptocurrency payments, this means we are waiting for confirmations
    const ERROR_PARTIAL_PAYMENT_RECEIVED = 2; //the buyer only paid a partial amount
    const FULL_PAYMENT_RECEIVED = 3; //this order status signifies that the product delivery failed (e.g. b/c the buyers email was incorrect or out of stock)
    const PRODUCT_DELIVERED = 4; // AKA success. This signifies product email delivery
    const REFUNDED = 5; //The order was refunded
        
    const UNKNOWN_ERROR = 6;
    
    const PAYPAL_PENDING = 8;
    const PAYPAL_OTHER = 9; //if a paypal dispute is favored to the seller, this is the order status.
    const PAYPAL_REVERSED = 10; //buyer disputed via paypal
    
    const STRIPE_AUTO_REFUND = 20;
    const STRIPE_DECLINED = 21;
    const STRIPE_DISPUTED = 22;
    
}

if(!isset($_POST) || sizeof($_POST) === 0 || !isset($_SERVER['HTTP_IPN_HASH'])) {    
    http_response_code(400);
    die('Received Invalid IPN ');
}


$hmac = hash_hmac("sha512", json_encode($_POST), trim($IPN_SECRET));
if ($hmac != $_SERVER['HTTP_IPN_HASH']) { 
    http_response_code(401);
    die('IPN Hash does not match'); 
}

$order_id = $_POST['order_id'];
$product_title = $_POST['product_title'];
$product_id = $_POST['product_id'];
$buyer_email = $_POST['buyer_email'];
$buyer_ip = $_POST['buyer_ip'];
$payment_method = intval($_POST['payment_method']);
$invoice_amount_usd = floatval($_POST['invoice_amount_usd']);
$quantity = intval($_POST['quantity']);
$purchased_at = $_POST['purchased_at'];
$txn_id = $_POST['txn_id']; //note this can represent different things, paypal's transaction id, btc/eth blockchain txid, perfect momey id etc
$status = intval($_POST['status']);
$custom_fields = json_decode($_POST['custom_fields']); //this will be an array with the keys as the name of the custom_field and the value as the user input.

/**
 * You can process the IPN below.
 * For example:
 *  if($status === rocketrOrderStatus::PRODUCT_DELIVERED) {
 *  	if($payment_method === rocketrPaymentMethods::PAYPAL) {
 *      	sendBuyerEmail('Thanks for paying with Paypal');
 *  	}
 *  } else if($status === rocketrOrderStatus::PAYPAL_REVERSED) {
 *  	//E.G Here you can revoke the license.
 *  }
 */

if($status === rocketrOrderStatus::PRODUCT_DELIVERED) {
    $color = 3066993;
    $content = 'New Rocketr Order [Click here to view](https://rocketr.net/seller/orders/' .$order_id . ')';
} else if($status === rocketrOrderStatus::PAYPAL_REVERSED || $status === rocketrOrderStatus::STRIPE_DISPUTED) {
    $color = 12597547;
    $content = 'Rocketr Order Disputed [Click here to view](https://rocketr.net/seller/orders/' .$order_id . ')';
} else if($status === rocketrOrderStatus::REFUNDED) {
    $color = 15965202;
    $content = 'Rocketr Order Refunded [Click here to view](https://rocketr.net/seller/orders/' .$order_id . ')';
} else {
    //ignore other order statuses
    die('ok');
}

$data = [
	'username' => 'Rocketr',
	'avatar_url' => 'https://rocketr.net/assets/rocket_60.png',
	'content' => $content,
	'embeds' => [
		[
			'color' => $color,
			'fields' => [
				[
					'inline' => true,
					'name' => 'Order ID',
					'value' => $order_id
				],
				[
					'inline' => true,
					'name' => 'Buyer E-mail',
					'value' => $buyer_email
				],
				[
					'inline' => true,
					'name' => 'Product',
					'value' => $product_title
				],
				[
					'inline' => true,
					'name' => 'Payment Method',
					'value' => rocketrPaymentMethods::getName($payment_method)
				],
				[
					'inline' => true,
					'name' => 'Quantity',
					'value' => $quantity
				],
				[
					'inline' => true,
					'name' => 'Price',
					'value' => '$' . $invoice_amount_usd
				]
			],
			'footer' => [
				'text' => 'Service by Rocketr',
				'icon_url' => 'https://rocketr.net/assets/rocket_60.png'
			],
		]
	]
];
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $DISCORD_URL);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$result = curl_exec($ch);
curl_close($ch);
?>
