<?php

namespace HelloPlus\Modules\Admin\Components;

use HelloPlus\Includes\Utils;
use HelloPlus\Modules\Admin\Classes\Menu\Pages\Settings;
use HelloPlus\Modules\Admin\Classes\Menu\Pages\Setup_Wizard;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Admin_Menu_Controller {

	const SETUP_WIZARD_TRANSIENT_NAME = 'helloplus_redirect_to_setup_wizard';

	public function admin_menu( $parent_slug ) {
		if ( Utils::has_hello_elementor_theme() ) {
			return;
		}

		$show_menu = filter_input( INPUT_GET, 'show-menu', FILTER_UNSAFE_RAW );

		if ( 'true' !== $show_menu && Utils::has_at_least_one_kit() ) {
			return;
		}

		$setup_wizard = new Setup_Wizard();
		$setup_wizard->register_setup_wizard_page( $parent_slug );
	}

	public function activate() {
		set_transient( self::SETUP_WIZARD_TRANSIENT_NAME, true );
	}

	public function redirect_on_first_activation() {
		if ( empty( get_transient( self::SETUP_WIZARD_TRANSIENT_NAME ) ) ) {
			return;
		}

		if ( ! is_admin() ) {
			return;
		}

		delete_transient( self::SETUP_WIZARD_TRANSIENT_NAME );

		if ( Utils::are_we_on_elementor_domains() ) {
			return;
		}

		if ( Utils::is_test_environment() ) {
			return;
		}

		if ( Utils::has_at_least_one_kit() ) {
			wp_safe_redirect(
				add_query_arg(
					[
						'page' => Utils::get_theme_slug(),
						'install-confirmation' => 'true',
					],
					self_admin_url( 'admin.php' )
				)
			);
		} else {
			wp_safe_redirect( self_admin_url( 'admin.php?page=' . Setup_Wizard::SETUP_WIZARD_PAGE_SLUG ) );
		}

		exit;
	}

	public function __construct() {
		add_action( 'hello-plus-theme/admin-menu', [ $this, 'admin_menu' ], 10, 1 );
		add_action( 'hello-plus/init', [ $this, 'redirect_on_first_activation' ] );
		if ( ! Utils::has_hello_elementor_theme() ) {
			add_action( 'hello-plus/activate', [ $this, 'activate' ] );
		}
	}
}
