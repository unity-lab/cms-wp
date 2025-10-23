<?php
/**
 * PayPal Gateway Constants.
 *
 * Provides constants for PayPal payment statuses, intents, and other PayPal-related values.
 *
 * @version     10.3.0
 * @package  WooCommerce\Gateways
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WC_Gateway_Paypal_Constants Class.
 */
class WC_Gateway_Paypal_Constants {
	/**
	 * PayPal proxy request timeout.
	 */
	const WPCOM_PROXY_REQUEST_TIMEOUT = 60;

	/**
	 * PayPal payment statuses.
	 */
	const STATUS_COMPLETED = 'COMPLETED';
	const STATUS_APPROVED  = 'APPROVED';
	const STATUS_CAPTURED  = 'CAPTURED';

	/**
	 * PayPal payment intents.
	 */
	const INTENT_CAPTURE   = 'CAPTURE';
	const INTENT_AUTHORIZE = 'AUTHORIZE';

	/**
	 * PayPal payment actions.
	 */
	const PAYMENT_ACTION_CAPTURE   = 'capture';
	const PAYMENT_ACTION_AUTHORIZE = 'authorize';

	/**
	 * PayPal shipping preferences.
	 */
	const SHIPPING_NO_SHIPPING          = 'NO_SHIPPING';
	const SHIPPING_GET_FROM_FILE        = 'GET_FROM_FILE';
	const SHIPPING_SET_PROVIDED_ADDRESS = 'SET_PROVIDED_ADDRESS';

	/**
	 * PayPal user actions.
	 */
	const USER_ACTION_PAY_NOW = 'PAY_NOW';

	/**
	 * Maximum lengths for PayPal fields.
	 */
	const PAYPAL_ORDER_ITEM_NAME_MAX_LENGTH = 127;
	const PAYPAL_INVOICE_ID_MAX_LENGTH      = 127;
	const PAYPAL_ADDRESS_LINE_MAX_LENGTH    = 300;
	const PAYPAL_STATE_MAX_LENGTH           = 300;
	const PAYPAL_CITY_MAX_LENGTH            = 120;
	const PAYPAL_POSTAL_CODE_MAX_LENGTH     = 60;

	/**
	 * Supported payment sources.
	 */
	const PAYMENT_SOURCE_PAYPAL     = 'paypal';
	const PAYMENT_SOURCE_VENMO      = 'venmo';
	const PAYMENT_SOURCE_PAYLATER   = 'paylater';
	const SUPPORTED_PAYMENT_SOURCES = array( self::PAYMENT_SOURCE_PAYPAL, self::PAYMENT_SOURCE_VENMO, self::PAYMENT_SOURCE_PAYLATER );

	/**
	 * Fields to redact from logs.
	 *
	 * @var array
	 */
	const FIELDS_TO_REDACT = array(
		'given_name',
		'surname',
		'full_name',
		'address_line_1',
		'address_line_2',
		'admin_area_1',
		'admin_area_2',
		'postal_code',
		'phone',
		'phone_number',
		'national_number',
	);
}
