<?php

namespace suspendTransients;

class Suspend_Transients_Database extends Suspend_Transients {

	public function on_activate() {
		parent::on_activate();
		$this->scan_transients();
	}

	public function init() {
		parent::init();
		add_action( 'admin_bar_menu', [ $this, 'inject_admin_bar_button_scan' ] );

		if ( isset( $_GET['scan-transients'] ) ) {
			$this->scan_transients();
		}
	}

	/**
	 * Retrieve the transients from the database.
	 */
	public function scan_transients() {
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

	/**
	 * Add the Scan transients button
	 */
	public function inject_admin_bar_button_scan() {
		global $wp_admin_bar;

		$wp_admin_bar->add_menu(
			array(
				'id'     => 'scan-transients',
				'parent' => 'top-secondary',
				'title'  => 'Scan Transients',
				'href'   => '?scan-transients=true',
			)
		);
	}
}
