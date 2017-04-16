<?php
/**
 * Plugin Name: WP Paramscaper
 * Description: Helps to collect and pass tracking params
 * Version: 1.0.0
 *
 * Text Domain: wp-paramscraper
 *
 * @package WpParamscraper
 */

/**
 * WpParamscraper Class
 */
class WpParamscraper {

	/**
	 * Construct the plugin
	 */
	public function __construct() {

		/**
		 * Add settings menu item
		 */
		add_action( 'init', array( $this, 'set_params' ) );
		add_action( 'admin_menu', array( $this, 'create_menu_item' ) );
		add_action( 'wp_ajax_new_param', array( $this, 'get_field' ) );
		add_shortcode( 'get_param', array( $this, 'get_params' ) );
	}

	/**
	 * Paramscraper menu item function
	 */
	public function create_menu_item() {
		add_submenu_page( 'options-general.php', 'Paramscaper', 'Paramscaper', 'manage_options', 'paramscraper', array( $this, 'paramscraper_settings' ) );
		return;
	}

	/**
	 * Paramscraper page function
	 */
	public function paramscraper_settings() {
	?>
		<div class="wrap">
			<form method="post" action="options.php">
				<?php
					wp_nonce_field( 'update-options' );
				?>
				<script>
					jQuery(function ($) {
					  $('body').on('click', '.add-param', function (e) {
					  	e.preventDefault();
					  	$.ajax({
					  		url: ajaxurl,
					  		type: 'post',
					  		data: {
					  			action: 'new_param'
					  		},
					  		success: function( data ) {
					  			$('.all-params').append(data);
					  		}
					  	});
					  }).on('click', '.remove-param', function (e) {
					  	e.preventDefault();
					    $(this).parent().remove();
					  });
					});
				</script>
				<button class="add-param">Add new param</button>
				<div class="all-params">
					<?php
					foreach ( get_option( 'paramscraper-input' ) as $param ) {
						echo self::render_input( $param );
					}
					?>
				</div>
				<input type="submit" name="submit" value="Save"></input>
				<input name="action" value="update" type="hidden"></input>
				<input name="page_options" value="paramscraper-input" type="hidden"></input>
			</form>
		</div>
	<?php
	return;
	}

	/**
	 * Get and echo input
	 */
	public function get_field() {
		echo self::render_input();
		wp_die();
	}

	/**
	 * Input field
	 *
	 * @param  string $val value.
	 * @return string
	 */
	public function render_input( $val ) { 

		return <<<HTML
		<div class="param-container">
			<label>
				<input type="text" id="paramscraper-input" name="paramscraper-input[]" value="{$val}">
			</label>
			<button class="remove-param">Remove</button>
		</div>
HTML;

	}

	/**
	 * Set params based on inputs
	 */
	public function set_params() {

		if ( ! session_id() ) {
			session_start();
		}

		$params = [];
		foreach ( get_option( 'paramscraper-input' ) as $param ) {
			if ( isset( $_GET[$param] ) && ! empty( $_GET[$param] ) ) {
				$params[$param] = $_GET[$param];
			}
		}

		if ( count( $params ) ) {
			$_SESSION['paramscraper_params'] = $params;
		}
	}

	/**
	 * Get param from session using shortcode
	 *
	 * @param  string $atts [description].
	 * @return string       [description]
	 */
	public static function get_param( $atts ) {

		$atts = shortcode_atts( array(
			'param' => $param,
		), $atts, 'get_param' );

		if ( isset( $_SESSION['paramscraper_params'][$atts['param']] ) ) {
			return $_SESSION['paramscraper_params'][$atts['param']];
		}
	}

	/**
	 * Get params from session
	 *
	 * @return array  array of params
	 */
	public static function get_params() {

		if ( ! session_id() ) {
			session_start();
		}

		if ( ! isset( $_SESSION['paramscraper_params'] ) ) {
			return [];
		}

		$params = [];
		foreach ( get_option( 'paramscraper-input' ) as $param ) {
			if ( isset( $_SESSION['paramscraper_params'][$param] ) && ! empty( $_SESSION['paramscraper_params'][$param] ) ) {
				$params[$param] = $_SESSION['paramscraper_params'][$param];
			}
		}
		return $params;
	}

}
$wp_paramscraper = new WpParamscraper();
