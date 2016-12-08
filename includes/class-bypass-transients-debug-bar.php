<?php
class Bypass_Transients_Debug_Bar extends Debug_Bar_Panel {

	public function init() {
		$this->title( __( 'Bypass Transients' ) );
	}

	/**
	 * Renders the page
	 */
	public function render() {
		?>
		<div id="bypass-transient-information">
			<?php
			$this->output_transients( 'suspended' );
			$this->output_transients( 'found' );
			$this->output_transients( 'known' );
			?>
		</div>
		<?php
	}

	function output_transients( $type = 'known' ) {
		global $bypass_transients;
		switch ( $type ) {
			case 'suspended':
				$transients           = $bypass_transients->get_bypassed_transients();
				$number_of_transients = count( $transients );
				$message              = sprintf( _n( 'There was %s transient bypassed for this page load.', 'There were %s transients bypassed for this page load.', $number_of_transients ), $number_of_transients );
				break;
			case 'found':
				$transients           = $bypass_transients->get_found_transients();
				$number_of_transients = count( $transients );
				$message              = sprintf( _n( 'There was %s transient found for this page load.', 'There were %s transients found for this page load.', $number_of_transients ), $number_of_transients );
			break;
			case 'known':
				$transients           = $bypass_transients->get_known_transients();
				$number_of_transients = count( $transients );
				$message              = sprintf( _n( 'There is %s known transient for the active theme/plugins.', 'There are %s known transients for the active theme/plugins.', $number_of_transients ), $number_of_transients );
				break;
			default:
				$message = 'Unknown $type parameter';
				$transients = array();


		}

		?>
		<h4><?php echo esc_html( $message );?></h4>
		<?php
		if ( ! empty( $transients ) ) {
			echo "<pre>\n" . print_r( $transients,1 ) . '</pre>';
		}
	}
}