<?php
namespace suspendTransients;

class Suspend_Transients {

	protected $_known_transients       = array();
	protected $_found_transients       = array();
	protected $_option_key             = 'st_known_transients';
	protected $_transients_suspended   = array();

	protected $_is_bypassing           = false;

	/**
	 * Entry point
	 */
	public function init() {

		if ( isset( $_GET['bypass-transients'] ) ) {
			$this->_is_bypassing = true;
			add_action( 'after_setup_theme', [ $this, 'filter_all_known_transients' ] );
		}

		if ( isset( $_GET['flush-transients'] ) ) {
			add_action( 'after_setup_theme', [ $this, 'flush_transients' ] );
		}

		add_action( 'setted_transient', [ $this, 'setted_callback' ] );
		add_action( 'shutdown',         [ $this, 'save_found_transients' ] );
		add_action( 'admin_bar_menu',   [ $this, 'inject_admin_bar_button' ] );
		add_action( 'admin_bar_menu',   [ $this, 'inject_admin_bar_button_scan' ] );
	}


	/**
	 * Add a filter for each known transient to return it as false.
	 */
	public function filter_all_known_transients() {
		$this->verify_user_intent( 'bypass_transients' );
		$this->_known_transients = $this->get_known_transients();

		foreach ( $this->get_known_transients() as $transient ) {
			add_filter( 'transient_' .  $transient , [ $this, 'count_and_return_false' ], 10, 2 );
		}
	}


	public function count_and_return_false( $value, $transient ) {
		$this->_transients_suspended[] = $transient;
		return false;
	}

	/**
	 * Any set_transient call will add the key to the array
	 * @param $transient_name
	 */
	public function setted_callback( $transient_name ) {
		if ( is_array( $this->_known_transients ) && ! in_array( $transient_name, $this->_known_transients, true ) ) {
			$this->_found_transients[] = $transient_name;
		}
	}

	/**
	 * Let's add any new transients that were found during the page load.
	 */
	public function save_found_transients() {
		if ( ! empty( $this->_found_transients ) ) {
			$known = $this->get_known_transients();
			update_option( $this->_option_key, array_merge( $known, $this->_found_transients ) );
		}
	}

	/**
	 * Callback to save all of the known options very late in the WP load
	 * to avoid race conditions.
	 */
	public function save_known_transients() {
		update_option( $this->_option_key, $this->_known_transients );
	}

	/**
	 * Helper to retrieve the known transients.
	 * @return mixed|void
	 */
	public function get_known_transients() {
		return get_option( $this->_option_key , array() );
	}

	/**
	 * Helper to retrieve suspended transients.
	 * @return array
	 */
	public function get_suspended_transients() {
		return $this->_transients_suspended;
	}

	/**
	 * Helper to retrieve found transients
	 * @return array
	 */
	public function get_found_transients() {
		return $this->_found_transients;
	}

	/**
	 * Flush the object cache
	 */
	public function flush_transients() {
		$this->verify_user_intent( 'flush_transients' );
		wp_cache_flush();
	}

	/**
	 * Helper to DRY out the nonce checks
	 * @param $nonce
	 */
	protected function verify_user_intent( $nonce ) {
		if ( ! current_user_can( 'manage_options' ) || false === \wp_verify_nonce( $_GET['wp_nonce'], $nonce ) ) {
			wp_die( 'You don\'t have the correct permissions to do that' );
		}
	}


	/**
	 * On some on activation stuff.
	 */
	public function on_activate() {}


	/**
	 * Inject the Bypass button.
	 */
	public function inject_admin_bar_button() {
		global $wp_admin_bar;

		$classes = ( $this->_is_bypassing ) ? 'bypass-transients active': 'bypass-transients';

		echo $var;
		$wp_admin_bar->add_menu(
			array(
				'id'     => 'suspend-transients',
				'parent' => 'top-secondary',
				'title'  => 'Bypass Transients',
				'href'   => '?bypass-transients=true&wp_nonce=' . wp_create_nonce( 'bypass_transients' ),
				'meta'   => array( 'class' => $classes ),
			)
		);
	}

	/**
	 * Add the Flush transients button
	 */
	public function inject_admin_bar_button_scan() {
		global $wp_admin_bar;

		$wp_admin_bar->add_menu(
			array(
				'id'     => 'flush-transients',
				'parent' => 'suspend-transients',
				'title'  => 'Flush Transients',
				'href'   => '?flush-transients=true&wp_nonce=' . wp_create_nonce( 'flush_transients' ),
			)
		);
	}
}
