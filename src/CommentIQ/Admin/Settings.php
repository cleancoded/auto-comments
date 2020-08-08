<?php
/**
 * Cleancoded Comments Settings Page
 *
 * Initializes the settings page for Cleancoded Comments
 *
 * @since 1.1.1
 *
 * @package WordPress
 */
class CommentIQ_Admin_Settings {
    private $options = false;
    
    public function __construct() {
    }
    
    public function run() {
        //Admin Settings
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
		add_action( 'admin_init', array( $this, 'init_admin_settings' ) );
		
		//Plugin settings
		add_filter( 'plugin_action_links_' . cleancoded_COMMENTS_DIR_NAME , array( $this, 'add_settings_link' ) );
		
		$this->get_plugin_options();
    }
    
    /**
	 * Initialize options page
	 *
	 * Create plugin options page and callback
	 *
	 * @since 1.1.1
	 * @access public
	 *
	 * @see run
	 *
	 */	
	public function add_admin_menu() {
		add_options_page( _x( 'Cleancoded Comments', 'Plugin Name - Settings Page Title', 'cleancoded-comments' ), _x( 'Cleancoded Comments', 'Plugin Name - Menu Item', 'cleancoded-comments' ), 'manage_options', 'cleancoded-comments', array( $this, 'options_page' ) );
	}
	
	/**
	 * Add Show-In-Content setting.
	 *
	 * Output checkbox for displaying Facebook sharing.
	 *
	 * @since 1.1.1
	 * @access public
	 *
	 * @see init_admin_settings
	 *
	 * @param array $args {
	 		@type string $label_for Settings label and ID.
	
	 		@type string $desc Description for the setting.
	 		
	 }
	 */
	public function add_settings_field_content_enable( $args = array() ) {
		$settings = $this->get_plugin_options();
		$enable_content = isset( $settings[ 'show_in_content' ] ) ? (bool)$settings[ 'show_in_content' ] : true;
		echo '<input name="cleancoded-comments[show_in_content]" value="off" type="hidden" />';
		printf( '<input id="cleancoded-show-content" type="checkbox" name="cleancoded-comments[show_in_content]" value="on" %s />&nbsp;<label for="cleancoded-show-content">%s</label>', checked( true, $enable_content, false ), __( 'Automatically insert the best comment near the top of each post.', 'cleancoded-comments' ) );
	}
	
	/**
	 * Add a settings link to the plugin's options.
	 *
	 * Add a settings link on the WordPress plugin's page.
	 *
	 * @since 1.1.1
	 * @access public
	 *
	 * @see init
	 *
	 * @param array $links Array of plugin options
	 * @return array $links Array of plugin options
	 */
	public function add_settings_link( $links ) { 
		$settings_link = sprintf( '<a href="%s">%s</a>', esc_url( admin_url( 'options-general.php?page=cleancoded-comments' ) ), _x( 'Settings', 'Plugin settings link on the plugins page', 'cleancoded-comments' ) ); 
			array_unshift($links, $settings_link); 
			return $links; 
	}
	
	/**
	 * Return an option key
	 *
	 * Return an option key
	 *
	 * @since 1.1.1
	 * @access public
	 *
	 * @return mixed option, empty string on failure
	 */
	public static function get_plugin_option( $key ) {
    	$settings = get_option( 'cleancoded-comments' );
    	if ( isset( $settings[ $key ] ) ) {
        	return $settings[ $key ];
    	}
    	return false;
	}
	
	/**
	 * Initialize and return plugin options.
	 *
	 * Return an array of plugin options.
	 *
	 * @since 1.1.1
	 * @access public
	 *
	 * @see run
	 *
	 * @return array Plugin options
	 */
	public function get_plugin_options() {
		if ( false === $this->options ) {
			$settings = get_option( 'cleancoded-comments' );	
		} else {
			$settings = $this->options;
		}
		
		if ( false === $settings || !is_array( $settings ) ) {
			$defaults = array(
				'show_in_content' => true,
			);
			update_option( 'cleancoded-comments', $defaults );
			return $defaults;
		}
		$this->options = $settings;
		return $settings;
	}
	
	/**
	 * Initialize options 
	 *
	 * Initialize page settings, fields, and sections and their callbacks
	 *
	 * @since 1.1.1
	 * @access public
	 *
	 * @see init
	 *
	 */
	public function init_admin_settings() {
		register_setting( 'cleancoded-comments', 'cleancoded-comments', array( $this, 'sanitization' ) );
		
		add_settings_section( 'cleancoded-content-show', _x( 'Options ', 'plugin settings heading' , 'cleancoded-comments' ), array( $this, 'settings_section' ), 'cleancoded-comments' );
		
		add_settings_field( 'cleancoded-comments-content-enable', __( 'Display', 'cleancoded-comments' ), array( $this, 'add_settings_field_content_enable' ), 'cleancoded-comments', 'cleancoded-content-show', array( 'desc' => __( 'Would you like to automatically add Cleancoded Comments to the main content areas?', 'cleancoded-comments' ) ) );
	}
	
	/**
	 * Output options page HTML.
	 *
	 * Output option page HTML and fields/sections.
	 *
	 * @since 1.1.1
	 * @access public
	 *
	 * @see add_admin_menu
	 *
	 */
	public function options_page() {
	?>
	    <div class="wrap">
	        <h2><?php echo esc_html( _x( 'Cleancoded Comments - by Postmatic', 'Plugin Name - Settings Page Title', 'cleancoded-comments' ) ); ?></h2>
	        <form action="<?php echo esc_url( admin_url( 'options.php' ) ); ?>" method="POST">
	            <?php settings_fields( 'cleancoded-comments' ); ?>
	            <?php do_settings_sections( 'cleancoded-comments' ); ?>
	            <?php submit_button(); ?>
	        </form>
	    </div>
    <?php
	}
    
	/**
	 * Sanitize options before they are saved.
	 *
	 * Sanitize and prepare error messages when saving options.
	 *
	 * @since 1.1.1
	 * @access public
	 *
	 * @see init_admin_settings
	 *
	 * @param array $input {
	 		@type string $show_in_content Whether to show in content or not..
	 }
	 * @return array Sanitized array of options
	 */
	public function sanitization( $input = array() ) {
		$output = get_option( 'cleancoded-comments' );
		
		//Check if settings are being initialized for the first time
		if ( false === $output ) {
			//No settings have been saved yet and we're being supplied with defaults
			foreach( $input as $key => &$value ) {
				if ( is_bool( $value ) ) continue;
				$value = sanitize_text_field( $value );
			}	
			return $input;
		}
		//Settings are being saved.  Update.
		foreach( $input as $key => $value ) {
			if ( $input[ $key ] == 'on' ) {
				$output[ $key ] = true;	
			} else {
				$output[ $key ] = false;
			}
		}
		add_settings_error( 'cleancoded-comments', 'success', _x( 'Settings Saved', 'Success on save', 'cleancoded-comments' ), 'updated' );
		return $output;
	}
	
	/**
	 * Output settings HTML
	 *
	 * Output any HTML required to go into a settings section
	 *
	 * @since 1.1.1
	 * @access public
	 *
	 * @see init_admin_settings
	 *
	 */
	public function settings_section() {
	}
}