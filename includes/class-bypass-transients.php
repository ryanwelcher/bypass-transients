<?php

class Bypass_Transients {

	protected $_known_transients       = array();
	protected $_found_transients       = array();
	protected $_option_key             = 'st_known_transients';
	protected $_transients_bypassed    = array();

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

		add_action( 'setted_transient',      [ $this, 'setted_callback' ] );
		add_action( 'shutdown',              [ $this, 'save_found_transients' ] );
		add_action( 'admin_bar_menu',        [ $this, 'inject_admin_bar_button' ] );
		add_action( 'admin_bar_menu',        [ $this, 'inject_admin_bar_button_scan' ] );
		add_action( 'wp_enqueue_scripts',    [ $this, 'add_admin_bar_css' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'add_admin_bar_css' ] );
	}

	function add_admin_bar_css() {
		$min = ( defined( 'SCRIPT_DEBUG' ) & true === SCRIPT_DEBUG ) ? 'src' : 'min';
		wp_enqueue_style( 'bypass-transients', plugins_url( 'assets/css/bypass-transients.' . $min . '.css', dirname( __FILE__ ) ) );
	}


	/**
	 * Add a filter for each known transient to return it as false.
	 */
	public function filter_all_known_transients() {
		$this->verify_user_intent( 'bypass_transients' );
		$this->_known_transients = $this->get_known_transients();

		foreach ( $this->get_known_transients() as $transient ) {
			add_filter( 'transient_' . $transient , [ $this, 'count_and_return_false' ], 10, 2 );
		}
	}


	public function count_and_return_false( $value, $transient ) {
		$this->_transients_bypassed[] = $transient;
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
	public function get_bypassed_transients() {
		return $this->_transients_bypassed;
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
		if ( ! current_user_can( 'manage_options' ) || false === wp_verify_nonce( $_GET['wp_nonce'], $nonce ) ) {
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

		$classes = 'bypass-transients';

		if ( $this->_is_bypassing ) {
			$classes .= ' active';
		} elseif ( 0 < count( $this->_found_transients ) ) {
			$classes .= ' found';
		}

		$title   = ( $this->_is_bypassing ) ? 'Re-activate Transients' : 'Bypass Transients';
		$href    = ( $this->_is_bypassing ) ? '/' :'?bypass-transients=true&wp_nonce=' . wp_create_nonce( 'bypass_transients' );

		$wp_admin_bar->add_menu(
			array(
				'id'     => 'bypass-transients',
				'parent' => 'top-secondary',
				'title'  => $title,
				'href'   => $href,
				'meta'   => array( 'class' => $classes ),
			)
		);

		if ( $this->_is_bypassing ) {

			$wp_admin_bar->add_menu(
				array(
					'id' => 'bypassed-transients',
					'parent' => 'bypass-transients',
					'title' => 'Bypassed Transients: ' . count( $this->_transients_bypassed ),
				)
			);

			foreach ( $this->_transients_bypassed as $key => $transient ) {
				$wp_admin_bar->add_menu(
					array(
						'id' => $key . '_' . $transient,
						'parent' => 'bypassed-transients',
						'title' => $transient,
					)
				);

			}
		}

		$wp_admin_bar->add_menu(
			array(
				'id'     => 'known-transients',
				'parent' => 'bypass-transients',
				'title'  => 'Known Transients: ' . count( $this->get_known_transients() ),
			)
		);

		if ( 0 < count( $this->get_found_transients() ) ) {
			$wp_admin_bar->add_menu(
				array(
					'id'     => 'found-transients',
					'parent' => 'bypass-transients',
					'title'  => 'Found Transients: ' . count( $this->get_found_transients() ),
				)
			);
			foreach ( $this->_found_transients as  $key => $transient ) {
				$wp_admin_bar->add_menu(
					array(
						'id' => $key . '_' . $transient,
						'parent' => 'found-transients',
						'title' => $transient,
					)
				);

			}
		}
	}

	/**
	 * Add the Flush transients button
	 */
	public function inject_admin_bar_button_scan() {
		global $wp_admin_bar;

		$wp_admin_bar->add_menu(
			array(
				'id'     => 'flush-transients',
				'parent' => 'bypass-transients',
				'title'  => 'Flush Transients',
				'href'   => '?flush-transients=true&wp_nonce=' . wp_create_nonce( 'flush_transients' ),
			)
		);
	}
}
