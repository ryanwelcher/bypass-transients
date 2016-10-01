<?php
namespace suspendTransients;

class Suspend_Transients {

	protected $_known_transients       = array();
	protected $_found_transients       = array();
	protected $_option_key             = 'st_known_transients';
	protected $_transients_suspended   = array();

	/**
	 * Entry point
	 */
	public function init() {
		if ( ! is_admin()  && isset( $_GET['bypass-transients'] ) ) {
			$this->_known_transients = $this->get_known_transients();
			add_action( 'after_setup_theme', [ $this, 'filter_all_known_transients' ] );
		}
		add_action( 'setted_transient', [ $this, 'setted_callback' ] );
		add_action( 'shutdown', [ $this, 'save_known_transients' ] );
		add_action( 'admin_bar_menu', [ $this, 'inject_admin_bar_button' ] );
	}


	/**
	 * Add a filter for each known transient to return it as false.
	 */
	public function filter_all_known_transients() {
		foreach ( $this->_known_transients as $transient ) {
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

	public function get_suspended_transients() {
		return $this->_transients_suspended;
	}

	/**
	 * On some on activation stuff.
	 */
	public function on_activate() {}

	public function inject_admin_bar_button() {
		global $wp_admin_bar;

		$wp_admin_bar->add_menu( array(
			'id'     => 'suspend-transients',
			'parent' => 'top-secondary',
			'title'  => 'Bypass Transients',
			'href'   => '?bypass-transients=true',
			)
		);
	}
}
