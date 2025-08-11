<?php

namespace Amazon\Affiliate\Fiu;

if( ! class_exists( 'Fiu' ) ):

	class Fiu {

		public $woo_hook;
		
		private static $instance;

		private function __construct() {}

		public static function instance() {
			if( !self::$instance ) {
				// Create instance of class
				self::$instance = new self();
				self::$instance->define_constants();
				self::$instance->woo_hook = new FiuImageUrl();
			}
			
			return self::$instance;	
		}

		/**
		 * Define constants
		 */
		public function define_constants() {

			if( ! defined( 'AMSWOOFIU_OPTIONS' ) ){
				define( 'AMSWOOFIU_OPTIONS', 'amswoofiu_options' );
			}

			if( ! defined( 'AMSWOOFIU_URL' ) ){
				define( 'AMSWOOFIU_URL', '_amswoofiu_url' );
			}

			if( ! defined( 'AMSWOOFIU_ALT' ) ){
				define( 'AMSWOOFIU_ALT', '_amswoofiu_alt' );
			}
			
			if( ! defined( 'AMSWOOFIU_WCGALLARY' ) ){
				define( 'AMSWOOFIU_WCGALLARY', '_amswoofiu_wcgallary' );
			}
			
		}
	}

endif;