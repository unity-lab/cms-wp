<?php
namespace HelloPlus\Modules\TemplateParts\Components;

use HelloPlus\Modules\TemplateParts\Classes\Runners\Import_Floating_Elements;
use HelloPlus\Modules\TemplateParts\Classes\Runners\Handle_Woocommerce_Activation;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Elementor\App\Modules\ImportExport\Processes\Import as Elementor_Import;
use Elementor\App\Modules\ImportExport\Processes\Export as Elementor_Export;
use Elementor\App\Modules\ImportExport\Processes\Revert as Elementor_Revert;

use HelloPlus\Includes\Utils;
use HelloPlus\Modules\TemplateParts\Classes\Runners\Import as Ehp_Import;
use HelloPlus\Modules\TemplateParts\Classes\Runners\Export as Ehp_Export;
use HelloPlus\Modules\TemplateParts\Classes\Runners\Revert as Ehp_Revert;

class Import_Export {
	public function register_import_runners( Elementor_Import $import ) {
		$import->register( new Ehp_Import() );
	}

	public function register_import_runner_floating_elements( Elementor_Import $import ) {
		$import->register( new Import_Floating_Elements() );
	}

	public function register_import_runner_handle_woocommerce_activation( Elementor_Import $import ) {
		$import->register( new Handle_Woocommerce_Activation() );
	}

	public function register_export_runners( Elementor_Export $export ) {
		$export->register( new Ehp_Export() );
	}

	public function register_revert_runners( Elementor_Revert $revert ) {
		$revert->register( new Ehp_Revert() );
	}

	public function __construct() {

		add_action( 'elementor/import-export/import-kit', [ $this, 'register_import_runner_floating_elements' ], 20, 1 );
		add_action( 'elementor/import-export/import-kit', [ $this, 'register_import_runner_handle_woocommerce_activation' ], 20, 1 );

		if ( ! Utils::has_pro() ) {
			add_action( 'elementor/import-export/import-kit', [ $this, 'register_import_runners' ], 25, 1 );
			add_action( 'elementor/import-export/export-kit', [ $this, 'register_export_runners' ] );
			add_action( 'elementor/import-export/revert-kit', [ $this, 'register_revert_runners' ] );
		}
	}
}
