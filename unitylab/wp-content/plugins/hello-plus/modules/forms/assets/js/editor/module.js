import Component from './component';
import FieldsMapControl from './fields-map-control';
import FieldsRepeaterControl from './fields-repeater-control';

export default class FormsModule extends elementorModules.editor.utils.Module {
	onElementorInit() {
		const ReplyToField = require( './reply-to-field' );

		this.replyToField = new ReplyToField();

		// Form fields
		const AcceptanceField = require( './fields/acceptance' ),
			TelField = require( './fields/tel' );

		this.Fields = {
			tel: new TelField( 'ehp-form' ),
			acceptance: new AcceptanceField( 'ehp-form' ),
		};

		elementor.addControlView( 'ehp_fields_map', FieldsMapControl );
		elementor.addControlView( 'ehp_forms_fields_repeater', FieldsRepeaterControl );

		if ( typeof elementorPromotionsData !== 'undefined' ) {
			elementorPromotionsData.collect_submit = window.ehpFormsPromotionData;
		}
	}

	onElementorInitComponents() {
		$e.components.register( new Component( { manager: this } ) );
	}
}
