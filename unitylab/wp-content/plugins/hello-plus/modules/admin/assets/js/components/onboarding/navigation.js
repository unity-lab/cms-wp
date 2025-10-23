import Box from '@elementor/ui/Box';
import { __ } from '@wordpress/i18n';
import Step from '@elementor/ui/Step';
import StepLabel from '@elementor/ui/StepLabel';
import Stepper from '@elementor/ui/Stepper';
import Typography from '@elementor/ui/Typography';
import { useAdminContext } from '../../hooks/use-admin-context';

export const Navigation = () => {
	const { step } = useAdminContext();

	const steps = [ __( 'Get started', 'hello-plus' ), __( 'Choose website template', 'hello-plus' ), __( 'Ready to go', 'hello-plus' ) ];

	return (
		<Box sx={ { width: '100%' } }>
			<Stepper activeStep={ step }>
				{ steps.map( ( label, index ) => {
					return (
						<Step key={ label } completed={ index < step } active={ index === step }>
							<StepLabel>
								<Typography>
									{ label }
								</Typography>
							</StepLabel>
						</Step>
					);
				} ) }
			</Stepper>
		</Box>
	);
};
