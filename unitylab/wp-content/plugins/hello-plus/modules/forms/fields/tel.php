<?php
namespace HelloPlus\Modules\Forms\Fields;

use HelloPlus\Modules\Forms\Classes;
use HelloPlus\Modules\Forms\Components\Ajax_Handler;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Tel extends Field_Base {

	public function get_type() {
		return 'ehp-tel';
	}

	public function get_name() {
		return esc_html__( 'Tel', 'hello-plus' );
	}

	public function render( $item, $item_index, $form ) {
		$form->add_render_attribute( 'input' . $item_index, 'class', 'elementor-field-textual' );
		$form->add_render_attribute( 'input' . $item_index, 'pattern', '[0-9()#&+*\-=.]+' );
		$form->add_render_attribute( 'input' . $item_index, 'title', esc_html__( 'Only numbers and phone characters (#, -, *, etc) are accepted.', 'hello-plus' ) );
		?>
		<input size="1" <?php $form->print_render_attribute_string( 'input' . $item_index ); ?>>

		<?php
	}

	public function validation( $field, Classes\Form_Record $record, Ajax_Handler $ajax_handler ) {
		if ( empty( $field['value'] ) ) {
			return;
		}
		if ( preg_match( '/^[0-9()#&+*-=.]+$/', $field['value'] ) !== 1 ) {
			$ajax_handler->add_error( $field['id'], esc_html__( 'The field accepts only numbers and phone characters (#, -, *, etc).', 'hello-plus' ) );
		}
	}
}
