# rocketr IPN  PHP
This is an example class of how you can receive and validate notifications from rocketr.

rocketr is a stress free payment gateway that allows you to sell digital files and programs with ease. You can accept PayPal, Bitcoin, and more through our secure checkout process--all from one dashboard.

You can learn more [here](http://rocketr.net)

# IPN Notification Payload
After you enable IPN Notifications for your products and have successfully setup IPN Notifications on your website, you will receive a post request every time a buyer has purchased a product and paid for the product in its entirety. 

To verify the integrity of the payload, we will send an HMAC signature over a HTTP header called "IPN_HASH". The HMAC signature will be a signed json encoded POST object with your IPN secret. You can see how to verify the signature in the `example_rocketr_ipn.php` file in this repository.

We will send the following information in the post request as a JSON Object:
 - order_id
   * This is a unique string of 12 characters that identifies each order.
 - product_title
   * This the product title for which the IPN is being sent.
 - product_id
   * This the product id for which the IPN is being sent.
 - buyer_email
   * The email address of the buyer.
 - buyer_ip
   * The IP Address of the buyer (note this can be in IPv4 or IPv6 format)
 - payment_method
   * This is a number that represents the payment method used for the purchase:
      * 0 = Paypal
      * 1 = Bitcoin
      * 2 = Ethereum
      * 3 = Perfect Money
      * 4 = Stripe 
 - invoice_amount_usd
   * This is a decimal value of the amoun the buyer was invoiced (and the amount the buyer paid in USD)
 - quantity
   * The number of items the buyer purchased.
 - purchased_at
   * A MySQL DateTime object signifying the time the invoice was generated (formated as YYYY-MM-DD HH:MM:SS)
 - txn_id
   * This is a string that represents a transaction id for the payment method used to purchase the product. For example, this can refer to payapl's transaction id, btc/eth's blockchain ID, or perfect money id


# How to Setup IPN Notifications on Your Website
Setting  up IPN notifications is extremely easy. Take a look at the `example_rocketr_ipn.php` file in this repository and edit it to suit your needs.

# How to Enable IPN Notifications for Your Products

1. In order to receive instant payment notifications, you must first add an IPN secret by visiting the account settings page which can be found [here](https://rocketr.net/seller/settings/account). 
![alt text](http://i.imgur.com/4Bh9SWD.png "Screenshot")
2. Afterwards, you must add a product and ensure you add the the url you wish to receive IPN notifications as show here
![alt text](http://i.imgur.com/xEQrmNf.png "Screenshot")
3. That's it. It's that simple. Now, whenever someone purchases your product, you will receive a post request (as show above) containing order information.
 
# Firewall Considerations
In order to ensure that you consistently received Instant Payment Notifications from rocketr, please make sure that you have whitelisted `104.27.160.104` in yoru firewall. 
