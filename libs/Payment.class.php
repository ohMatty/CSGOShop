<?php
class Payment {
	public $app;

	public $paypal;
	public $paypal_adaptive;

	public $coinbase;

	public $currency;

	public function __construct(&$app)
	{
		$this->app =& $app;
		$config = $app->config;
		$this->currency = $config->get('paypal.currency');

		require_once './libs/paypal-php-library/PayPal.php';
		require_once './libs/paypal-php-library/Adaptive.php';
		$this->paypal = new angelleye\PayPal\Paypal(array(
			'Sandbox' => $config->get('paypal.mode'),
			'APIUsername' => $config->get('paypal.user'),
			'APIPassword' => $config->get('paypal.pass'),
			'APISignature' => $config->get('paypal.signature'), 
			'APIVersion' => '97.0', 
			'APISubject' => ''
		));

		$this->paypal_adaptive = new angelleye\PayPal\Adaptive(array(
			'Sandbox' => $config->get('paypal.mode'),
			'ApplicationID' => 'APP-80W284485P519543T',
			'APIUsername' => $config->get('paypal.user'),
			'APIPassword' => $config->get('paypal.pass'),
			'APISignature' => $config->get('paypal.signature'), 
			'APIVersion' => '97.0', 
			'APISubject' => ''
		));

		require_once './libs/Coinbase/Coinbase.php';
		$this->coinbase = Coinbase::withApiKey(
			$config->get('coinbase.key'),
			$config->get('coinbase.secret'));

		require_once './libs/Stripe/Stripe.php';
		Stripe::setApiKey($config->get('stripe.secret'));
	}

	// Paypal Express Checkout methods

	// set express checkout
	public function paypal_SEC($order, $return_url, $cancel_url)
	{
		$SECFields = array(
					'token' => '', 								// A timestamped token, the value of which was returned by a previous SetExpressCheckout call.
					'maxamt' => $order->total_taxed, 						// The expected maximum total amount the order will be, including S&H and sales tax.
					'returnurl' => $this->app->config->get('core.url') . $return_url, 							// Required.  URL to which the customer will be returned after returning from PayPal.  2048 char max.
					'cancelurl' => $this->app->config->get('core.url') . $cancel_url, 							// Required.  URL to which the customer will be returned if they cancel payment on PayPal's site.
					'callback' => '', 							// URL to which the callback request from PayPal is sent.  Must start with https:// for production.
					'callbacktimeout' => '', 					// An override for you to request more or less time to be able to process the callback request and response.  Acceptable range for override is 1-6 seconds.  If you specify greater than 6 PayPal will use default value of 3 seconds.
					'callbackversion' => '', 					// The version of the Instant Update API you're using.  The default is the current version.							
					'reqconfirmshipping' => '0', 				// The value 1 indicates that you require that the customer's shipping address is Confirmed with PayPal.  This overrides anything in the account profile.  Possible values are 1 or 0.
					'noshipping' => '1', 						// The value 1 indiciates that on the PayPal pages, no shipping address fields should be displayed.  Maybe 1 or 0.
					'allownote' => '1', 							// The value 1 indiciates that the customer may enter a note to the merchant on the PayPal page during checkout.  The note is returned in the GetExpresscheckoutDetails response and the DoExpressCheckoutPayment response.  Must be 1 or 0.
					'addroverride' => '', 						// The value 1 indiciates that the PayPal pages should display the shipping address set by you in the SetExpressCheckout request, not the shipping address on file with PayPal.  This does not allow the customer to edit the address here.  Must be 1 or 0.
					'localecode' => '', 						// Locale of pages displayed by PayPal during checkout.  Should be a 2 character country code.  You can retrive the country code by passing the country name into the class' GetCountryCode() function.
					'pagestyle' => '', 							// Sets the Custom Payment Page Style for payment pages associated with this button/link.  
					'hdrimg' => '', 							// URL for the image displayed as the header during checkout.  Max size of 750x90.  Should be stored on an https:// server or you'll get a warning message in the browser.
					'hdrbordercolor' => '', 					// Sets the border color around the header of the payment page.  The border is a 2-pixel permiter around the header space.  Default is black.  
					'hdrbackcolor' => '', 						// Sets the background color for the header of the payment page.  Default is white.  
					'payflowcolor' => '', 						// Sets the background color for the payment page.  Default is white.
					'skipdetails' => '', 						// This is a custom field not included in the PayPal documentation.  It's used to specify whether you want to skip the GetExpressCheckoutDetails part of checkout or not.  See PayPal docs for more info.
					'email' => '', 								// Email address of the buyer as entered during checkout.  PayPal uses this value to pre-fill the PayPal sign-in page.  127 char max.
					'solutiontype' => 'Sole', 						// Type of checkout flow.  Must be Sole (express checkout for auctions) or Mark (normal express checkout)
					'landingpage' => 'Billing', 						// Type of PayPal page to display.  Can be Billing or Login.  If billing it shows a full credit card form.  If Login it just shows the login screen.
					'channeltype' => '', 						// Type of channel.  Must be Merchant (non-auction seller) or eBayItem (eBay auction)
					'giropaysuccessurl' => '', 					// The URL on the merchant site to redirect to after a successful giropay payment.  Only use this field if you are using giropay or bank transfer payment methods in Germany.
					'giropaycancelurl' => '', 					// The URL on the merchant site to redirect to after a canceled giropay payment.  Only use this field if you are using giropay or bank transfer methods in Germany.
					'banktxnpendingurl' => '',  				// The URL on the merchant site to transfer to after a bank transfter payment.  Use this field only if you are using giropay or bank transfer methods in Germany.
					'brandname' => 'CSGOShop', 							// A label that overrides the business name in the PayPal account on the PayPal hosted checkout pages.  127 char max.
					'customerservicenumber' => '555-555-5555', 				// Merchant Customer Service number displayed on the PayPal Review page. 16 char max.
					'giftmessageenable' => '0', 					// Enable gift message widget on the PayPal Review page. Allowable values are 0 and 1
					'giftreceiptenable' => '0', 					// Enable gift receipt widget on the PayPal Review page. Allowable values are 0 and 1
					'giftwrapenable' => '0', 					// Enable gift wrap widget on the PayPal Review page.  Allowable values are 0 and 1.
					'giftwrapname' => '', 						// Label for the gift wrap option such as "Box with ribbon".  25 char max.
					'giftwrapamount' => '', 					// Amount charged for gift-wrap service.
					'buyeremailoptionenable' => '0', 			// Enable buyer email opt-in on the PayPal Review page. Allowable values are 0 and 1
					'surveyquestion' => '', 					// Text for the survey question on the PayPal Review page. If the survey question is present, at least 2 survey answer options need to be present.  50 char max.
					'surveyenable' => '0', 						// Enable survey functionality. Allowable values are 0 and 1
					'buyerid' => '', 							// The unique identifier provided by eBay for this buyer. The value may or may not be the same as the username. In the case of eBay, it is different. 255 char max.
					'buyerusername' => '', 						// The user name of the user at the marketplaces site.
					'buyerregistrationdate' => '',  			// Date when the user registered with the marketplace.
					'allowpushfunding' => ''					// Whether the merchant can accept push funding.  0 = Merchant can accept push funding : 1 = Merchant cannot accept push funding.			
				);

			// Basic array of survey choices.  Nothing but the values should go in here.  
			$SurveyChoices = array('Yes', 'No');

			$Payments = array();
			$Payment = array(
							'amt' => $order->total_taxed, 							// Required.  The total cost of the transaction to the customer.  If shipping cost and tax charges are known, include them in this value.  If not, this value should be the current sub-total of the order.
							'currencycode' => $this->currency, 					// A three-character currency code.  Default is USD.
							'itemamt' => $order->total, 						// Required if you specify itemized L_AMT fields. Sum of cost of all items in this order.  
							'shippingamt' => '', 					// Total shipping costs for this order.  If you specify SHIPPINGAMT you mut also specify a value for ITEMAMT.
							'insuranceoptionoffered' => '', 		// If true, the insurance drop-down on the PayPal review page displays the string 'Yes' and the insurance amount.  If true, the total shipping insurance for this order must be a positive number.
							'handlingamt' => '', 					// Total handling costs for this order.  If you specify HANDLINGAMT you mut also specify a value for ITEMAMT.
							'taxamt' => $order->total_taxed - $order->total, 						// Required if you specify itemized L_TAXAMT fields.  Sum of all tax items in this order. 
							'desc' => 'Order #'.$this->app->hashids->encrypt($order->id), 							// Description of items on the order.  127 char max.
							'custom' => '', 						// Free-form field for your own use.  256 char max.
							'invnum' => '',	// Your own invoice or tracking number.  127 char max.
							'notifyurl' => '',  						// URL for receiving Instant Payment Notifications
							'shiptoname' => '', 					// Required if shipping is included.  Person's name associated with this address.  32 char max.
							'shiptostreet' => '', 					// Required if shipping is included.  First street address.  100 char max.
							'shiptostreet2' => '', 					// Second street address.  100 char max.
							'shiptocity' => '', 					// Required if shipping is included.  Name of city.  40 char max.
							'shiptostate' => '', 					// Required if shipping is included.  Name of state or province.  40 char max.
							'shiptozip' => '', 						// Required if shipping is included.  Postal code of shipping address.  20 char max.
							'shiptocountry' => '', 					// Required if shipping is included.  Country code of shipping address.  2 char max.
							'shiptophonenum' => '',  				// Phone number for shipping address.  20 char max.
							'notetext' => '', 						// Note to the merchant.  255 char max.  
							'allowedpaymentmethod' => '', 			// The payment method type.  Specify the value InstantPaymentOnly.
							'paymentaction' => 'Sale', 					// How you want to obtain the payment.  When implementing parallel payments, this field is required and must be set to Order. 
							'paymentrequestid' => '',  				// A unique identifier of the specific payment request, which is required for parallel payments. 
							'sellerpaypalaccountid' => ''			// A unique identifier for the merchant.  For parallel payments, this field is required and must contain the Payer ID or the email address of the merchant.
							);
			
			$orderTable = $order->toTable();
			$descriptions = array_merge($orderTable['listings'], $orderTable['bulk']);

			$PaymentOrderItems = array();
			foreach($descriptions as $idx => $d) {
				$Item = array(
							'name' => $d['listing']->description->name, // Item name. 127 char max.
							'desc' => '', 							// Item description. 127 char max.
							'amt' => $d['listing']->price, 					// Cost of item.
							'number' => $d['listing']->description->id, 	// Item number.  127 char max.
							'qty' => $d['qty'], 								// Item qty on order.  Any positive integer.
							'taxamt' => '', 							// Item sales tax
							'itemurl' => '', 							// URL for the item.
							'itemcategory' => '', 				// One of the following values:  Digital, Physical
							'itemweightvalue' => '', 					// The weight value of the item.
							'itemweightunit' => '', 					// The weight unit of the item.
							'itemheightvalue' => '', 					// The height value of the item.
							'itemheightunit' => '', 					// The height unit of the item.
							'itemwidthvalue' => '', 					// The width value of the item.
							'itemwidthunit' => '', 					// The width unit of the item.
							'itemlengthvalue' => '', 					// The length value of the item.
							'itemlengthunit' => '',  					// The length unit of the item.
							'ebayitemnumber' => '', 					// Auction item number.  
							'ebayitemauctiontxnid' => '', 			// Auction transaction ID number.  
							'ebayitemorderid' => '',  				// Auction order ID number.
							'ebayitemcartid' => ''					// The unique identifier provided by eBay for this order from the buyer. These parameters must be ordered sequentially beginning with 0 (for example L_EBAYITEMCARTID0, L_EBAYITEMCARTID1). Character length: 255 single-byte characters
						);
				array_push($PaymentOrderItems, $Item);				
			}

			$Payment['order_items'] = $PaymentOrderItems;
			array_push($Payments, $Payment);
						
			// For shipping options we create an array of all shipping choices similar to how order items works.
			$ShippingOptions = array();
			$Option = array(
							'l_shippingoptionisdefault' => '', 				// Shipping option.  Required if specifying the Callback URL.  true or false.  Must be only 1 default!
							'l_shippingoptionname' => '', 					// Shipping option name.  Required if specifying the Callback URL.  50 character max.
							'l_shippingoptionlabel' => '', 					// Shipping option label.  Required if specifying the Callback URL.  50 character max.
							'l_shippingoptionamount' => '' 					// Shipping option amount.  Required if specifying the Callback URL.  
							);
			array_push($ShippingOptions, $Option);
					
			$BillingAgreements = array();
			$Item = array(
						  'l_billingtype' => 'MerchantInitiatedBilling', 							// Required.  Type of billing agreement.  For recurring payments it must be RecurringPayments.  You can specify up to ten billing agreements.  For reference transactions, this field must be either:  MerchantInitiatedBilling, or MerchantInitiatedBillingSingleSource
						  'l_billingagreementdescription' => 'Billing Agreement', 			// Required for recurring payments.  Description of goods or services associated with the billing agreement.  
						  'l_paymenttype' => 'Any', 							// Specifies the type of PayPal payment you require for the billing agreement.  Any or IntantOnly
						  'l_billingagreementcustom' => ''					// Custom annotation field for your own use.  256 char max.
						  );
			array_push($BillingAgreements, $Item);

			$PayPalRequest = array(
								   'SECFields' => $SECFields, 
								   'SurveyChoices' => $SurveyChoices, 
								   'BillingAgreements' => $BillingAgreements, 
								   'Payments' => $Payments
								   );

			$result = $this->paypal->SetExpressCheckout($PayPalRequest);
			if($result['ACK'] == 'Success' || $result['ACK'] == 'SuccessWithWarning')
				return $result['REDIRECTURL'];
			else 
				throw new PayPal_CheckoutError(json_encode($result));
	}

	public function paypal_process($token, $payer_id, $order_id)
	{
		$success = false;
		$result = $this->paypal_DECP($token, $payer_id, $order_id);
		$details = $this->paypal_GECD($token);

		if($details['CHECKOUTSTATUS'] == 'PaymentActionCompleted') {
			$success = true;
		}

		if($details['PAYERSTATUS'] != 'verified')
			throw new PayPal_VerificationError;
		else if(!$success)
			throw new PayPal_CheckoutError(json_encode($result));
		return $details;
	}

	// do express checkout procedure
	public function paypal_DECP($token, $payer_id, $order_id)
	{
		$order = Order::find($order_id);

		$DECPFields = array(
							'token' => $token, 								// Required.  A timestamped token, the value of which was returned by a previous SetExpressCheckout call.
							'payerid' => $payer_id, 							// Required.  Unique PayPal customer id of the payer.  Returned by GetExpressCheckoutDetails, or if you used SKIPDETAILS it's returned in the URL back to your RETURNURL.
							'returnfmfdetails' => '1', 					// Flag to indiciate whether you want the results returned by Fraud Management Filters or not.  1 or 0.
							'giftmessage' => '', 						// The gift message entered by the buyer on the PayPal Review page.  150 char max.
							'giftreceiptenable' => '', 					// Pass true if a gift receipt was selected by the buyer on the PayPal Review page. Otherwise pass false.
							'giftwrapname' => '', 						// The gift wrap name only if the gift option on the PayPal Review page was selected by the buyer.
							'giftwrapamount' => '', 					// The amount only if the gift option on the PayPal Review page was selected by the buyer.
							'buyermarketingemail' => '', 				// The buyer email address opted in by the buyer on the PayPal Review page.
							'surveyquestion' => '', 					// The survey question on the PayPal Review page.  50 char max.
							'surveychoiceselected' => '',  				// The survey response selected by the buyer on the PayPal Review page.  15 char max.
							'allowedpaymentmethod' => '', 				// The payment method type. Specify the value InstantPaymentOnly.
							'buttonsource' => '' 						// ID code for use by third-party apps to identify transactions in PayPal. 
						);
								
		$Payments = array();
		$Payment = array(
						'amt' => $order->total_taxed, 							// Required.  The total cost of the transaction to the customer.  If shipping cost and tax charges are known, include them in this value.  If not, this value should be the current sub-total of the order.
						'currencycode' => $this->currency, 					// A three-character currency code.  Default is USD.
						'itemamt' => $order->total, 						// Required if you specify itemized L_AMT fields. Sum of cost of all items in this order.  
						'shippingamt' => '', 					// Total shipping costs for this order.  If you specify SHIPPINGAMT you mut also specify a value for ITEMAMT.
						'insuranceoptionoffered' => '', 		// If true, the insurance drop-down on the PayPal review page displays the string 'Yes' and the insurance amount.  If true, the total shipping insurance for this order must be a positive number.
						'handlingamt' => '', 					// Total handling costs for this order.  If you specify HANDLINGAMT you mut also specify a value for ITEMAMT.
						'taxamt' => $order->total_taxed - $order->total, 						// Required if you specify itemized L_TAXAMT fields.  Sum of all tax items in this order. 
						'desc' => 'Order #'.$this->app->hashids->encrypt($order->id), 							// Description of items on the order.  127 char max.
						'custom' => '', 						// Free-form field for your own use.  256 char max.
						'invnum' => '', 						// Your own invoice or tracking number.  127 char max.
						'notifyurl' => '',  						// URL for receiving Instant Payment Notifications
						'shiptoname' => '', 					// Required if shipping is included.  Person's name associated with this address.  32 char max.
						'shiptostreet' => '', 					// Required if shipping is included.  First street address.  100 char max.
						'shiptostreet2' => '', 					// Second street address.  100 char max.
						'shiptocity' => '', 					// Required if shipping is included.  Name of city.  40 char max.
						'shiptostate' => '', 					// Required if shipping is included.  Name of state or province.  40 char max.
						'shiptozip' => '', 						// Required if shipping is included.  Postal code of shipping address.  20 char max.
						'shiptocountry' => '', 					// Required if shipping is included.  Country code of shipping address.  2 char max.
						'shiptophonenum' => '',  				// Phone number for shipping address.  20 char max.
						'notetext' => '', 						// Note to the merchant.  255 char max.  
						'allowedpaymentmethod' => '', 			// The payment method type.  Specify the value InstantPaymentOnly.
						'paymentaction' => 'Sale', 					// How you want to obtain the payment.  When implementing parallel payments, this field is required and must be set to Order. 
						'paymentrequestid' => '',  				// A unique identifier of the specific payment request, which is required for parallel payments. 
						'sellerpaypalaccountid' => ''			// A unique identifier for the merchant.  For parallel payments, this field is required and must contain the Payer ID or the email address of the merchant.
						);
						
			$orderTable = $order->toTable();
			$descriptions = array_merge($orderTable['listings'], $orderTable['bulk']);

		$PaymentOrderItems = array();
		foreach($descriptions as $idx => $d) {
			$Item = array(
						'name' => $d['listing']->description->name, // Item name. 127 char max.
						'desc' => '', 							// Item description. 127 char max.
						'amt' => $d['listing']->price, 					// Cost of item.
						'number' => $d['listing']->description->id, 	// Item number.  127 char max.
						'qty' => $d['qty'], 								// Item qty on order.  Any positive integer.
						'taxamt' => '', 							// Item sales tax
						'itemurl' => '', 							// URL for the item.
						'itemweightvalue' => '', 					// The weight value of the item.
						'itemweightunit' => '', 					// The weight unit of the item.
						'itemheightvalue' => '', 					// The height value of the item.
						'itemheightunit' => '', 					// The height unit of the item.
						'itemwidthvalue' => '', 					// The width value of the item.
						'itemwidthunit' => '', 					// The width unit of the item.
						'itemlengthvalue' => '', 					// The length value of the item.
						'itemlengthunit' => '',  					// The length unit of the item.
						'ebayitemnumber' => '', 					// Auction item number.  
						'ebayitemauctiontxnid' => '', 			// Auction transaction ID number.  
						'ebayitemorderid' => '',  				// Auction order ID number.
						'ebayitemcartid' => ''					// The unique identifier provided by eBay for this order from the buyer. These parameters must be ordered sequentially beginning with 0 (for example L_EBAYITEMCARTID0, L_EBAYITEMCARTID1). Character length: 255 single-byte characters
						);
			array_push($PaymentOrderItems, $Item);			
		}

		$Payment['order_items'] = $PaymentOrderItems;
		array_push($Payments, $Payment);				

		$PayPalRequest = array(
							   'DECPFields' => $DECPFields, 
							   'Payments' => $Payments
							   );

		$_SESSION['PayPalRequestResult'] = $this->paypal->DoExpressCheckoutPayment($PayPalRequest);
		return $_SESSION['PayPalRequestResult'];
	}

	// get express checkout details
	public function paypal_GECD($token)
	{
		return $this->paypal->GetExpressCheckoutDetails($token);
	}

	// Stripe merchant methods
	public function stripe_charge($order, $token)
	{
		return Stripe_Charge::create(array(
			'amount' => $order->total_taxed * 100,
			'currency' => $this->currency,
			'card' => $token,
			'description' => '',
			'metadata' => array(
				'user_id' => $order->user_id,
				'user_name' => $order->user->name,
				'order_id' => $this->app->hashids->encrypt($order->id)),
			'statement_description' => 'Order #'.$this->app->hashids->encrypt($order->id)));
	}

	public function stripe_retrieve_charge($charge_id)
	{
		return Stripe_Charge::retrieve($charge_id);
	}	

	// Coinbase Merchant Methods

	// create button + payment page
	public function coinbase_button($order, $urls = array())
	{
		$core_url = $this->app->config->get('core.url');
		$urls = array_map(function($url) use ($core_url) { return $core_url . $url; }, $urls);

		return $this->coinbase->createButton(
			'CSGOShop Order #'.$this->app->hashids->encrypt($order->id), 
			$order->total_taxed, 
			$this->currency, 
			$order->id,
			$urls
		);
	}

	// get order details
	public function coinbase_god($custom_id)
	{
		$cb_order = $this->coinbase->getOrder($custom_id);
		if($cb_order->status != 'completed')
			throw new Coinbase_CheckoutError(json_encode($cb_order));
		return true;
	}

	/*
	 * ******************************
	 * CASHOUT METHODS 
	 * ******************************
	 */

	// Paypal

	public function paypal_generate_payment($cashout, $return_url, $cancel_url)
	{
		// Prepare request arrays
		$PayRequestFields = array(
								'ActionType' => 'PAY', 								// Required.  Whether the request pays the receiver or whether the request is set up to create a payment request, but not fulfill the payment until the ExecutePayment is called.  Values are:  PAY, CREATE, PAY_PRIMARY
								'CancelURL' => $this->app->config->get('core.url') . $cancel_url, 									// Required.  The URL to which the sender's browser is redirected if the sender cancels the approval for the payment after logging in to paypal.com.  1024 char max.
								'CurrencyCode' => $this->currency, 					// Required.  3 character currency code.
								'FeesPayer' => 'SENDER',							// The payer of the fees.  Values are:  SENDER, PRIMARYRECEIVER, EACHRECEIVER, SECONDARYONLY
								'IPNNotificationURL' => '', 						// The URL to which you want all IPN messages for this payment to be sent.  1024 char max.
								'Memo' => 'Cashout #'.$this->app->hashids->encrypt($cashout->id), 	// A note associated with the payment (text, not HTML).  1000 char max
								'Pin' => '', 										// The sener's personal id number, which was specified when the sender signed up for the preapproval
								'PreapprovalKey' => '', 							// The key associated with a preapproval for this payment.  The preapproval is required if this is a preapproved payment.  
								'ReturnURL' => $this->app->config->get('core.url') . $return_url,		// Required.  The URL to which the sener's browser is redirected after approvaing a payment on paypal.com.  1024 char max.
								'ReverseAllParallelPaymentsOnError' => '', 			// Whether to reverse paralel payments if an error occurs with a payment.  Values are:  TRUE, FALSE
								'SenderEmail' => '', 	// Sender's email address.  127 char max.
								'TrackingID' => $this->app->hashids->encrypt($cashout->id, time())	// Unique ID that you specify to track the payment.  127 char max.
								);
								
		$ClientDetailsFields = array(
								'CustomerID' => '',									// Your ID for the sender  127 char max.
								'CustomerType' => '', 								// Your ID of the type of customer.  127 char max.
								'GeoLocation' => '', 								// Sender's geographic location
								'Model' => '', 										// A sub-identification of the application.  127 char max.
								'PartnerName' => ''									// Your organization's name or ID
								);
								
		$FundingTypes = array('ECHECK', 'BALANCE', 'CREDITCARD');					// Funding constrainigs require advanced permissions levels.

		$Receivers = array();
		$Receiver = array(
						'Amount' => $cashout->total_taxed, 								// Required.  Amount to be paid to the receiver.
						'Email' => $cashout->provider_identifier, 					// Receiver's email address. 127 char max.
						'InvoiceID' => $this->app->hashids->encrypt($cashout->id),	// The invoice number for the payment.  127 char max.
						'PaymentType' => '', 							// Transaction type.  Values are:  GOODS, SERVICE, PERSONAL, CASHADVANCE, DIGITALGOODS
						'PaymentSubType' => '', 								// The transaction subtype for the payment.
						'Phone' => array('CountryCode' => '', 'PhoneNumber' => '', 'Extension' => ''), // Receiver's phone number.   Numbers only.
						'Primary' => ''												// Whether this receiver is the primary receiver.  Values are boolean:  TRUE, FALSE
						);
		array_push($Receivers,$Receiver);

		$SenderIdentifierFields = array(
										'UseCredentials' => ''						// If TRUE, use credentials to identify the sender.  Default is false.
										);
										
		$AccountIdentifierFields = array(
										'Email' => '', 								// Sender's email address.  127 char max.
										'Phone' => array('CountryCode' => '', 'PhoneNumber' => '', 'Extension' => '')								// Sender's phone number.  Numbers only.
										);
										
		$PayPalRequestData = array(
							'PayRequestFields' => $PayRequestFields, 
							'ClientDetailsFields' => $ClientDetailsFields, 
							'FundingTypes' => $FundingTypes, 
							'Receivers' => $Receivers, 
							'SenderIdentifierFields' => $SenderIdentifierFields, 
							'AccountIdentifierFields' => $AccountIdentifierFields
							);


		// Pass data into class for processing with PayPal and load the response array into $PayPalResult
		$PayPalResult = $this->paypal_adaptive->Pay($PayPalRequestData);
		if($PayPalResult['Ack'] == 'Success' || $PayPalResult['Ack'] == 'SuccessWithWarning')
			return $PayPalResult;
		else
			throw new PayPal_CashoutError(json_encode($PayPalResult['Errors']));
	}

	public function paypal_paykey_url()
	{
		return $this->app->config->get('paypal.mode') == 'sandbox' 
			? 'https://www.sandbox.paypal.com/cgi-bin/webscr?cmd=_ap-payment&paykey=' 
			: 'https://www.paypal.com/cgi-bin/webscr?cmd=_ap-payment&paykey=';
	}

	// Coinbase

	public function coinbase_generate_address()
	{
		$result = $this->coinbase->post('tokens');
		if(empty($result) || !$result->success)
			throw new Coinbase_CashoutError(json_encode($result));
		return $result;
	}

	public function coinbase_claim($token_id)
	{
		return $this->coinbase->post('tokens/redeem', array('token_id' => $token_id));
	}

	// Stripe

	public function stripe_generate_recipient($cashout)
	{
		return Stripe_Recipient::create(array(
			'name' => $cashout->user->name,
			'type' => 'individual',
			'card' => $cashout->provider_identifier,
			'description' => 'Request #'.$this->app->hashids->encrypt($cashout->id),
			'metadata' => array(
				'user_id' => $cashout->user_id,
				'user_name' => $cashout->user->name,
				'cashout_id' => $this->app->hashids->encrypt($cashout->id))
		));
	}

	public function stripe_generate_transfer($cashout)
	{
		return Stripe_Transfer::create(array(
			'amount' => $cashout->total_taxed * 100,
			'currency' => $this->currency,
			'recipient' => $cashout->token,
			'description' => '',
			'statement_description' => 'Request #'.$this->app->hashids->encrypt($cashout->id),
			'metadata' => array(
				'user_id' => $cashout->user_id,
				'user_name' => $cashout->user->name,
				'cashout_id' => $this->app->hashids->encrypt($cashout->id))
			));
	}
}