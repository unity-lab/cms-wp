<?php
/**
 * ShippingZoneSchema class.
 *
 * @package WooCommerce\RestApi
 */

declare( strict_types=1 );

namespace Automattic\WooCommerce\RestApi\Routes\V4\ShippingZones;

defined( 'ABSPATH' ) || exit;

use Automattic\WooCommerce\RestApi\Routes\V4\AbstractSchema;
use WC_Shipping_Zone;
use WP_REST_Request;

/**
 * ShippingZoneSchema class.
 */
class ShippingZoneSchema extends AbstractSchema {
	/**
	 * The schema item identifier.
	 *
	 * @var string
	 */
	const IDENTIFIER = 'shipping_zone';

	/**
	 * Return all properties for the item schema.
	 *
	 * @return array
	 */
	public function get_item_schema_properties(): array {
		$schema = array(
			'id'        => array(
				'description' => __( 'Unique identifier for the shipping zone.', 'woocommerce' ),
				'type'        => 'integer',
				'readonly'    => true,
			),
			'name'      => array(
				'description' => __( 'Shipping zone name.', 'woocommerce' ),
				'type'        => 'string',
				'readonly'    => true,
			),
			'order'     => array(
				'description' => __( 'Shipping zone order.', 'woocommerce' ),
				'type'        => 'integer',
				'readonly'    => true,
			),
			'locations' => array(
				'description' => __( 'Array of location names for this zone.', 'woocommerce' ),
				'type'        => 'array',
				'readonly'    => true,
				'items'       => array(
					'type' => 'string',
				),
			),
			'methods'   => array(
				'description' => __( 'Shipping methods for this zone.', 'woocommerce' ),
				'type'        => 'array',
				'readonly'    => true,
				'items'       => array(
					'type'       => 'object',
					'properties' => array(
						'instance_id' => array(
							'description' => __( 'Shipping method instance ID.', 'woocommerce' ),
							'type'        => 'integer',
						),
						'title'       => array(
							'description' => __( 'Shipping method title.', 'woocommerce' ),
							'type'        => 'string',
						),
						'enabled'     => array(
							'description' => __( 'Whether the shipping method is enabled.', 'woocommerce' ),
							'type'        => 'boolean',
						),
						'method_id'   => array(
							'description' => __( 'Shipping method ID (e.g., flat_rate, free_shipping).', 'woocommerce' ),
							'type'        => 'string',
						),
						'settings'    => array(
							'description' => __( 'Raw shipping method settings for frontend processing.', 'woocommerce' ),
							'type'        => 'object',
						),
					),
				),
			),
		);

		return $schema;
	}

	/**
	 * Get the item response.
	 *
	 * @param WC_Shipping_Zone $zone WordPress representation of the zone.
	 * @param WP_REST_Request  $request Request object.
	 * @param array            $include_fields Fields to include in the response.
	 * @return array The item response.
	 */
	public function get_item_response( $zone, WP_REST_Request $request, array $include_fields = array() ): array {
		return array(
			'id'        => $zone->get_id(),
			'name'      => $zone->get_zone_name(),
			'order'     => $zone->get_zone_order(),
			'locations' => $this->get_formatted_zone_locations( $zone, isset( $request['id'] ) ? 'detailed' : 'summary' ),
			'methods'   => $this->get_formatted_zone_methods( $zone ),
		);
	}

	/**
	 * Get array of location names for display.
	 *
	 * @param WC_Shipping_Zone $zone Shipping zone object.
	 * @param string           $view The view for which the API is requested ('summary' or 'detailed').
	 * @return array
	 */
	protected function get_formatted_zone_locations( WC_Shipping_Zone $zone, string $view = 'summary' ): array {
		if ( 0 === $zone->get_id() ) {
			return array( __( 'All regions not covered above', 'woocommerce' ) );
		}

		$locations = $zone->get_zone_locations();

		if ( 'summary' === $view ) {
			$location_names = array();
			foreach ( $locations as $location ) {
				$location_names[] = $this->get_location_name( $location );
			}
			return $location_names;
		} else {
			// For detailed view, add name property to each location object.
			$detailed_locations = array();
			foreach ( $locations as $location ) {
				$location_copy        = clone $location;
				$location_copy->name  = $this->get_location_name( $location );
				$detailed_locations[] = $location_copy;
			}
			return $detailed_locations;
		}
	}

	/**
	 * Get formatted methods for a zone.
	 *
	 * @param WC_Shipping_Zone $zone Shipping zone object.
	 * @return array
	 */
	protected function get_formatted_zone_methods( $zone ) {
		$methods           = $zone->get_shipping_methods( false, 'json' );
		$formatted_methods = array();

		foreach ( $methods as $method ) {
			$formatted_method = array(
				'instance_id' => $method->instance_id,
				'title'       => $method->title,
				'enabled'     => 'yes' === $method->enabled,
				'method_id'   => $method->id,
				'settings'    => $this->get_method_settings( $method ),
			);

			$formatted_methods[] = $formatted_method;
		}

		return $formatted_methods;
	}

	/**
	 * Get raw method settings for frontend processing.
	 *
	 * @param object $method Shipping method object.
	 * @return array
	 */
	protected function get_method_settings( $method ) {
		$settings = array();

		// Common settings that most methods have.
		$common_fields = array( 'cost', 'min_amount', 'requires', 'class_cost', 'no_class_cost' );

		foreach ( $common_fields as $field ) {
			if ( isset( $method->$field ) ) {
				$settings[ $field ] = $method->$field;
			}
		}

		// Return all available settings for maximum flexibility.
		if ( isset( $method->instance_settings ) && is_array( $method->instance_settings ) ) {
			$settings = array_merge( $settings, $method->instance_settings );
		}

		return $settings;
	}

	/**
	 * Get location name from location object.
	 *
	 * @param object $location Location object.
	 * @return string
	 */
	protected function get_location_name( $location ) {
		switch ( $location->type ) {
			case 'continent':
				$continents = WC()->countries->get_continents();
				return isset( $continents[ $location->code ] ) ? $continents[ $location->code ]['name'] : $location->code;

			case 'country':
				$countries = WC()->countries->get_countries();
				return isset( $countries[ $location->code ] ) ? $countries[ $location->code ] : $location->code;

			case 'state':
				$parts = explode( ':', $location->code );
				if ( count( $parts ) === 2 ) {
					$states = WC()->countries->get_states( $parts[0] );
					return isset( $states[ $parts[1] ] ) ? $states[ $parts[1] ] : $location->code;
				}
				return $location->code;

			case 'postcode':
				return $location->code;

			default:
				return $location->code;
		}
	}
}
