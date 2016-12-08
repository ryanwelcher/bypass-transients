<?php

class Bypass_Transients_Database extends Bypass_Transients {

	public function on_activate() {
		parent::on_activate();
		$this->retrieve_transients_from_database();
	}

	public function init() {
		parent::init();
		add_action( 'admin_bar_menu', [ $this, 'inject_admin_bar_button_scan' ] );

		if ( isset( $_GET['scan-transients'] ) ) {
			add_action( 'after_setup_theme', [ $this, 'scan_transients' ] );
		}
	}

	/**
	 * Retrieve the transients from the database with user checking.
	 */
	public function scan_transients() {
		$this->verify_user_intent( 'scan_transients' );
		$this->retrieve_transients_from_database();
	}


	/**
	 * Retrieves the transients from the options table and generates the known list.
	 */
	protected function retrieve_transients_from_database() {
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
				'parent' => 'bypass-transients',
				'title'  => 'Scan Transients',
				'href'   => '?scan-transients=true&wp_nonce=' . wp_create_nonce( 'scan_transients' ),
			)
		);
	}
}
