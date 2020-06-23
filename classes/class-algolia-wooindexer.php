<?php
/**
 * Main Algolia Woo Indexer class
 * Called from main plugin file algolia-woo-indexer.php
 *
 * @package algolia-woo-indexer
 */

namespace ALGOWOO;

// Define the plugin version.
define( 'ALGOWOO_DB_OPTION', 'algo_woo' );
define( 'ALGOWOO_CURRENT_DB_VERSION', 0.3 );

if ( ! class_exists( 'Algolia_Woo_Indexer' ) ) {
	/**
	 * WooIndexer
	 */
	class Algolia_Woo_Indexer {

		const PLUGIN_NAME      = 'Algolia Woo Indexer';
		const PLUGIN_TRANSIENT = 'algowoo-plugin-notice';

		/**
		 * Class instance
		 *
		 * @var object
		 */
		private static $instance;

		/**
		 * The plugin URL
		 *
		 * @var string
		 */
		private static $plugin_url = '';

		/**
		 * Class constructor
		 *
		 * @return void
		 */
		public function __construct() {
			$this->init();
		}

		/**
		 * Setup sections and fields to store and retrieve values from Settings API
		 *
		 * @return void
		 */
		public static function setup_settings_sections() {
			/**
			* Setup arguments for settings sections and fields
			* See https://developer.wordpress.org/reference/functions/register_setting/
			*/
			if ( is_admin() ) {
				$arguments = array(
					'type'              => 'string',
					'sanitize_callback' => 'settings_fields_validate_options',
					'default'           => null,
				);
				register_setting( 'algo_woo_options', 'algo_woo_options', $arguments );

				/**
				 * Make sure we reference the instance of the current class by using self::get_instance()
				 * This way we can setup the correct callback function for add_settings_section and add_settings_field
				 */
				$algowooindexer = self::get_instance();

				add_settings_section(
					'algo_woo_plugin_main',
					'Algo Woo Plugin Settings',
					array( $algowooindexer, 'algo_woo_plugin_section_text' ),
					'algo_woo_plugin'
				);
				add_settings_field(
					'algo_woo_plugin',
					'Test Name',
					array( $algowooindexer, 'algo_woo_plugin_setting_name' ),
					'algo_woo_plugin',
					'algo_woo_plugin_main'
				);
			}
		}

		/**
		 * Section text for plugin settings field
		 *
		 * @return void
		 */
		public static function algo_woo_plugin_setting_name() {
			echo '<p>Enter your settings here.</p>';
		}

		/**
		 * Section text for plugin settings section
		 *
		 * @return void
		 */
		public static function algo_woo_plugin_section_text() {
			echo '<p>Enter your settings here.</p>';
		}


		/**
		 * Initialize class, setup settings sections and fields
		 *
		 * @return void
		 */
		public static function init() {
			$ob_class = get_called_class();
			add_action( 'plugins_loaded', array( $ob_class, 'load_textdomain' ) );
			self::load_settings();
			if ( is_admin() ) {
				/**
				 * Add actions to setup admin menu
				 */
				add_action( 'admin_menu', array( $ob_class, 'admin_menu' ) );
				add_action( 'admin_init', array( $ob_class, 'setup_settings_sections' ) );

				self::$plugin_url = admin_url( 'options-general.php?page=algolia-woo-indexer-settings' );

			}
		}

		/**
		 * Sanitize input in settings fields and filter through regex to accept only a-z and A-Z
		 *
		 * @param string $input Settings text data
		 * @return array
		 */
		public static function settings_fields_validate_options( $input ) {
			$valid         = array();
			$valid['name'] = preg_replace(
				'/[^a-zA-Z\s]/',
				'',
				$input['name']
			);
			return $valid;
		}

		/**
		 * Load text domain for internalization
		 *
		 * @return void
		 */
		public static function load_textdomain() {
			load_plugin_textdomain( 'algolia-woo-indexer', false, basename( dirname( __FILE__ ) ) . '/languages/' );
		}

		/**
		 * Add the new menu to settings section so that we can configure the plugin
		 *
		 * @return void
		 */
		public static function admin_menu() {
			add_submenu_page(
				'options-general.php',
				esc_html__( 'Algolia Woo Indexer Settings', 'algolia-woo-indexer' ),
				esc_html__( 'Algolia Woo Indexer Settings', 'algolia-woo-indexer' ),
				'manage_options',
				'algolia-woo-indexer-settings',
				array( get_called_class(), 'algolia_woo_indexer_settings' )
			);
		}

		/**
		 * Display settings and allow user to modify them
		 *
		 * @return void
		 */
		public static function algolia_woo_indexer_settings() {
			/**
			* Verify that the user can access the settings page
			*/
			if ( ! current_user_can( 'manage_options' ) ) {
				wp_die( esc_html__( 'Action not allowed.', 'algolia_woo_indexer_settings' ) );
			}
			?>
			<div class="wrap">
				<h1><?php esc_html_e( 'Algolia Woo Indexer Settings', 'algolia-woo-indexer' ); ?></h1>
				<form action="<?php echo esc_url( self::$plugin_url ); ?>" method="POST">
				<?php
				settings_fields( 'algo_woo_options' );
				do_settings_sections( 'algo_woo_plugin' );
				submit_button( 'Save Changes', 'primary' );
				?>
			  

				</form>
			</div>
			<?php
		}

		/**
		 * Get active object instance
		 *
		 * @return object
		 */
		public static function get_instance() {
			if ( ! self::$instance ) {
				self::$instance = new Algolia_Woo_Indexer();
			}
			return self::$instance;
		}

		/**
		 * Load plugin settings.
		 *
		 * @return void
		 */
		public static function load_settings() {
			// TODO Load settings and get plugin options !
		}

		/**
		 * The actions to execute when the plugin is activated.
		 *
		 * @return void
		 */
		public static function activate_plugin() {
			set_transient( self::PLUGIN_TRANSIENT, true );
		}

		/**
		 * The actions to execute when the plugin is deactivated.
		 *
		 * @return void
		 */
		public static function deactivate_plugin() {
			delete_option( ALGOWOO_DB_OPTION . '_db_ver' );
		}
	}
}
