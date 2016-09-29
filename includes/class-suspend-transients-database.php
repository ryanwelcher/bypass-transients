<?php

namespace suspendTransients;

class Suspend_Transients_Database extends Suspend_Transients {

	public function on_activate() {
		parent::on_activate();
		global $wpdb;
		$sql = "SELECT option_name from $wpdb->options WHERE option_name LIKE '_transient_%'";
		$transients = $wpdb->get_results( $sql );

		if ( ! empty( $transients ) ) {
			foreach ( $transients as $transient ) {
				if ( false === strpos( $transient->option_name, '_timeout_' ) ) {
					$transient_name = str_replace( '_transient_', '', $transient->option_name );
					if ( ! in_array( $transient_name, $this->_known_transients, true ) ) {
						$this->_known_transients[] = $transient_name;
					}
				}
			}
		}
		$this->save_known_transients();
	}
}
