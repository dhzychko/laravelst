<?php
/**
 * Plugin Name: Cost Calculator
 * Description: Cost Calculator by BoldThemes
 * Version: 2.2.3
 * Author: BoldThemes
 * Author URI: http://codecanyon.net/user/boldthemes
 */
require_once( 'bold-builder-light/bold-builder-light.php' );
class BT_CC_Root {
	static $builder;
}
// BB Light
BT_CC_Root::$builder = new BTBB_Light(
	array(
		'slug' => 'bt-cost-calculator',
		'single_name' => esc_html__( 'Cost Calculator', 'bt-cost-calculator' ),
		'plural_name' => esc_html__( 'Cost Calculators', 'bt-cost-calculator' ),
		'icon' => 'dashicons-plus-alt',
		'home_url' => 'https://codecanyon.net/item/cost-calculator-wordpress-plugin/12778927',
		'doc_url' => 'http://documentation.bold-themes.com/cost-calculator',
		'support_url' => 'https://boldthemes.ticksy.com',
		'shortcode' => 'bt_cc'
	)
);
/***/
function bt_cc_enqueue() {
	wp_enqueue_script( 'bt_cc_dd', plugins_url( 'jquery.dd.js', __FILE__ ), array( 'jquery' ) );
	wp_enqueue_script( 'bt_cc_main', plugins_url( 'cc.main.js', __FILE__  ), array( 'jquery' ) );
	$js = '';
	$js .= 'window.bt_cc_translate = [];';
	$js .= 'window.bt_cc_translate[\'prev\'] = \'' . esc_html__( 'Prev', 'bt-cost-calculator' ) . '\';';
	$js .= 'window.bt_cc_translate[\'next\'] = \'' . esc_html__( 'Next', 'bt-cost-calculator' ) . '\';';
	$js .= 'window.bt_cc_translate[\'su\'] = \'' . esc_html__( 'Su', 'bt-cost-calculator' ) . '\';';
	$js .= 'window.bt_cc_translate[\'mo\'] = \'' . esc_html__( 'Mo', 'bt-cost-calculator' ) . '\';';
	$js .= 'window.bt_cc_translate[\'tu\'] = \'' . esc_html__( 'Tu', 'bt-cost-calculator' ) . '\';';
	$js .= 'window.bt_cc_translate[\'we\'] = \'' . esc_html__( 'We', 'bt-cost-calculator' ) . '\';';
	$js .= 'window.bt_cc_translate[\'th\'] = \'' . esc_html__( 'Th', 'bt-cost-calculator' ) . '\';';
	$js .= 'window.bt_cc_translate[\'fr\'] = \'' . esc_html__( 'Fr', 'bt-cost-calculator' ) . '\';';
	$js .= 'window.bt_cc_translate[\'sa\'] = \'' . esc_html__( 'Sa', 'bt-cost-calculator' ) . '\';';
	$js .= 'window.bt_cc_translate[\'january\'] = \'' . esc_html__( 'January', 'bt-cost-calculator' ) . '\';';
	$js .= 'window.bt_cc_translate[\'february\'] = \'' . esc_html__( 'February', 'bt-cost-calculator' ) . '\';';
	$js .= 'window.bt_cc_translate[\'march\'] = \'' . esc_html__( 'March', 'bt-cost-calculator' ) . '\';';
	$js .= 'window.bt_cc_translate[\'april\'] = \'' . esc_html__( 'April', 'bt-cost-calculator' ) . '\';';
	$js .= 'window.bt_cc_translate[\'may\'] = \'' . esc_html__( 'May', 'bt-cost-calculator' ) . '\';';
	$js .= 'window.bt_cc_translate[\'june\'] = \'' . esc_html__( 'June', 'bt-cost-calculator' ) . '\';';
	$js .= 'window.bt_cc_translate[\'july\'] = \'' . esc_html__( 'July', 'bt-cost-calculator' ) . '\';';
	$js .= 'window.bt_cc_translate[\'august\'] = \'' . esc_html__( 'August', 'bt-cost-calculator' ) . '\';';
	$js .= 'window.bt_cc_translate[\'september\'] = \'' . esc_html__( 'September', 'bt-cost-calculator' ) . '\';';
	$js .= 'window.bt_cc_translate[\'october\'] = \'' . esc_html__( 'October', 'bt-cost-calculator' ) . '\';';
	$js .= 'window.bt_cc_translate[\'november\'] = \'' . esc_html__( 'November', 'bt-cost-calculator' ) . '\';';
	$js .= 'window.bt_cc_translate[\'december\'] = \'' . esc_html__( 'December', 'bt-cost-calculator' ) . '\';';
	wp_add_inline_script( 'bt_cc_main', $js );
	wp_enqueue_style( 'bt_cc_style', plugins_url( 'style.min.css', __FILE__ ) );
}
add_action( 'wp_enqueue_scripts', 'bt_cc_enqueue' );
global $bt_recaptcha;
$bt_recaptcha = false;
function bt_enqueue_recaptcha() {
	global $bt_recaptcha;
	if ( ! $bt_recaptcha ) {
		?>
		<script>
			var BTCaptchaCallback = function() {
				jQuery( '.g-rec' ).each(function() {
					var widget_id = grecaptcha.render( jQuery( this ).attr( 'id' ), { 'sitekey' : jQuery( this ).data( 'sk' ) } );
					jQuery( this ).data( 'widget_id', widget_id );
				});
			};
		</script>
		<?php
		echo '<script src="https://www.google.com/recaptcha/api.js?onload=BTCaptchaCallback&render=explicit" async defer></script>';
		$bt_recaptcha = true;
	}
}
/**
 * Load plugin textdomain.
 *
 * @since 1.0.0
 */
function bt_cc_load_textdomain() {
  load_plugin_textdomain( 'bt-cost-calculator', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' ); 
}
add_action( 'plugins_loaded', 'bt_cc_load_textdomain' );
// [bt_cost_calculator]
class bt_cost_calculator {
	public static $date_text;
	public static $time_text;
	static function init() {
		add_shortcode( 'bt_cost_calculator', array( __CLASS__, 'handle_shortcode' ) );
		add_action( 'wp_ajax_bt_cc', array( __CLASS__, 'bt_cc_callback' ) );
		add_action( 'wp_ajax_nopriv_bt_cc', array( __CLASS__, 'bt_cc_callback' ) );
		add_action( 'wp_ajax_bt_cc_paypal', array( __CLASS__, 'bt_cc_paypal_callback' ) );
		add_action( 'wp_ajax_nopriv_bt_cc_paypal', array( __CLASS__, 'bt_cc_paypal_callback' ) );
	}
	static function bt_cc_paypal_callback() {
		$name = $_POST['name'];
		$email = strip_tags( $_POST['email'] );
		$phone = $_POST['phone'];
		$address = $_POST['address'];
		$message = $_POST['message'];
		$total = $_POST['total'];

		global $wpdb;
		$check = $wpdb->insert(
			'rmje_calculcator_base',
			array(
				'form_name' => $name,
				'form_email' => $email,
				'form_phone' => $phone,
				'form_address' => $address,
				'form_message' => $message,
				'form_summ' => $total,
			)
		);
		echo $check;
	}

	static function bt_cc_callback() {
		check_ajax_referer( 'bt_cc_nonce', 'bt_cc_nonce' );
		$recaptcha_response = $_POST['recaptcha_response'];
		$recaptcha_secret = $_POST['recaptcha_secret'];
		$admin_email = $_POST['admin_email'];
		$email_client = $_POST['email_client'];
		$url_confirmation = $_POST['url_confirmation'];
		$currency = $_POST['currency'];
		$currency_after = $_POST['currency_after'];
		$currency_space = $_POST['currency_space'];
		$email_confirmation = $_POST['email_confirmation'];
		$subject = urldecode( $_POST['subject'] );
		$email_header = $_POST['email_header'];
		$email_footer = $_POST['email_footer'];
		$quote = urldecode( $_POST['quote'] );
		$quote_simple = $_POST['quote_simple'];
		$total_quote = $_POST['total_quote'];
		$total = $_POST['total'];
		$total_text = $_POST['total_text'];
		$CollectionsEmail = $_POST['CollectionsEmail'];
		$name = $_POST['name'];
		$email = strip_tags( $_POST['email'] );
		$phone = $_POST['phone'];
		$address = $_POST['address'];
		$date = isset($_POST['date'])?$_POST['date']:'';
		$time = isset($_POST['time'])?$_POST['time']:'';
		$date_text = isset($_POST['date_text'])?$_POST['date_text']:'';
		$time_text = isset($_POST['time_text'])?$_POST['time_text']:'';
		$message = $_POST['message'];
		$email_gdpr = $_POST['email_gdpr'];
		$email_gdpr_text = $_POST['email_gdpr_text'];
		$email_gdpr_not_text = $_POST['email_gdpr_not_text'];
//		if ( $recaptcha_response == '' && $recaptcha_secret != '' ) {
//			die();
//		}
//
//		if ( $recaptcha_response != '' && $recaptcha_secret != '' ) {
//			$recaptcha_post = wp_remote_post( 'https://www.google.com/recaptcha/api/siteverify', array( 'body' => array( 'secret' => $recaptcha_secret, 'response' => $recaptcha_response ) ) );
//			if ( is_wp_error( $recaptcha_post ) ) {
//				echo 'recaptcha post error';
//				die();
//			} else {
//				$json = json_decode( $recaptcha_post['body'] );
//				if ( $json->success != 1 ) {
//					echo 'recaptcha response false';
//					die();
//				}
//			}
//		}
		
		$total_curr = $total;
		// ADMIN EMAIL CONTENT
		$message_to_admin = '<html><body>' . "\r\n";
			$message_to_admin .= '<table style="width:100%" cellspacing="0">' . "\r\n";
			if ( $quote != '' ) $message_to_admin .= $quote;
			if ( $currency != '' ) {
				if ( $currency_after == 'yes' ) {
					if ( $currency_space == 'yes' ) {
						$total_curr = $total . ' ' . $currency;
					} else {
						$total_curr = $total . $currency;
					}
				} else {
					if ( $currency_space == 'yes' ) {
						$total_curr = $currency . ' ' . $total;
					} else {
						$total_curr = $currency . $total;
					}
				}
			}
			$message_to_admin .= '<tr><td style="font-weight:bold;border-top:1px solid #888;padding:.5em;">' . $total_text . '</td><td style="text-align:right;font-weight:bold;border-top:1px solid #888;padding:.5em;">' . $total_curr . '</td></tr>' . "\r\n";
			$message_to_admin .= '</table>' . "\r\n";
			$message_to_admin .= '<br>' . "\r\n";
			if ( $name != '' ) $message_to_admin .= '<div style="padding:.5em;"><b>' . esc_html__( 'Name', 'bt-cost-calculator' ) . '</b>: ' . stripslashes( $name ) . '</div>' . "\r\n";
			if ( $email != '' ) $message_to_admin .= '<div style="padding:.5em;"><b>' . esc_html__( 'Email', 'bt-cost-calculator' ) . '</b>: <a href="mailto:' . $email . '">' . $email . '</a></div>' . "\r\n";
			if ( $phone != '' ) $message_to_admin .= '<div style="padding:.5em;"><b>' . esc_html__( 'Phone', 'bt-cost-calculator' ) . '</b>: ' . $phone . '</div>' . "\r\n";
			if ( $address != '' ) $message_to_admin .= '<div style="padding:.5em;"><b>' . esc_html__( 'Address', 'bt-cost-calculator' ) . '</b>: ' . stripslashes( $address ) . '</div>' . "\r\n";
			if ( $date != '' ) $message_to_admin .= '<div style="padding:.5em;"><b>' . $date_text . '</b>: ' . $date . '</div>' . "\r\n";
			if ( $time != '' ) $message_to_admin .= '<div style="padding:.5em;"><b>' . $time_text . '</b>: ' . $time . '</div>' . "\r\n";
			if ( $message != '' ) $message_to_admin .= '<div style="padding:.5em;"><b>' . esc_html__( 'Message', 'bt-cost-calculator' ) . '</b>: ' . stripslashes( $message ) . '</div>' . "\r\n";
		$message_to_admin .= '</body></html>';
		// CLIENT EMAIL CONTENT
		$message_to_client = '<html><body>' . "\r\n";
			if ( $email_header != '' ) {
				$message_to_client .= base64_decode( $email_header );
			}
			$message_to_client .= '<table style="width:100%" cellspacing="0">' . "\r\n";
			if ( $quote != '' ) $message_to_client .= $quote;
			if ( $currency != '' ) {
				if ( $currency_after == 'yes' ) {
					if ( $currency_space == 'yes' ) {
						$total_curr = $total . ' ' . $currency;
					} else {
						$total_curr = $total . $currency;
					}
				} else {
					if ( $currency_space == 'yes' ) {
						$total_curr = $currency . ' ' . $total;
					} else {
						$total_curr = $currency . $total;
					}
				}
			}
			$message_to_client .= '<tr><td style="font-weight:bold;border-top:1px solid #888;padding:.5em;">' . $total_text . '</td><td style="text-align:right;font-weight:bold;border-top:1px solid #888;padding:.5em;">' . $total_curr . '</td></tr>' . "\r\n";
			$message_to_client .= '</table>' . "\r\n";
			$message_to_client .= '<br>' . "\r\n";
			if ( $name != '' ) $message_to_client .= '<div style="padding:.5em;"><b>' . esc_html__( 'Name', 'bt-cost-calculator' ) . '</b>: ' . stripslashes( $name ) . '</div>' . "\r\n";
			if ( $email != '' ) $message_to_client .= '<div style="padding:.5em;"><b>' . esc_html__( 'Email', 'bt-cost-calculator' ) . '</b>: <a href="mailto:' . $email . '">' . $email . '</a></div>' . "\r\n";
			if ( $phone != '' ) $message_to_client .= '<div style="padding:.5em;"><b>' . esc_html__( 'Phone', 'bt-cost-calculator' ) . '</b>: ' . $phone . '</div>' . "\r\n";
			if ( $address != '' ) $message_to_client .= '<div style="padding:.5em;"><b>' . esc_html__( 'Address', 'bt-cost-calculator' ) . '</b>: ' . stripslashes( $address ) . '</div>' . "\r\n";
			if ( $date != '' ) $message_to_client .= '<div style="padding:.5em;"><b>' . $date_text . '</b>: ' . $date . '</div>' . "\r\n";
			if ( $time != '' ) $message_to_client .= '<div style="padding:.5em;"><b>' . $time_text . '</b>: ' . $time . '</div>' . "\r\n";
			if ( $message != '' ) $message_to_client .= '<div style="padding:.5em;"><b>' . esc_html__( 'Message', 'bt-cost-calculator' ) . '</b>: ' . stripslashes( $message ) . '</div>' . "\r\n";
			if ( $email_footer != '' ) {
				$message_to_client .= base64_decode( $email_footer );
			}
		$message_to_client .= '</body></html>';
		//$message_to_admin = quoted_printable_encode( $message_to_admin );
		// SUBJECT
		$s = $subject;
		if ( $name != '' ) $s = $s . ' / ' . $name;
		try{
//			$r = true;
//			if ( $email_client == 'yes' && $email != '' &&  $email_confirmation == 'yes' ) {
//				$headers = "From: " . $admin_email . "\r\n";
//				$headers .= "MIME-Version: 1.0\r\n";
//				$headers .= "Content-Type: text/html; charset=UTF-8\r\n";
//				//$headers .= "Content-Transfer-Encoding: quoted-printable";
//				$r = mail( $email, $s, $message_to_client, $headers );
//			}
//			$headers = '';
//			//if ( $email != '' ) $headers = "From: " . $email . "\r\n"; // todo: email validation
//			$headers .= "Reply-to: " . $email . "\r\n";
//			$headers .= "MIME-Version: 1.0\r\n";
//			$headers .= "Content-Type: text/html; charset=UTF-8\r\n";
//			//$headers .= "Content-Transfer-Encoding: quoted-printable";
//			$r1 = mail( $admin_email, $s, $message_to_admin, $headers );
//			if ( $r && $r1 ) echo 'ok';

			$quote_line = [];
			foreach( $quote_simple as $key=>$quote_single ){
				if( $quote_single[0] == 'Destination City:' ){
					$destination_city = $quote_single[1];
				}else{
					//if($quote_single[1]){
						$quote_line[$key]['name'] = $quote_single[0];
						$quote_line[$key]['value'] = $quote_single[1];
					//}
				}
			}

			$quote_line = json_encode($quote_line);

			global $wpdb;
			$wpdb->insert(
				'rmje_calculcator_base',
				array(
					'form_name' => $name,
					'form_email' => $email,
					'form_phone' => $phone,
					'form_address' => $address,
					'form_message' => $message,
					'form_summ' => $total,
					'quote_line' => $quote_line,
					'destination_city' => $destination_city,
					'total_quote' => $total_quote,
					)
			);

			//$mylink = $wpdb->get_row( "SELECT * FROM 'rmje_calculcator_base' WHERE id = $save_id" );
			//var_dump($quote_line);
			//echo '<pre>' . print_r( $myarray, true ) . '</pre>';
		} catch ( Exception $e ) {
			echo $e->getMessage();
		}

		die();
	}
	static function handle_shortcode( $atts, $content ) {
		extract( shortcode_atts( array(
			'admin_email'        => '',
			'cf7_id'        	 => '',
			'subject'            => '',
			'email_client'       => '',
			'email_confirmation' => '',
			'email_header'       => '',
			'email_footer'       => '',
			'url_confirmation'	 => '',
			'date_format'        => 'mm/dd/yy',
			'time_start'         => '',
			'time_end'           => '',
			'time_format'        => '24',
			'currency'           => '',
			'currency_after'     => '',
			'currency_space'     => 'yes',
			'hide_total'         => '',
			'm_name'             => '',
			'm_email'            => '',
			'm_phone'            => '',
			'm_address'          => '',
			'm_date'             => '',
			'm_time'             => '',
			'm_message'          => '',
			'show_next'			 => '',
			'next_text'			 => esc_html__( 'Next', 'bt-cost-calculator' ),
			'no_next'            => '',
			'accent_color'       => '',
			'show_booking'       => '',
			'date_text'          => esc_html__( 'Preferred Service Date', 'bt-cost-calculator' ),
			'time_text'          => esc_html__( 'Preferred Service Time', 'bt-cost-calculator' ),
			'total_text'         => esc_html__( 'Total', 'bt-cost-calculator' ),
			'total_format'       => 'currency_1',
			'total_decimals'     => '2',
			'rec_site_key'       => '',
			'rec_secret_key'     => '',
			'paypal_email'       => '',
			'paypal_cart_name'   => '',
			'paypal_currency'    => '',
			'email_gdpr'        => '',
			'email_gdpr_text'   => '',
			'email_gdpr_not_text' => '',
			'el_class'           => '',
			'el_style'           => ''
		), $atts, 'bt_cost_calculator' ) );
		bt_cost_calculator::$date_text = $date_text;
		bt_cost_calculator::$time_text = $time_text;
		wp_enqueue_script( 'jquery-ui' );
		wp_enqueue_script( 'jquery-ui-datepicker' );
		wp_enqueue_script( 'jquery-ui-slider' );
		wp_enqueue_script( 'bt_touch-punch_js', plugins_url( 'jquery.ui.touch-punch.min.js', __FILE__ ), array( 'jquery-ui-slider' ) );
		$css_class = uniqid( 'c' );
		if ( $accent_color != '' )  { $el_class .= ' btQuoteBookingWithColor'; } 
		else { $el_class .= ' btQuoteBookingWithoutColor'; }
		$proxy = new Cost_Proxy( array(
			'accent_color' => $accent_color,
			'css_class' => $css_class,
		) );
		add_action( 'wp_footer', array( $proxy, 'js_init' ), 20 );
		$style_attr = '';
		if ( $el_style != '' ) {
			$style_attr = 'style="' . $el_style . '"';
		}
		if ( $next_text == '' ) $next_text = esc_html__( 'Next', 'bt-cost-calculator' );
		if ( $m_name != '' ) $m_name = ' ' . 'btContactField' . $m_name;
		if ( $m_email != '' ) $m_email = ' ' . 'btContactField' . $m_email;
		if ( $m_phone != '' ) $m_phone = ' ' . 'btContactField' . $m_phone;
		if ( $m_address != '' ) $m_address = ' ' . 'btContactField' . $m_address;
		if ( $m_message != '' ) $m_message = ' ' . 'btContactField' . $m_message;
		if ( $m_date != '' ) $m_date = ' ' . 'btContactField' . $m_date;
		if ( $m_time != '' ) $m_time = ' ' . 'btContactField' . $m_time;
		$total_next_wrapper_style = '';	
		$output = '<div class="btQuoteBooking ' . $el_class . ' ' . $css_class . '" ' . $style_attr . ' data-admin_email="' .  $admin_email. '" data-cf7_id="' .  $cf7_id. '" data-email_client="' . $email_client . '" data-email_gdpr="' . $email_gdpr . '" data-email_gdpr_text="' . $email_gdpr_text . '" data-email_gdpr_not_text="' . $email_gdpr_not_text . '" data-currency="' . $currency . '" data-currency_after="' . $currency_after . '" data-currency_space="' . $currency_space . '" data-url_confirmation="' . $url_confirmation . '" data-url_ajax="' . admin_url( 'admin-ajax.php' ) . '" data-subject="' . $subject . '" data-email_header="' . $email_header . '" data-email_footer="' . $email_footer . '" data-date_text="' . $date_text . '" data-time_text="' . $time_text . '" data-message_please_wait="' . esc_html__( 'Please wait...', 'bt-cost-calculator' ) . '" data-message_success="'. esc_html__( 'Thank you, we will contact you soon!', 'bt-cost-calculator' ) .'" data-message_error="' . esc_html__( 'Error! Please try again later.', 'bt-cost-calculator' ) . '" data-message_mandatory="' .  esc_html__( 'Please fill out all required fields.', 'bt-cost-calculator' ) . '" data-rec_secret_key="' . $rec_secret_key . '" data-total_text="' . $total_text . '" data-total_format="' . $total_format . '" data-total_decimals="' . $total_decimals . '" data-date_format="' . $date_format . '" data-bt_cc_nonce="' . wp_create_nonce( 'bt_cc_nonce' ) . '"><div class="btQuoteBookingWrap">';
			if ( $no_next == 'yes' ) {
				$output .= '<div class="btQuoteBookingForm">';
			} else {
				$output .= '<div class="btQuoteBookingForm btActive">';
			}
				$output .= wptexturize( do_shortcode( $content ) );
				$output .= '<div class="btTotalNextWrapper"' . $total_next_wrapper_style . '>';
					if ( $hide_total != 'yes' ) {
						if ( $currency_after == 'yes' ) {
							if ( $currency_space == 'yes' ) {
								$currency = '&nbsp;' . $currency;
							}
							$output .= '<div class="btQuoteTotal currencyAfter"><span class="btQuoteTotalText">' . $total_text . '</span><span class="btQuoteTotalCalc"></span><span class="btQuoteTotalCurrency">' . $currency . '</span></div>';
						} else {
							if ( $currency_space == 'yes' ) {
								$currency = $currency . '&nbsp;';
							}
							$output .= '<div class="btQuoteTotal"><span class="btQuoteTotalText">' . $total_text . '</span><span class="btQuoteTotalCurrency">' . $currency . '</span><span class="btQuoteTotalCalc"></span></div>';
						}
					}

					$output .= '<div class="boldBtn btnAccent btnSmall btnIco"><button type="submit" class="btContactNext">' . $next_text . '</button></div>';

				$output .= '</div>';
			$output .= '</div>';
			//if ( $paypal_email == '' && $show_next != '' ) {
			if ( $show_next != '' ) {
				if ( $cf7_id == '' || ! shortcode_exists( 'contact-form-7' ) ) {
					if ( $no_next == 'yes' ) {
						$output .= '<div class="btTotalQuoteContactGroup btActive">';
					} else {
						$output .= '<div class="btTotalQuoteContactGroup">';
					}
						$output .= '<div class="btQuoteContact"><form action="#">';

						$output .= '<div class="btQuoteItem fullWidth' . $m_collection_date . '"><input type="text" class="RequestedCollectionDate" placeholder="' . esc_html__( '*Requested collection date', 'bt-cost-calculator' ) . '" autocomplete="name"></div>';

							$output .= '<div class="btQuoteItem fullWidth' . $m_name . '"><input type="text" class="CollectionsName" placeholder="' . esc_html__( '*Name', 'bt-cost-calculator' ) . '" autocomplete="name"></div>';

							$output .= '<div class="btQuoteItem' . $m_email . '"><input type="text" class="CollectionsEmail btContactField" placeholder="' . esc_html__( '*Email', 'bt-cost-calculator' ) . '" autocomplete="email"></div>';

							$output .= '<div class="btQuoteItem' . $m_phone . '"><input type="text" class="btContactPhone btContactField" placeholder="' . esc_html__( '*Contact tel', 'bt-cost-calculator' ) . '" autocomplete="tel"></div>';

							$output .= '<div class="btQuoteItem fullWidth' . $m_address_1 . '"><input type="text" class="btContactAddress1 btContactField" placeholder="' . esc_html__( '*Collection address line 1', 'bt-cost-calculator' ) . '"></div>';

							$output .= '<div class="btQuoteItem fullWidth' . $m_address_2 . '"><input type="text" class="btContactAddress2 btContactField" placeholder="' . esc_html__( 'Collection address line 2', 'bt-cost-calculator' ) . '"></div>';

							$output .= '<div class="btQuoteItem' . $m_city . '"><input type="text" class="btContactCity btContactField" placeholder="' . esc_html__( '*City', 'bt-cost-calculator' ) . '"></div>';

							$output .= '<div class="btQuoteItem' . $m_postcode . '"><input type="text" class="btContactPostcode btContactField" placeholder="' . esc_html__( '*Postcode', 'bt-cost-calculator' ) . '"></div>';

							$output .= '<div class="btQuoteItem fullWidth' . $m_country . '"><span class="label_title">Destination detail</span><select class="btContactCountry btContactField" placeholder="' . esc_html__( 'Country (AU/NZ)', 'bt-cost-calculator' ) . '"><option>Select Country (AU/NZ)</option><option>Australia</option><option>New Zeland</option></select><p>Laws in New Zeland stipulate that the person sending the shipment must be the person receiving it so please ensure this is the case</p></div>';

							$output .= '<div class="btQuoteItem fullWidth' . $m_name_1 . '"><input type="text" class="btContactName btContactField" placeholder="' . esc_html__( '*Name', 'bt-cost-calculator' ) . '" autocomplete="name"></div>';

							$output .= '<div class="btQuoteItem' . $m_email_1 . '"><input type="text" class="btContactEmail btContactField" placeholder="' . esc_html__( '*Email', 'bt-cost-calculator' ) . '" autocomplete="email"></div>';

							$output .= '<div class="btQuoteItem' . $m_phone_1 . '"><input type="text" class="btContactPhone btContactField" placeholder="' . esc_html__( '*Contact tel', 'bt-cost-calculator' ) . '" autocomplete="tel"></div>';

							$output .= '<div class="btQuoteItem fullWidth' . $m_address_1_1 . '"><input type="text" class="btContactAddress1 btContactField" placeholder="' . esc_html__( '*Collection address line 1', 'bt-cost-calculator' ) . '"></div>';

							$output .= '<div class="btQuoteItem fullWidth' . $m_address_2_1 . '"><input type="text" class="btContactAddress2 btContactField" placeholder="' . esc_html__( 'Collection address line 2', 'bt-cost-calculator' ) . '"></div>';

							$output .= '<div class="btQuoteItem' . $m_city_1 . '"><input type="text" class="btContactCity btContactField" placeholder="' . esc_html__( '*City', 'bt-cost-calculator' ) . '"></div>';

							$output .= '<div class="btQuoteItem' . $m_postcode_1 . '"><input type="text" class="btContactPostcode btContactField" placeholder="' . esc_html__( '*Postcode', 'bt-cost-calculator' ) . '"></div>';

							$output .= '<div class="btQuoteItem' . $m_message . ' btQuoteItemFullWidth"><textarea class="btContactMessage btContactField" placeholder="' . esc_html__( 'Message', 'bt-cost-calculator' ) . '"></textarea></div>';

							$output .= '<div class="btQuoteItem creditCardDetails fullWidth"><span class="label_title">Enter credit card details</span>';

							$output .= '<div class="btQuoteItem' . $m_card_name . '"><input type="text" class="btCardName btContactField" placeholder="' . esc_html__( 'Card name', 'bt-cost-calculator' ) . '"></div>';

							$output .= '<div class="btQuoteItem' . $m_expiry_date . '"><input type="text" class="btExpiryDate btContactField" placeholder="' . esc_html__( 'Expiry date', 'bt-cost-calculator' ) . '"></div>';

							$output .= '<div class="btQuoteItem' . $m_card_number . '"><input type="text" class="btCardNumber btContactField" placeholder="' . esc_html__( 'Card number', 'bt-cost-calculator' ) . '"></div>';

							$output .= '<div class="btQuoteItem' . $m_cvc . '"><input type="text" class="btCVC btContactField" placeholder="' . esc_html__( 'CVC', 'bt-cost-calculator' ) . '"></div>';

							$output .= '<div class="btQuoteItem fullWidth' . $m_accept . '"><label for="acceptTerms" class="checkboxLabel"><input type="checkbox" id="acceptTerms" class="acceptTerms btContactField"><span>I accept the terms & conditions</span></label></div>';

							$output .= '</div>';

							if ( $show_booking != '' ) {
								$output .= '<div class="btQuoteItem' . $m_date . '"><input type="text" class="btContactDate btContactField" placeholder="' . $date_text . '"></div>';
								$output .= '<div class="btQuoteItem' . $m_time . '">';
									$output .= '<div class="btContactTime btContactField btDropDown"></div>';
									if ( $time_start == '' ) $time_start = 0;
									if ( $time_end == '' ) $time_end = 23;
									$proxy = new CostTime_Proxy( $time_start, $time_end, $time_format, $time_text, $css_class );
									add_action( 'wp_footer', array( $proxy, 'js_init' ), 20 );
								$output .= '</div>';
							}
							
							if ( $email_client == 'yes' && $email_confirmation == 'yes' ) {
								$id = uniqid();
								$output .= '<div class="bt_cc_email_confirmation_container"><input id="' . $id . '" class="bt_cc_email_confirmation" type="checkbox" value="yes"><label for="' . $id . '">' . esc_html__( 'Email me quote!', 'bt-cost-calculator' ) . '</label></div>';
							}	
							if ( $email_gdpr == 'yes' && $email_gdpr_text != '' ) {
								$id = uniqid();
								$output .= '<div class="bt_cc_email_confirmation_container bt_cc_gdpr_confirmation_container"><input id="' . $id . '" class="bt_cc_email_gdpr" type="checkbox" value="yes"><label for="' . $id . '">' . $email_gdpr_text . '</label></div>';
							}	
							if ( $rec_site_key != '' && $rec_secret_key != '' ) {
								$id = uniqid();
								$output .= '<div id=' . $id . ' class="g-rec" data-sk="' . $rec_site_key . '"></div>';
								add_action( 'wp_footer', 'bt_enqueue_recaptcha' );
							}
							$output .= '<div class="boldBtn btnAccent btnSmall btnIco"><button type="submit" class="btContactSubmit">' . esc_html__( 'Pay Now', 'bt-cost-calculator' ) . '</button><div class="bookNow">EMAIL ME</div></div>';

							$output .= '<div class="btSubmitMessage"></div>';
						$output .= '</form></div><!-- btQuoteContact -->';

						$output .= '<div class="btPayPalButtonWrap"><span class="payPalTitle">or pay now with paypal</span><div class="btPayPalButton" style="background-image:url(' . plugin_dir_url( __FILE__ ) . 'paypal.png);"></div><form class="btPayPalForm" action="https://www.paypal.com/cgi-bin/webscr" method="post">
								<input type="hidden" name="cmd" value="_xclick">
								<input type="hidden" name="upload" value="1">
								<input type="hidden" name="amount" class="form_amount" value="20">
								<input type="hidden" name="business" value="' . $paypal_email . '">
								<input type="hidden" name="item_name" value="' . $paypal_cart_name . '">
								<input type="hidden" name="currency_code" value="' . $paypal_currency . '">
								<input type="image" src="' . plugin_dir_url( __FILE__ ) . 'paypal.png" name="submit" alt="PayPal">
								</form>';

					$output .= '</div></div>';
				} else {
					// CF 7 support 
					if ( $no_next == 'yes' ) {
						$output .= '<div class="btTotalQuoteContactGroup btActive">';
					} else {
						$output .= '<div class="btTotalQuoteContactGroup">';
					}
					$output .= '<div class="btQuoteContact btQuoteContactForm7">';
					$output .= do_shortcode('[contact-form-7 id="' . $cf7_id . '"]');
					$output .= '</div></div>';	
				} 
			}
		$output .= '</div>';
		$output .= '</div>';
		return $output;
	}
}
class Cost_Proxy {
	function __construct( $arr ) {
		$this->accent_color = $arr['accent_color'];
		$this->css_class = $arr['css_class'];
	}	
	public function js_init() { ?>
		<script>
			var bt_cc_accent_<?php echo $this->css_class; ?>_init_finished = false;
			document.addEventListener('readystatechange', function() {
				if ( ! bt_cc_accent_<?php echo $this->css_class; ?>_init_finished && typeof(jQuery) !== 'undefined' && ( document.readyState === 'interactive' || document.readyState === 'complete' ) ) {
					var css_class = '<?php echo $this->css_class; ?>';
					var c = jQuery( '.' + css_class );
					setTimeout( function(){ c.css( 'opacity', '1' ); }, 200 );
					var accent_color = '<?php echo $this->accent_color; ?>';
					if ( accent_color != '' ) {
						jQuery( 'head' ).append( '<style>.btQuoteBooking.' + css_class + ' .btContactNext { color: ' + accent_color + ' !important; border: ' + accent_color + ' 2px solid !important; }.btQuoteBooking.' + css_class + '  input[type="text"]:hover, .btQuoteBooking.' + css_class + '  input[type="email"]:hover, .btQuoteBooking.' + css_class + '  input[type="password"]:hover, .btQuoteBooking.' + css_class + '  input[type="url"]:hover, .btQuoteBooking.' + css_class + '  input[type="tel"]:hover, .btQuoteBooking.' + css_class + '  input[type="number"]:hover, .btQuoteBooking.' + css_class + '  input[type="date"]:hover, .btQuoteBooking.' + css_class + '  textarea:hover, .btQuoteBooking.' + css_class + '  .fancy-select .trigger:hover {	box-shadow: 0 0 0 ' + accent_color + ' inset, 0 1px 5px rgba(0,0,0,0.2) !important;}.btQuoteBooking.' + css_class + ' .dd.ddcommon.borderRadius:hover .ddTitleText {	box-shadow: 0 0 0 ' + accent_color + ' inset, 0 1px 5px rgba(0,0,0,0.2) !important;}.btQuoteBooking.' + css_class + '  input[type="text"]:focus, .btQuoteBooking.' + css_class + '  input[type="email"]:focus, .btQuoteBooking.' + css_class + '  input[type="url"]:focus, .btQuoteBooking.' + css_class + '  input[type="tel"]:focus, .btQuoteBooking.' + css_class + '  input[type="number"]:focus, .btQuoteBooking.' + css_class + '  input[type="date"]:focus, .btQuoteBooking.' + css_class + '  textarea:focus, .btQuoteBooking.' + css_class + '  .fancy-select .trigger.open {	box-shadow: 5px 0 0 ' + accent_color + ' inset, 0 2px 10px rgba(0,0,0,0.2) !important;}.btQuoteBooking.' + css_class + ' .dd.ddcommon.borderRadiusTp .ddTitleText, .btQuoteBooking.' + css_class + ' .dd.ddcommon.borderRadiusBtm .ddTitleText {	box-shadow: 5px 0 0 ' + accent_color + ' inset, 0 2px 10px rgba(0,0,0,0.2) !important;}.btQuoteBooking.' + css_class + '  .ui-slider .ui-slider-handle {	background: ' + accent_color + ' !important;}.btQuoteBooking.' + css_class + ' .btQuoteBookingForm .btQuoteTotal {	background: ' + accent_color + ' !important;}.btQuoteBooking.' + css_class + '  .btContactFieldMandatory input:hover, .btQuoteBooking.' + css_class + '  .btContactFieldMandatory textarea:hover {	box-shadow: 0 0 0 1px #AAA inset, 0 0 0 ' + accent_color + ' inset, 0 1px 5px rgba(0,0,0,0.2) !important;}.btQuoteBooking.' + css_class + ' .btContactFieldMandatory .dd.ddcommon.borderRadius:hover .ddTitleText {	box-shadow: 0 0 0 1px #AAA inset, 0 0 0 ' + accent_color + ' inset, 0 1px 5px rgba(0,0,0,0.2) !important;}.btQuoteBooking.' + css_class + '  .btContactFieldMandatory input:focus, .btQuoteBooking.' + css_class + '  .btContactFieldMandatory textarea:focus {	box-shadow: 0 0 0 1px #AAA inset, 5px 0 0 ' + accent_color + ' inset, 0 1px 5px rgba(0,0,0,0.2) !important;}.btQuoteBooking.' + css_class + ' .btContactFieldMandatory .dd.ddcommon.borderRadiusTp .ddTitleText {	box-shadow: 0 0 0 1px #AAA inset, 5px 0 0 ' + accent_color + ' inset, 0 1px 5px rgba(0,0,0,0.2) !important;}.btQuoteBooking.' + css_class + '  .btContactFieldMandatory.btContactFieldError input, .btQuoteBooking.' + css_class + '  .btContactFieldMandatory.btContactFieldError textarea {	border: 1px solid ' + accent_color + ' !important;	box-shadow: 0 0 0 1px ' + accent_color + ' inset !important;}.btQuoteBooking.' + css_class + ' .btContactFieldMandatory.btContactFieldError .dd.ddcommon.borderRadius .ddTitleText {	border: 1px solid ' + accent_color + ' !important;	box-shadow: 0 0 0 1px ' + accent_color + ' inset !important;}.btQuoteBooking.' + css_class + '  .btContactFieldMandatory.btContactFieldError input:hover, .btQuoteBooking.' + css_class + '  .btContactFieldMandatory.btContactFieldError textarea:hover {	box-shadow: 0 0 0 1px ' + accent_color + ' inset, 0 0 0 ' + accent_color + ' inset, 0 1px 5px rgba(0,0,0,0.2) !important;}.btQuoteBooking.' + css_class + ' .btContactFieldMandatory.btContactFieldError .dd.ddcommon.borderRadius:hover .ddTitleText {	box-shadow: 0 0 0 1px ' + accent_color + ' inset, 0 0 0 ' + accent_color + ' inset, 0 1px 5px rgba(0,0,0,0.2) !important;}.btQuoteBooking.' + css_class + '  .btContactFieldMandatory.btContactFieldError input:focus, .btQuoteBooking.' + css_class + '  .btContactFieldMandatory.btContactFieldError textarea:focus {	box-shadow: 0 0 0 1px ' + accent_color + ' inset, 5px 0 0 ' + accent_color + ' inset, 0 1px 5px rgba(0,0,0,0.2) !important;}.btQuoteBooking.' + css_class + ' .btContactFieldMandatory.btContactFieldError .dd.ddcommon.borderRadiusTp .ddTitleText {	box-shadow: 0 0 0 1px ' + accent_color + ' inset, 5px 0 0 ' + accent_color + ' inset, 0 1px 5px rgba(0,0,0,0.2) !important;}.btQuoteBooking.' + css_class + ' .btSubmitMessage {	color: ' + accent_color + ' !important;}.btDatePicker .ui-datepicker-header {	background-color: ' + accent_color + ' !important;}.btQuoteBooking.' + css_class + ' .btContactSubmit {	background-color: ' + accent_color + ' !important;}.btQuoteBooking.' + css_class + ' .btQuoteSwitch.on .btQuoteSwitchInner{background: ' + accent_color + ' !important;}</style>' );
					}
					bt_cc_accent_<?php echo $this->css_class; ?>_init_finished = true;
				}
			}, false);
		</script>
	<?php }
}
class CostTime_Proxy {
	function __construct( $time_start, $time_end, $time_format, $title, $css_class ) {
		$this->time_start = $time_start;
		$this->time_end = $time_end;
		$this->time_format = $time_format;
		$this->title = $title;
		$this->css_class = $css_class;
	}	
	public function js_init() { ?>
		<script>
			var bt_cc_<?php echo $this->css_class; ?>_init_finished = false;
			document.addEventListener('readystatechange', function() { 
				if ( ! bt_cc_<?php echo $this->css_class; ?>_init_finished && typeof(jQuery) !== 'undefined' && ( document.readyState === 'interactive' || document.readyState === 'complete' ) ) {
					var css_class = '<?php echo $this->css_class; ?>';
					var c = jQuery( '.' + css_class );
					var bt_time_ddData = [
					<?php
						echo '{ text:\'' . $this->title . '\', value:\'\' },';
						for ( $i = intval( $this->time_start ); $i <= intval( $this->time_end ); $i++ ) {
							if ( $this->time_format == '24' ) {
								if ( $i < 10 ) $i = '0' . $i;
								echo '{ text: \'' . $i . ':00\', value: \'' . $i . ':00\' },';
							} else {
								if ( $i == 0 ) {
									echo '{ text: \'12:00 AM\', value: \'12:00 AM\' },';
								} else if ( $i < 12 ) {
									if ( $i < 10 ) $i = '0' . $i;
									echo '{ text: \'' . $i . ':00 AM\', value: \'' . $i . ':00 AM\' },';									
								} else if ( $i == 12 ) {
									echo '{ text: \'12:00PM\', value: \'12:00 PM\' },';
								} else {
									$t = $i - 12;
									if ( $t < 10 ) $t = '0' . $t;
									echo '{ text: \'' . $t . ':00 PM\', value: \'' . $t . ':00 PM\' },';									
								}
							}
						}
					?>
					];
					c.find( '.btContactTime' ).msDropDown({
						byJson:{data:bt_time_ddData},
						on:{change:function( data, ui ) {
							var val = data.value;
						}}
					});
					bt_cc_<?php echo $this->css_class; ?>_init_finished = true;
				}
			}, false);
		</script>
	<?php }
}
class CostDD_Proxy {
	function __construct( $dd_id, $items_arr, $title, $img_height, $initial_index ) {
		$this->dd_id = $dd_id;
		$this->items_arr = $items_arr;
		$this->title = $title;
		$this->img_height = $img_height;
		$this->vrednost = "";
		if ( $initial_index > 0 ){
			$items_arr2 =	$items_arr[$initial_index-1];
			$vrednost_arr = explode( ';', $items_arr2 );
			$this->vrednost = $vrednost_arr[1];
		}		
		if ( $initial_index > count($this->items_arr) ){
			$initial_index = count($this->items_arr);
		}
		$this->initial_index = $initial_index > 0 ? $initial_index : 0;
	}	
	public function js_init() { ?>
		<script>
			var bt_cc_<?php echo $this->dd_id; ?>_init_finished = false;
			document.addEventListener('readystatechange', function() { 
				if ( ! bt_cc_<?php echo $this->dd_id; ?>_init_finished && typeof(jQuery) !== 'undefined' && ( document.readyState === 'complete' ) ) {
					var img_height = '<?php echo $this->img_height; ?>';
					if ( img_height != '' ) {
						jQuery( 'head' ).append( '<style>.ddImage img {height:' + img_height + 'px !important;}</style>' );
					}			
					var ddData = [<?php
						echo '{ text:\'' . $this->title . '\', value:\'\' }';             
						foreach ( $this->items_arr as $item ) {
							if ( trim( $item ) != '' ) {
								$arr = explode( ';', $item );
								if ( ! isset( $arr[1] ) ) {
									$arr[1] = '';
								}
								if ( ! isset( $arr[2] ) ) {
									$arr[2] = '';
								}
								if ( ! isset( $arr[3] ) ) {
									$arr[3] = '';
								}
								echo ',{ text: \'' . $arr[0] . '\', value: \'' . floatval( $arr[1] ) . '\', description: \'' . sanitize_text_field( $arr[2] ) . '\', image: \'' . $arr[3] . '\' }';							
							}
						}
					?>];
					var oDropdown = jQuery( '#<?php echo $this->dd_id; ?>' ).msDropDown({
						byJson:{ data:ddData },
						on:{change:function( data, ui ) {
							var val = data.value;
							ui.data( 'value', val );
							bt_cc_eval_conditions( val, jQuery( ui ).closest( '.btQuoteSelect' ).data( 'condition' ) );
							bt_quote_total( jQuery( ui ).closest( '.btQuoteBooking' ) );
							bt_paypal_items( jQuery( ui ).closest( '.btQuoteBooking' ) );
						}}
					}).data('dd');
					if ( oDropdown ) {
						bt_cc_init_dropdown( oDropdown, '#<?php echo $this->dd_id; ?>', <?php echo $this->initial_index; ?> );
					}					
					bt_cc_<?php echo $this->dd_id; ?>_init_finished = true;
				}
			}, false);
		</script>
	<?php }
}
// [bt_cc_item]
class bt_cc_item {
	static function init() {
		add_shortcode( 'bt_cc_item', array( __CLASS__, 'handle_shortcode' ) );
	}
	static function handle_shortcode( $atts, $content ) {
		extract( shortcode_atts( array(
			'name'					=> '',
			'description'			=> '',
			'type'					=> 'text',
			'value'					=> '',           
			'initial_value'			=> '',
			'images'				=> '',
			'img_height'			=> '',
			'condition'				=> '',
			'item_el_id'			=> '',
			'item_el_class'         => '',
			'item_el_style'         => ''
		), $atts, 'bt_cc_item' ) );
		$name = sanitize_text_field( $name );
		$description = sanitize_text_field( $description );
		$type = sanitize_text_field( $type );
		$initial_value = sanitize_text_field( $initial_value );
		$images = sanitize_text_field( $images );
		$img_height = sanitize_text_field( $img_height );
		// $condition = sanitize_text_field( $condition );
		$item_el_id = sanitize_text_field( $item_el_id );
		$item_el_class = sanitize_text_field( $item_el_class );
		$item_el_style = sanitize_text_field( $item_el_style ); 
		$value = str_replace( "'", "\'", $value );		
		$item_id_attr = '';
		if ( $item_el_id == '' ) {
			$item_el_id = uniqid( 'btQuoteItem' );
		} else {
			$item_el_id = $item_el_id;
		}
		$item_id_attr = 'id="' . $item_el_id . '"';
		$item_class = array();
		if ( $item_el_class != '' ) {
			$item_class[] = $item_el_class;
		}
		$item_style_attr = '';
		if ( $item_el_style != '' ) {
			$item_style_attr = 'style="' . $item_el_style . '"';
		}
		$images = explode( ',', $images );
		if ( $condition != '' ) {
			$condition = preg_replace( '/$\R?^/m', "#bt_cc_nl#", $condition );	
			/*$condition = str_replace( '%3E', "&gt;", $condition );
			$condition = str_replace( '%3C', "&lt;", $condition );			
			$condition = sanitize_text_field( $condition );*/
			$condition = str_replace( '#gt#', "&gt;", $condition );
			$condition = str_replace( '#lt#', "&lt;", $condition );
			$condition = strip_tags( $condition );/**/
		}
		if ( $type == 'text' ) {
			$price = round( floatval( $value ), 2 );
			$input = '<input type="text" class="btQuoteText btQuoteElement" data-condition="' . $condition . '" data-price="' . $price . '" value="' . $initial_value . '" data-initial-value="' . $initial_value  . '"/>';
		} else if ( $type == 'select' ) {
			$items_arr = preg_split( '/$\R?^/m', $value );
			$i = 0;
			foreach ( $items_arr as $item ) {
				if ( isset( $images[ $i ] ) ) {
					$items_arr[ $i ] = sanitize_text_field( $items_arr[ $i ] . ';' . wp_get_attachment_thumb_url( $images[ $i ] ) );
				}  
				$i++;
			}
			$dd_id = uniqid() . "W" . rand(100,999);
			//$input = '<div id="' . $dd_id . '" class="btQuoteSelect btContactField btDropDown" data-value="'.$initial_value.'"></div>';
			$input = '<div id="' . $dd_id . '" class="btQuoteSelect btContactField btDropDown btQuoteElement" data-condition="' . $condition . '" data-initial-value="' . $initial_value  . '"></div>';
			$proxy = new CostDD_Proxy( $dd_id, $items_arr, esc_html__( 'Select...', 'bt-cost-calculator' ), $img_height, $initial_value );
			add_action( 'wp_footer', array( $proxy, 'js_init' ), 20 );			
		} else if ( $type == 'slider' ) {    
			$arr = explode( ';', $value );
			$price = round( floatval( $arr[3] ), 2 );
			$offset = isset( $arr[4] ) ? round( floatval( $arr[4] ), 2 ) : 0;
			if ( $initial_value > $arr[1] ){
				$initial_value =  $arr[1];
			}
			if ( $initial_value < $arr[0] ){
				$initial_value =  $arr[0];
			}
			$input = '<div class="btQuoteSlider btQuoteElement" data-value="' . $initial_value . '"  data-initial-value="' . $initial_value  . '" data-min="' . $arr[0] . '" data-max="' . $arr[1] . '" data-step="' . $arr[2] . '" data-price="' . $price . '" data-offset="' . $offset . '" data-condition="' . $condition . '"></div><span class="btQuoteSliderValue">' . $initial_value . '</span>';
		} else if ( $type == 'switch' ) {
			$arr = explode( ';', $value );
			if ( ! is_array( $arr ) || count( $arr ) < 2 ) {
				$arr = array( 0, 1 );
			}
			$class_on = '';
			if ( $initial_value ==  $arr[1] ){
				$class_on = ' on';
			}
			$input = '<div class="btQuoteSwitch btQuoteElement' . $class_on . '" data-off="' . $arr[0] . '" data-on="' . $arr[1] . '" data-condition="' . $condition . '" data-initial-value="' . $initial_value  . '"><div class="btQuoteSwitchInner"></div></div>';
		}
		$output = '<div class="btQuoteItem ' . implode( ' ', $item_class ) . '" ' . $item_id_attr . ' ' . $item_style_attr . '><label>' . $name . '</label>
		<div class="btQuoteItemInput">' . $input;
		if ( $description != '' ) $output .= '<div class="btQuoteItemDescription">' . $description . '</div>';
		$output .= '</div></div>';
		return $output;
	}
}
// [bt_cc_multiply]
class bt_cc_multiply {
	static function init() {
		add_shortcode( 'bt_cc_multiply', array( __CLASS__, 'handle_shortcode' ) );
	}
	static function handle_shortcode( $atts, $content ) {
		extract( shortcode_atts( array(
		), $atts, 'bt_cc_multiply' ) );
		$output = '<div class="btQuoteMBlock">' . wptexturize( do_shortcode( $content ) ) . '</div>';
		return $output;
	}
}
// [bt_cc_group]
class bt_cc_group {
	static function init() {
		add_shortcode( 'bt_cc_group', array( __CLASS__, 'handle_shortcode' ) );
	}
	static function handle_shortcode( $atts, $content ) {
		extract( shortcode_atts( array(
			'eval'         			=> '',
			'paypal_label' 			=> '',
			'item_el_id'			=> '',
			'item_el_class'         => '',
			'item_el_style'         => ''
		), $atts, 'bt_cc_group' ) );
		$item_el_id = sanitize_text_field( $item_el_id );
		$item_el_class = sanitize_text_field( $item_el_class );
		$item_el_style = sanitize_text_field( $item_el_style ); 
		$item_id_attr = '';
		if ( $item_el_id == '' ) {
			$item_el_id = uniqid( 'btQuoteItem' );
		} else {
			$item_el_id = $item_el_id;
		}
		$item_id_attr = 'id="' . $item_el_id . '"';
		$item_class = array();
		if ( $item_el_class != '' ) {
			$item_class[] = $item_el_class;
		}
		$item_style_attr = '';
		if ( $item_el_style != '' ) {
			$item_style_attr = 'style="' . $item_el_style . '"';
		}
		/*$eval = sanitize_text_field( $eval );*/
		$eval = preg_replace( '/$\R?^/m', "", $eval );
		$eval = str_replace( '#lt#', "&lt;", $eval );
		$eval = str_replace( '#gt#', "&gt;", $eval );
		$eval = strip_tags($eval);
		$output = '<div class="btQuoteGBlock ' . implode( ' ', $item_class ) . '" data-eval="' . $eval . '" data-paypal_label="' . $paypal_label . '" ' . $item_id_attr . ' ' . $item_style_attr . '>' . wptexturize( do_shortcode( $content ) ) . '</div>';
		return $output;
	}
}
// [bt_cc_text]
class bt_cc_text {
	static function init() {
		add_shortcode( 'bt_cc_text', array( __CLASS__, 'handle_shortcode' ) );
	}
	static function handle_shortcode( $atts, $content ) {
		extract( shortcode_atts( array(
			'name'					=> '',
			'description'			=> '',
			'value'					=> '',
			'initial_value'			=> '',
			'condition'				=> '',
			'item_el_id'			=> '',
			'item_el_class' 		=> '',
			'item_el_style' 		=> ''
		), $atts, 'bt_cc_text' ) );
		$name = sanitize_text_field( $name );
		$description = sanitize_text_field( $description );
		$type = sanitize_text_field( 'text' );
		$value = sanitize_text_field( $value );
		$initial_value = sanitize_text_field( $initial_value );
		// $condition = sanitize_text_field( $condition );
		$item_el_id = sanitize_text_field( $item_el_id );
		$item_el_class = sanitize_text_field( $item_el_class );
		$item_el_style = sanitize_text_field( $item_el_style );
		if ( $condition != '' ) $condition = preg_replace( '/$\R?^/m', "#bt_cc_nl#", $condition );
		$output = wptexturize( do_shortcode( '[bt_cc_item '
				. 'name="' . $name . '" '
				. 'description="' . $description . '" '
				. 'type="' . $type . '" '
				. 'value="' . $value . '" '
				. 'initial_value="' . $initial_value . '" '
				. 'condition="' . $condition . '" '
				. 'item_el_id="' . $item_el_id . '" '
				. 'item_el_class="' . $item_el_class . '" '
				. 'item_el_style="' . $item_el_style . '" '
				. ']' ) );
		return $output;
	}
}
// [bt_cc_select]
class bt_cc_select {
	static function init() {
		add_shortcode( 'bt_cc_select', array( __CLASS__, 'handle_shortcode' ) );
	}
	static function handle_shortcode( $atts, $content ) {
		extract( shortcode_atts( array(
			'name'					=> '',
			'description'			=> '',
			'value'					=> '',
			'initial_value'			=> '',
			'images'				=> '',
			'img_height'			=> '',
			'condition'				=> '',
			'item_el_id'			=> '',
			'item_el_class' 		=> '',
			'item_el_style' 		=> ''
		), $atts, 'bt_cc_select' ) );
		$name = sanitize_text_field( $name );
		$description = sanitize_text_field( $description );
		$type = sanitize_text_field( 'select' );
		$initial_value = sanitize_text_field( $initial_value );
		$images = sanitize_text_field( $images );
		$img_height = sanitize_text_field( $img_height );
		// $condition = sanitize_text_field( $condition );
		$item_el_id = sanitize_text_field( $item_el_id );
		$item_el_class = sanitize_text_field( $item_el_class );
		$item_el_style = sanitize_text_field( $item_el_style );
		if ( $condition != '' ) $condition = preg_replace( '/$\R?^/m', "#bt_cc_nl#", $condition );
		 $output = wptexturize( do_shortcode( '[bt_cc_item '
				. 'name="' . $name . '" '
				. 'description="' . $description . '" '
				. 'type="' . $type . '" '
				. 'value="' . $value . '" '
				. 'initial_value="' . $initial_value . '" '
				. 'images="' . $images . '" '
				. 'img_height="' . $img_height . '" '
				. 'condition="' . $condition . '" '
				. 'item_el_id="' . $item_el_id . '" '
				. 'item_el_class="' . $item_el_class . '" '
				. 'item_el_style="' . $item_el_style . '" '
				. ']' ) );
		return $output;
	}
}
// [bt_cc_slider]
class bt_cc_slider {
	static function init() {
		add_shortcode( 'bt_cc_slider', array( __CLASS__, 'handle_shortcode' ) );
	}
	static function handle_shortcode( $atts, $content ) {
		extract( shortcode_atts( array(
			'name'					=> '',
			'description'			=> '',
			'value_min'				=> '',
			'value_max'				=> '',
			'value_step'			=> '',
			'value_unit'			=> '',
			'value_offset'			=> '',
			'initial_value'			=> '',
			'condition'				=> '',
			'item_el_id'			=> '',
			'item_el_class' 		=> '',
			'item_el_style' 		=> ''
		), $atts, 'bt_cc_slider' ) );
		$name = sanitize_text_field( $name );
		$description = sanitize_text_field( $description );
		$type = sanitize_text_field( 'slider' );                
		$value_min = sanitize_text_field( $value_min );
		$value_max = sanitize_text_field( $value_max );
		$value_step = sanitize_text_field( $value_step );
		$value_unit = sanitize_text_field( $value_unit );
		$value_offset = sanitize_text_field( $value_offset );                
		$initial_value = sanitize_text_field( $initial_value );
		// $condition = sanitize_text_field( $condition );
		$item_el_id = sanitize_text_field( $item_el_id );
		$item_el_class = sanitize_text_field( $item_el_class );
		$item_el_style = sanitize_text_field( $item_el_style );
		$value = $value_min . ";" . $value_max . ";" . $value_step . ";" . $value_unit . ";" . $value_offset ;
		if ( $condition != '' ) $condition = preg_replace( '/$\R?^/m', "#bt_cc_nl#", $condition );
		$output = wptexturize( do_shortcode( '[bt_cc_item '
				. 'name="' . $name . '" '
				. 'description="' . $description . '" '
				. 'type="' . $type . '" '
				. 'value="' . $value . '" '
				. 'initial_value="' . $initial_value . '" '
				. 'condition="' . $condition . '" '
				. 'item_el_id="' . $item_el_id . '" '
				. 'item_el_class="' . $item_el_class . '" '
				. 'item_el_style="' . $item_el_style . '" '
				. ']' ) );
		return $output;
	}
}
// [bt_cc_switch]
class bt_cc_switch {
	static function init() {
		add_shortcode( 'bt_cc_switch', array( __CLASS__, 'handle_shortcode' ) );
	}
	static function handle_shortcode( $atts, $content ) {
		extract( shortcode_atts( array(
			'name'					=> '',
			'description'			=> '',
			'value_off'				=> '',
			'value_on'				=> '',
			'initial_value'			=> '',
			'condition'				=> '',
			'item_el_id'			=> '',
			'item_el_class' 		=> '',
			'item_el_style' 		=> ''
		), $atts, 'bt_cc_switch' ) );
		$name = sanitize_text_field( $name );
		$description = sanitize_text_field( $description );
		$type = sanitize_text_field( 'switch' );
		$value_off = sanitize_text_field( $value_off );
		$value_on = sanitize_text_field( $value_on );
		$initial_value = sanitize_text_field( $initial_value );
		// $condition = sanitize_text_field( $condition );
		$item_el_id = sanitize_text_field( $item_el_id );
		$item_el_class = sanitize_text_field( $item_el_class );
		$item_el_style = sanitize_text_field( $item_el_style );
		$value = $value_off . ";" . $value_on;
		if ( $condition != '' ) $condition = preg_replace( '/$\R?^/m', "#bt_cc_nl#", $condition );
		$output = wptexturize( do_shortcode( '[bt_cc_item '
				. 'name="' . $name . '" '
				. 'description="' . $description . '" '
				. 'type="' . $type . '" '
				. 'value="' . $value . '" '
				. 'initial_value="' . $initial_value . '" '
				. 'condition="' . $condition . '" '
				. 'item_el_id="' . $item_el_id . '" '
				. 'item_el_class="' . $item_el_class . '" '
				. 'item_el_style="' . $item_el_style . '" '
				. ']' ) );
		return $output;
	}
}
// [bt_cc_raw_html]
class bt_cc_raw_html {
	static function init() {
		add_shortcode( 'bt_cc_raw_html', array( __CLASS__, 'handle_shortcode' ) );
	}
	static function handle_shortcode( $atts, $content ) {
		extract( shortcode_atts( array(
			'raw_html' => '',
		), $atts, 'bt_cc_raw_html' ) );
		$output = base64_decode( $raw_html );
		return do_shortcode($output);
	}
}
// [bt_cc_separator]
class bt_cc_separator {
	static function init() {
		add_shortcode( 'bt_cc_separator', array( __CLASS__, 'handle_shortcode' ) );
	}
	static function handle_shortcode( $atts, $content ) {
		extract( shortcode_atts( array(
			'top_spacing'    => '',
			'bottom_spacing' => '',
			'border_style'   => '',
			'item_el_id'	 => '',
			'item_el_class'  => '',
			'item_el_style'  => ''
		), $atts, 'bt_cc_separator' ) );
		$class = array( 'bt_cc_separator' );
		$item_id_attr = '';
		if ( $item_el_id == '' ) {
			$item_el_id = uniqid( 'bt_cc_separator' );
		} else {
			$item_el_id = $item_el_id;
		}
		$item_id_attr = 'id="' . $item_el_id . '"';
		if ( $item_el_class != '' ) {
			$class[] = $item_el_class;
		}
		$item_style_attr = '';
		if ( $item_el_style != '' ) {
			$item_style_attr = 'style="' . $item_el_style . '"';
		}
		if ( $top_spacing != '' ) {
			$class[] = 'bt_cc_top_spacing' . '_' . $top_spacing;
		}
		if ( $bottom_spacing != '' ) {
			$class[] = 'bt_cc_bottom_spacing' . '_' . $bottom_spacing;
		}
		if ( $border_style != '' ) {
			$class[] = 'bt_cc_border_style' . '_' . $border_style;
		}
		$output = '<div class="' . implode( ' ', $class ) . '" ' . $item_id_attr . ' ' . $item_style_attr . '></div>';
		return $output;
	}
}
bt_cost_calculator::init();
bt_cc_item::init();
bt_cc_multiply::init();
bt_cc_group::init();
bt_cc_text::init();
bt_cc_select::init();
bt_cc_slider::init();
bt_cc_switch::init();
bt_cc_raw_html::init();
bt_cc_separator::init();
/*
 * * * * * * * * * *
 * RC / VC MAPPING *
 * * * * * * * * * *
 */
function bt_quote_map_sc() {
	$time_array = array();
	$time_array[ '' ] = '';
	for ( $i = 0; $i <= 23; $i++ ) {
		if ( $i < 10 ) $i = '0' . $i;
		$time_array[ $i . ':00' ] =  $i . ':00';
	}
	$args = array('post_type' => 'wpcf7_contact_form', 'posts_per_page' => -1, 'post_status' => 'publish');
	$forms_data = array();
	$all_plugins = apply_filters('active_plugins', get_option('active_plugins'));
	if ( stripos( implode( $all_plugins ), 'wp-contact-form-7.php' ) ) {
		if ( $data = get_posts( $args ) ) {
			$forms_data[ esc_html__( 'No CF7 form (use default form with settings below)', 'bt-cost-calculator' ) ] = '';
			foreach( $data as $key ){
				$forms_data[ $key -> post_title ] = $key -> ID;
			}
		} else {
			$forms_data[ esc_html__( 'No contact form found', 'bt-cost-calculator' ) ] = '';
		}
	} else {
		$forms_data[ esc_html__( 'Contact Form 7 not installed', 'bt-cost-calculator' ) ] = '';
	}
	$bt_quote_params = array(
		array( 'param_name' => 'accent_color', 'type' => 'colorpicker', 'heading' => esc_html__( 'Accent Color', 'bt-cost-calculator' ) ),
		array( 'param_name' => 'total_text', 'type' => 'textfield', 'heading' => esc_html__( 'Total Title', 'bt-cost-calculator' ), 'value' => esc_html__( 'Total', 'bt-cost-calculator' ) ),
		array( 'param_name' => 'total_format', 'type' => 'dropdown', 'heading' => esc_html__( 'Total Format', 'bt-cost-calculator' ),
			'value' => array(
				esc_html__( '1,000.00 (currency)' ) => 'currency_1',
				esc_html__( '1.000,00 (currency)' ) => 'currency_2',
				esc_html__( '1 000,00 (currency)' ) => 'currency_3',
				esc_html__( '1000.00 (decimal)' )   => 'decimal_1',
				esc_html__( '1000,00 (decimal)' )   => 'decimal_2',
				esc_html__( '1000 (rounded)' )      => 'rounded',
		) ),
		array( 'param_name' => 'total_decimals', 'type' => 'dropdown', 'default' => '2', 'heading' => esc_html__( 'Number of Decimals in Total', 'bt-cost-calculator' ),
			'value' => array(
				'1' => '1',
				'2' => '2',
				'3' => '3',
				'4' => '4',
				'5' => '5',
		) ),
		array( 'param_name' => 'currency', 'type' => 'textfield', 'heading' => esc_html__( 'Currency', 'bt-cost-calculator' ) ),
		array( 'param_name' => 'currency_after', 'type' => 'checkbox', 'value' => array( esc_html__( 'Yes', 'bt-cost-calculator' ) => 'yes' ), 'heading' => esc_html__( 'Currency After Total', 'bt-cost-calculator' ) ),
		array( 'param_name' => 'currency_space', 'type' => 'checkbox', 'value' => array( esc_html__( 'Yes', 'bt-cost-calculator' ) => 'yes' ), 'default' => 'yes', 'heading' => esc_html__( 'Space Between Currency and Total', 'bt-cost-calculator' ) ),
		array( 'param_name' => 'hide_total', 'type' => 'checkbox', 'value' => array( esc_html__( 'Yes', 'bt-cost-calculator' ) => 'yes' ), 'heading' => esc_html__( 'Hide Total', 'bt-cost-calculator' ) ),
		array( 'param_name' => 'show_next', 'type' => 'checkbox', 'value' => array( esc_html__( 'Yes', 'bt-cost-calculator' ) => 'yes' ), 'heading' => esc_html__( 'Enable Contact Form', 'bt-cost-calculator' ), 'group' => esc_html__( 'Contact Form', 'bt-cost-calculator' ) ),
		array( 'param_name' => 'next_text', 'type' => 'textfield', 'heading' => esc_html__( 'Next Button Text', 'bt-cost-calculator' ), 'group' => esc_html__( 'Contact Form', 'bt-cost-calculator' ), 'description' => esc_html__( 'Next button is used to show contact form. Leave blank to use default value (Next)', 'bt-cost-calculator' ) ),
		array( 'param_name' => 'no_next', 'type' => 'checkbox', 'value' => array( esc_html__( 'Yes', 'bt-cost-calculator' ) => 'yes' ), 'heading' => esc_html__( 'Contact Form Initially Visible (Remove Next Button)', 'bt-cost-calculator' ), 'group' => esc_html__( 'Contact Form', 'bt-cost-calculator' ) ),
		array( 'param_name' => 'cf7_id', 'type' => 'dropdown', 'value' => $forms_data, 'heading' => esc_html__( 'Contact Form 7', 'bt-cost-calculator' ), 'preview' => true, 'group' => esc_html__( 'Contact Form', 'bt-cost-calculator' ) ),
		array( 'param_name' => 'url_confirmation', 'type' => 'textfield', 'heading' => esc_html__( 'Optional Redirection URL', 'bt-cost-calculator' ), 'description' => esc_html__( 'User will be redirected to this URL after submit', 'bt-cost-calculator' ), 'preview' => true, 'group' => esc_html__( 'Contact Form', 'bt-cost-calculator' ) ),
		array( 'param_name' => 'show_booking', 'type' => 'checkbox', 'value' => array( esc_html__( 'Yes', 'bt-cost-calculator' ) => 'yes' ), 'heading' => esc_html__( 'Show Date/Time Inputs', 'bt-cost-calculator' ), 'group' => esc_html__( 'Contact Form', 'bt-cost-calculator' ) ),
		array( 'param_name' => 'date_text', 'type' => 'textfield', 'heading' => esc_html__( 'Date Input Title', 'bt-cost-calculator' ), 'value' => esc_html__( 'Preferred Service Date', 'bt-cost-calculator' ), 'group' => esc_html__( 'Contact Form', 'bt-cost-calculator' ) ),
		array( 'param_name' => 'date_format', 'type' => 'textfield', 'default' => 'mm/dd/yy', 'heading' => esc_html__( 'Date Input Format', 'bt-cost-calculator' ), 'description' => esc_html__( 'Date format: ', 'bt-cost-calculator' ) . '<a href="https://api.jqueryui.com/datepicker/#utility-formatDate">https://api.jqueryui.com/datepicker/#utility-formatDate</a>', 'group' => esc_html__( 'Contact Form', 'bt-cost-calculator' ) ),
		array( 'param_name' => 'time_text', 'type' => 'textfield', 'heading' => esc_html__( 'Time Input Title', 'bt-cost-calculator' ), 'value' => esc_html__( 'Preferred Service Time', 'bt-cost-calculator' ), 'group' => esc_html__( 'Contact Form', 'bt-cost-calculator' ) ),
		array( 'param_name' => 'time_start', 'type' => 'dropdown', 'heading' => esc_html__( 'Time Input Start', 'bt-cost-calculator' ), 'value' => $time_array, 'group' => esc_html__( 'Contact Form', 'bt-cost-calculator' )),
		array( 'param_name' => 'time_end', 'type' => 'dropdown', 'heading' => esc_html__( 'Time Input End', 'bt-cost-calculator' ), 'value' => $time_array, 'group' => esc_html__( 'Contact Form', 'bt-cost-calculator' )),	
		array( 'param_name' => 'time_format', 'type' => 'dropdown', 'default' => '24', 'heading' => esc_html__( 'Time Input Format', 'bt-cost-calculator' ), 'group' => esc_html__( 'Contact Form', 'bt-cost-calculator' ),
			'value' => array(
				esc_html__( '24-hour clock', 'bt-cost-calculator' ) 			=> '24',
				esc_html__( '12-hour (AM/PM) clock', 'bt-cost-calculator' ) 	=> '12'
		) ),
		array( 'param_name' => 'm_name', 'type' => 'checkbox', 'value' => array( esc_html__( 'Yes', 'bt-cost-calculator' ) => 'Mandatory' ), 'heading' => esc_html__( 'Mandatory Name', 'bt-cost-calculator' ), 'group' => esc_html__( 'Contact Form', 'bt-cost-calculator' ) ),
		array( 'param_name' => 'm_email', 'type' => 'checkbox', 'value' => array( esc_html__( 'Yes', 'bt-cost-calculator' ) => 'Mandatory' ), 'heading' => esc_html__( 'Mandatory Email', 'bt-cost-calculator' ), 'group' => esc_html__( 'Contact Form', 'bt-cost-calculator' ) ),
		array( 'param_name' => 'm_phone', 'type' => 'checkbox', 'value' => array( esc_html__( 'Yes', 'bt-cost-calculator' ) => 'Mandatory' ), 'heading' => esc_html__( 'Mandatory Phone', 'bt-cost-calculator' ), 'group' => esc_html__( 'Contact Form', 'bt-cost-calculator' ) ),
		array( 'param_name' => 'm_address', 'type' => 'checkbox', 'value' => array( esc_html__( 'Yes', 'bt-cost-calculator' ) => 'Mandatory' ), 'heading' => esc_html__( 'Mandatory Address', 'bt-cost-calculator' ), 'group' => esc_html__( 'Contact Form', 'bt-cost-calculator' ) ),
		array( 'param_name' => 'm_message', 'type' => 'checkbox', 'value' => array( esc_html__( 'Yes', 'bt-cost-calculator' ) => 'Mandatory' ), 'heading' => esc_html__( 'Mandatory Message', 'bt-cost-calculator' ), 'group' => esc_html__( 'Contact Form', 'bt-cost-calculator' ) ),
		array( 'param_name' => 'm_date', 'type' => 'checkbox', 'value' => array( esc_html__( 'Yes', 'bt-cost-calculator' ) => 'Mandatory' ), 'heading' => esc_html__( 'Mandatory Date', 'bt-cost-calculator' ), 'group' => esc_html__( 'Contact Form', 'bt-cost-calculator' ) ),
		array( 'param_name' => 'm_time', 'type' => 'checkbox', 'value' => array( esc_html__( 'Yes', 'bt-cost-calculator' ) => 'Mandatory' ), 'heading' => esc_html__( 'Mandatory Time', 'bt-cost-calculator' ), 'group' => esc_html__( 'Contact Form', 'bt-cost-calculator' ) ),
		array( 'param_name' => 'admin_email', 'type' => 'textfield', 'heading' => esc_html__( 'Admin Email', 'bt-cost-calculator' ), 'preview' => true, 'group' => esc_html__( 'Email', 'bt-cost-calculator' ) ),
		array( 'param_name' => 'subject', 'type' => 'textfield', 'heading' => esc_html__( 'Email Subject', 'bt-cost-calculator' ), 'group' => esc_html__( 'Email', 'bt-cost-calculator' ) ),
		array( 'param_name' => 'email_client', 'type' => 'checkbox', 'value' => array( esc_html__( 'Yes', 'bt-cost-calculator' ) => 'yes' ), 'heading' => esc_html__( 'Send Email to Client', 'bt-cost-calculator' ), 'group' => esc_html__( 'Email', 'bt-cost-calculator' ) ),
		array( 'param_name' => 'email_confirmation', 'type' => 'checkbox', 'value' => array( 'Show confirmation checkbox for sending email to client' => 'yes' ), 'heading' => esc_html__( 'Client Email Confirmation', 'bt-cost-calculator' ), 'group' => esc_html__( 'Email', 'bt-cost-calculator' ) ),
		array( 'param_name' => 'email_gdpr', 'type' => 'checkbox', 'value' => array( esc_html__( 'Yes', 'bt-cost-calculator' ) => 'yes' ), 'heading' => esc_html__( 'Add GDPR checkbox', 'bt-cost-calculator' ), 'group' => esc_html__( 'Email', 'bt-cost-calculator' ) ),
		array( 'param_name' => 'email_gdpr_text', 'type' => 'textarea', 'heading' => esc_html__( 'GDPR checkbox description', 'bt-cost-calculator' ), 'description' => 'Enter GDPR checkbox description', 'group' => esc_html__( 'Email', 'bt-cost-calculator' ) ),
		array( 'param_name' => 'email_gdpr_not_text', 'type' => 'textfield', 'heading' => esc_html__( 'GDPR checkbox warning', 'bt-cost-calculator' ), 'description' => 'Enter GDPR checkbox warning text', 'group' => esc_html__( 'Email', 'bt-cost-calculator' ) ),
		array( 'param_name' => 'email_header', 'type' => 'textarea_object', 'heading' => esc_html__( 'Client Email Header', 'bt-cost-calculator' ), 'description' => 'Enter HTML for client email header', 'group' => esc_html__( 'Email', 'bt-cost-calculator' ) ),
		array( 'param_name' => 'email_footer', 'type' => 'textarea_object', 'heading' => esc_html__( 'Client Email Footer', 'bt-cost-calculator' ), 'description' => 'Enter HTML for client email footer', 'group' => esc_html__( 'Email', 'bt-cost-calculator' ) ),
		array( 'param_name' => 'rec_site_key', 'type' => 'textfield', 'heading' => esc_html__( 'reCAPTCHA Site Key', 'bt-cost-calculator' ), 'group' => esc_html__( 'reCAPTCHA', 'bt-cost-calculator' ) ),
		array( 'param_name' => 'rec_secret_key', 'type' => 'textfield', 'heading' => esc_html__( 'reCAPTCHA Secret Key', 'bt-cost-calculator' ), 'group' => esc_html__( 'reCAPTCHA', 'bt-cost-calculator' ) ),
		array( 'param_name' => 'paypal_email', 'type' => 'textfield', 'heading' => esc_html__( 'Your PayPal Account Email Address', 'bt-cost-calculator' ), 'group' => esc_html__( 'PayPal', 'bt-cost-calculator' ) ),
		array( 'param_name' => 'paypal_cart_name', 'type' => 'textfield', 'heading' => esc_html__( 'Shopping Cart Name', 'bt-cost-calculator' ), 'group' => esc_html__( 'PayPal', 'bt-cost-calculator' ) ),
		array( 'param_name' => 'paypal_currency', 'type' => 'textfield', 'heading' => esc_html__( 'Currency Code (USD, EUR, GBP, CAD, JPY)', 'bt-cost-calculator' ), 'group' => esc_html__( 'PayPal', 'bt-cost-calculator' ) ),
		
		array( 'param_name' => 'stripe_publishable_key', 'type' => 'textfield', 'heading' => esc_html__( 'Your Stripe Publishable Key', 'bt-cost-calculator' ), 'group' => esc_html__( 'Stripe', 'bt-cost-calculator' ) ),
		array( 'param_name' => 'stripe_secret_key', 'type' => 'textfield', 'heading' => esc_html__( 'Your Stripe Secret Key', 'bt-cost-calculator' ), 'group' => esc_html__( 'Stripe', 'bt-cost-calculator' ) ),
		
		
		array( 'param_name' => 'el_class', 'type' => 'textfield', 'heading' => esc_html__( 'Extra Class Name(s)', 'bt-cost-calculator' ), 'group' => esc_html__( 'Design', 'bt-cost-calculator' ) ),
		array( 'param_name' => 'el_style', 'type' => 'textfield', 'heading' => esc_html__( 'Inline Style', 'bt-cost-calculator' ), 'group' => esc_html__( 'Design', 'bt-cost-calculator' ) )
	);
	$bt_cc_item_params = array(	
		array( 'param_name' => 'name', 'type' => 'textfield', 'heading' => esc_html__( 'Name', 'bt-cost-calculator' ), 'holder' => 'div' ),	
		array( 'param_name' => 'description', 'type' => 'textfield', 'heading' => esc_html__( 'Description', 'bt-cost-calculator' ), 'holder' => 'div' ),
		array( 'param_name' => 'type', 'type' => 'dropdown', 'heading' => esc_html__( 'Input Type', 'bt-cost-calculator' ), 'holder' => 'div',
			'value' => array(
				esc_html__( 'Text', 'bt-cost-calculator' ) => 'text',
				esc_html__( 'Select', 'bt-cost-calculator' ) => 'select',
				esc_html__( 'Slider', 'bt-cost-calculator' ) => 'slider',
				esc_html__( 'Switch', 'bt-cost-calculator' ) => 'switch'
		) ),
		array( 'param_name' => 'value', 'type' => 'textarea', 'heading' => esc_html__( 'Value', 'bt-cost-calculator' ), 'description' => esc_html__( 'Unit_value for Text / name;value;description separated by new line for Select / min;max;step;unit_value;offset_value for Slider / value_off;value_on for Switch', 'bt-cost-calculator' ) ),
		array( 'param_name' => 'initial_value', 'type' => 'textfield', 'heading' => esc_html__( 'Initial value or select list index', 'bt-cost-calculator' ), 'description' => esc_html__( 'Value for Text / index for Select ( index 0 for Select... item in list ) / value between min and max values for Slider / off or on value for Switch', 'bt-cost-calculator' ) , 'preview' => true),
		array( 'param_name' => 'images', 'type' => 'attach_images', 'heading' => esc_html__( 'Images for Select input type', 'bt-cost-calculator' ) ),
		array( 'param_name' => 'img_height', 'type' => 'textfield', 'heading' => esc_html__( 'Images Height in px', 'bt-cost-calculator' ) ),
		array( 'param_name' => 'condition', 'type' => 'textarea', 'heading' => esc_html__( 'Change Event Condition', 'bt-cost-calculator' ), 'description' => esc_html__( 'Clause operator (e.g. ==0 or >0);target element id;action (e.g. fadeTo(\'slow\',0.2));lock target (lock/unlock) separated by new line', 'bt-cost-calculator' ) ),
		array( 'param_name' => 'item_el_id', 'type' => 'textfield', 'heading' => esc_html__( 'Custom Id Attribute', 'bt-cost-calculator' ), 'group' => esc_html__( 'Design', 'bt-cost-calculator' ), 'preview' => true ),
		array( 'param_name' => 'item_el_class', 'type' => 'textfield', 'heading' => esc_html__( 'Extra Class Name(s)', 'bt-cost-calculator' ), 'group' => esc_html__( 'Design', 'bt-cost-calculator' ), 'preview' => true ),
		array( 'param_name' => 'item_el_style', 'type' => 'textfield', 'heading' => esc_html__( 'Inline Style', 'bt-cost-calculator' ), 'group' => esc_html__( 'Design', 'bt-cost-calculator' ), 'preview' => true )
	);	
	$bt_cc_multiply_params = array();
	$bt_cc_group_params = array(
		array( 'param_name' => 'eval', 'type' => 'textarea', 'heading' => esc_html__( 'Pseudo-JS Code', 'bt-cost-calculator' ), 'description' => esc_html__( '$1, $2, etc. can be used to reference values of items inside this group; always use return to return the value', 'bt-cost-calculator' ) ),
		array( 'param_name' => 'paypal_label', 'type' => 'textfield', 'heading' => esc_html__( 'PayPal Label', 'bt-cost-calculator' ), 'description' => esc_html__( 'If label is not entered, this group will not be included in PayPal payment', 'bt-cost-calculator' ) ),
		array( 'param_name' => 'item_el_id', 'type' => 'textfield', 'heading' => esc_html__( 'Custom Id Attribute', 'bt-cost-calculator' ), 'group' => esc_html__( 'Design', 'bt-cost-calculator' ), 'preview' => true ),
		array( 'param_name' => 'item_el_class', 'type' => 'textfield', 'heading' => esc_html__( 'Extra Class Name(s)', 'bt-cost-calculator' ), 'group' => esc_html__( 'Design', 'bt-cost-calculator' ), 'preview' => true ),
		array( 'param_name' => 'item_el_style', 'type' => 'textfield', 'heading' => esc_html__( 'Inline Style', 'bt-cost-calculator' ), 'group' => esc_html__( 'Design', 'bt-cost-calculator' ), 'preview' => true )
	);
    $bt_cc_text_params = array(
		array( 'param_name' => 'name', 'type' => 'textfield', 'heading' => esc_html__( 'Name', 'bt-cost-calculator' ), 'holder' => 'div' ),	
		array( 'param_name' => 'description', 'type' => 'textfield', 'heading' => esc_html__( 'Description', 'bt-cost-calculator' ), 'holder' => 'div' ),
        array( 'param_name' => 'value', 'type' => 'textfield', 'heading' => esc_html__( 'Unit', 'bt-cost-calculator' ), 'description' => esc_html__( 'Unit value', 'bt-cost-calculator' ) ),
		array( 'param_name' => 'initial_value', 'type' => 'textfield', 'heading' => esc_html__( 'Initial value', 'bt-cost-calculator' ), 'description' => esc_html__( 'Initial value', 'bt-cost-calculator' ) , 'preview' => true),
		array( 'param_name' => 'condition', 'type' => 'textarea', 'heading' => esc_html__( 'Change Event Condition', 'bt-cost-calculator' ), 'description' => esc_html__( 'Clause operator (e.g. ==0 or >0);target element id;action (e.g. fadeTo(\'slow\',0.2));lock target (lock/unlock) separated by new line', 'bt-cost-calculator' ) ),
		array( 'param_name' => 'item_el_id', 'type' => 'textfield', 'heading' => esc_html__( 'Custom Id Attribute', 'bt-cost-calculator' ), 'group' => esc_html__( 'Design', 'bt-cost-calculator' ), 'preview' => true ),
		array( 'param_name' => 'item_el_class', 'type' => 'textfield', 'heading' => esc_html__( 'Extra Class Name(s)', 'bt-cost-calculator' ), 'group' => esc_html__( 'Design', 'bt-cost-calculator' ), 'preview' => true ),
		array( 'param_name' => 'item_el_style', 'type' => 'textfield', 'heading' => esc_html__( 'Inline Style', 'bt-cost-calculator' ), 'group' => esc_html__( 'Design', 'bt-cost-calculator' ), 'preview' => true )
	);
    $bt_cc_select_params = array(	
		array( 'param_name' => 'name', 'type' => 'textfield', 'heading' => esc_html__( 'Name', 'bt-cost-calculator' ), 'holder' => 'div' ),	
		array( 'param_name' => 'description', 'type' => 'textfield', 'heading' => esc_html__( 'Description', 'bt-cost-calculator' ), 'holder' => 'div' ),
		array( 'param_name' => 'value', 'type' => 'textarea', 'heading' => esc_html__( 'Value', 'bt-cost-calculator' ), 'description' => esc_html__( 'name;value;description separated by new line', 'bt-cost-calculator' ) ),
		array( 'param_name' => 'initial_value', 'type' => 'textfield', 'heading' => esc_html__( 'Initial select list index', 'bt-cost-calculator' ), 'description' => esc_html__( 'Initial selected index ( index 0 for Select... item in list )', 'bt-cost-calculator' ) , 'preview' => true),
		array( 'param_name' => 'images', 'type' => 'attach_images', 'heading' => esc_html__( 'Images for Select list', 'bt-cost-calculator' ) ),
		array( 'param_name' => 'img_height', 'type' => 'textfield', 'heading' => esc_html__( 'Images Height in px', 'bt-cost-calculator' ) ),
		array( 'param_name' => 'condition', 'type' => 'textarea', 'heading' => esc_html__( 'Change Event Condition', 'bt-cost-calculator' ), 'description' => esc_html__( 'Clause operator (e.g. ==0 or >0);target element id;action (e.g. fadeTo(\'slow\',0.2));lock target (lock/unlock) separated by new line', 'bt-cost-calculator' ) ),
		array( 'param_name' => 'item_el_id', 'type' => 'textfield', 'heading' => esc_html__( 'Custom Id Attribute', 'bt-cost-calculator' ), 'group' => esc_html__( 'Design', 'bt-cost-calculator' ), 'preview' => true ),
		array( 'param_name' => 'item_el_class', 'type' => 'textfield', 'heading' => esc_html__( 'Extra Class Name(s)', 'bt-cost-calculator' ), 'group' => esc_html__( 'Design', 'bt-cost-calculator' ), 'preview' => true ),
		array( 'param_name' => 'item_el_style', 'type' => 'textfield', 'heading' => esc_html__( 'Inline Style', 'bt-cost-calculator' ), 'group' => esc_html__( 'Design', 'bt-cost-calculator' ), 'preview' => true )
	);	
    $bt_cc_slider_params = array(	
		array( 'param_name' => 'name', 'type' => 'textfield', 'heading' => esc_html__( 'Name', 'bt-cost-calculator' ), 'holder' => 'div' ),	
		array( 'param_name' => 'description', 'type' => 'textfield', 'heading' => esc_html__( 'Description', 'bt-cost-calculator' ), 'holder' => 'div' ),
		array( 'param_name' => 'value_min', 'type' => 'textfield', 'heading' => esc_html__( 'Min Value', 'bt-cost-calculator' ), 'description' => esc_html__( 'Min value', 'bt-cost-calculator' ) ),
		array( 'param_name' => 'value_max', 'type' => 'textfield', 'heading' => esc_html__( 'Max Value', 'bt-cost-calculator' ), 'description' => esc_html__( 'Max value', 'bt-cost-calculator' ) ),
		array( 'param_name' => 'value_step', 'type' => 'textfield', 'heading' => esc_html__( 'Step', 'bt-cost-calculator' ), 'description' => esc_html__( 'Step value', 'bt-cost-calculator' ) ),
		array( 'param_name' => 'value_unit', 'type' => 'textfield', 'heading' => esc_html__( 'Unit', 'bt-cost-calculator' ), 'description' => esc_html__( 'Unit value', 'bt-cost-calculator' ) ),
		array( 'param_name' => 'value_offset', 'type' => 'textfield', 'heading' => esc_html__( 'Offset', 'bt-cost-calculator' ), 'description' => esc_html__( 'Offset value', 'bt-cost-calculator' ) ),
		array( 'param_name' => 'initial_value', 'type' => 'textfield', 'heading' => esc_html__( 'Initial value', 'bt-cost-calculator' ), 'description' => esc_html__( 'Value between min and max values', 'bt-cost-calculator' ) , 'preview' => true),
		array( 'param_name' => 'condition', 'type' => 'textarea', 'heading' => esc_html__( 'Change Event Condition', 'bt-cost-calculator' ), 'description' => esc_html__( 'Clause operator (e.g. ==0 or >0);target element id;action (e.g. fadeTo(\'slow\',0.2));lock target (lock/unlock) separated by new line', 'bt-cost-calculator' ) ),
		array( 'param_name' => 'item_el_id', 'type' => 'textfield', 'heading' => esc_html__( 'Custom Id Attribute', 'bt-cost-calculator' ), 'group' => esc_html__( 'Design', 'bt-cost-calculator' ), 'preview' => true ),
		array( 'param_name' => 'item_el_class', 'type' => 'textfield', 'heading' => esc_html__( 'Extra Class Name(s)', 'bt-cost-calculator' ), 'group' => esc_html__( 'Design', 'bt-cost-calculator' ), 'preview' => true ),
		array( 'param_name' => 'item_el_style', 'type' => 'textfield', 'heading' => esc_html__( 'Inline Style', 'bt-cost-calculator' ), 'group' => esc_html__( 'Design', 'bt-cost-calculator' ), 'preview' => true )
	);
    $bt_cc_switch_params = array(	
		array( 'param_name' => 'name', 'type' => 'textfield', 'heading' => esc_html__( 'Name', 'bt-cost-calculator' ), 'holder' => 'div' ),	
		array( 'param_name' => 'description', 'type' => 'textfield', 'heading' => esc_html__( 'Description', 'bt-cost-calculator' ), 'holder' => 'div' ),
		array( 'param_name' => 'value_off', 'type' => 'textfield', 'heading' => esc_html__( 'Value Off', 'bt-cost-calculator' ), 'description' => esc_html__( 'Value when switched off', 'bt-cost-calculator' ) ),
		array( 'param_name' => 'value_on', 'type' => 'textfield', 'heading' => esc_html__( 'Value On', 'bt-cost-calculator' ), 'description' => esc_html__( 'Value when switched on', 'bt-cost-calculator' ) ),
		array( 'param_name' => 'initial_value', 'type' => 'textfield', 'heading' => esc_html__( 'Initial value', 'bt-cost-calculator' ), 'description' => esc_html__( 'Value off or value on', 'bt-cost-calculator' ) , 'preview' => true),
		array( 'param_name' => 'condition', 'type' => 'textarea', 'heading' => esc_html__( 'Change Event Condition', 'bt-cost-calculator' ), 'description' => esc_html__( 'Clause operator (e.g. ==0 or >0);target element id;action (e.g. fadeTo(\'slow\',0.2));lock target (lock/unlock) separated by new line', 'bt-cost-calculator' ) ),
		array( 'param_name' => 'item_el_id', 'type' => 'textfield', 'heading' => esc_html__( 'Custom Id Attribute', 'bt-cost-calculator' ), 'group' => esc_html__( 'Design', 'bt-cost-calculator' ), 'preview' => true ),
		array( 'param_name' => 'item_el_class', 'type' => 'textfield', 'heading' => esc_html__( 'Extra Class Name(s)', 'bt-cost-calculator' ), 'group' => esc_html__( 'Design', 'bt-cost-calculator' ), 'preview' => true ),
		array( 'param_name' => 'item_el_style', 'type' => 'textfield', 'heading' => esc_html__( 'Inline Style', 'bt-cost-calculator' ), 'group' => esc_html__( 'Design', 'bt-cost-calculator' ), 'preview' => true )
	);
	if ( function_exists( 'bt_rc_map' ) ) {
		bt_rc_map( 'bt_cost_calculator', array( 'name' => esc_html__( 'Cost Calculator', 'bt-cost-calculator' ), 'description' => esc_html__( 'Cost calculator container', 'bt-cost-calculator' ), 'container' => 'vertical', 'icon' => 'bt_bb_icon_bt_bb_cost_calculator', 'accept' => array( 'bt_cc_item' => true, 'bt_cc_multiply' => true, 'bt_cc_group' => true, 'bt_hr' => true, 'bt_header' => true, 'bt_text' => true, 'bt_bb_separator' => true, 'bt_bb_headline' => true, 'bt_bb_text' => true, 'bt_bb_raw_content' => true, 'bt_cc_text' => true, 'bt_cc_select' => true, 'bt_cc_slider' => true, 'bt_cc_switch' => true), 'toggle' => true,
			'params' => $bt_quote_params
		));
		bt_rc_map( 'bt_cc_item', array( 'name' => esc_html__( 'Cost Calculator Item (Deprecated)', 'bt-cost-calculator' ), 'description' => esc_html__( 'Single cost calculator element (all in one)', 'bt-cost-calculator' ), 'icon' => 'bt_bb_icon_bt_bb_cost_calculator_item', 'as_child' => array( 'only' => 'bt_cost_calculator,bt_cc_multiply,bt_cc_group' ),
			'params' => $bt_cc_item_params
		));
		bt_rc_map( 'bt_cc_multiply', array( 'name' => esc_html__( 'Cost Calculator Multiply', 'bt-cost-calculator' ), 'description' => esc_html__( 'Cost calculator multiply container', 'bt-cost-calculator' ), 'icon' => 'bt_bb_icon_bt_bb_cost_calculator_multiply', 'container' => 'vertical', 'accept' => array( 'bt_cc_item' => true, 'bt_cc_text' => true, 'bt_cc_select' => true, 'bt_cc_slider' => true, 'bt_cc_switch' => true ), 'show_settings_on_create' => false, 'as_child' => array( 'only' => 'bt_cost_calculator' ),
			'params' => $bt_cc_multiply_params
		));
		bt_rc_map( 'bt_cc_group', array( 'name' => esc_html__( 'Cost Calculator Group', 'bt-cost-calculator' ), 'description' => esc_html__( 'Cost calculator group container', 'bt-cost-calculator' ), 'container' => 'vertical', 'icon' => 'bt_bb_icon_bt_bb_cost_calculator_group', 'accept' => array( 'bt_cc_item' => true, 'bt_hr' => true, 'bt_header' => true, 'bt_text' => true, 'bt_bb_separator' => true, 'bt_bb_headline' => true, 'bt_bb_text' => true , 'bt_cc_text' => true, 'bt_cc_select' => true, 'bt_cc_slider' => true, 'bt_cc_switch' => true, 'bt_bb_raw_content' => true ), 'show_settings_on_create' => true, 'as_child' => array( 'only' => 'bt_cost_calculator' ),
			'params' => $bt_cc_group_params
		));
		bt_rc_map( 'bt_cc_text', array( 'name' => esc_html__( 'Cost Calculator Text', 'bt-cost-calculator' ), 'description' => esc_html__( 'Cost calculator text input', 'bt-cost-calculator' ), 'icon' => 'bt_bb_icon_bt_bb_cost_calculator_text', 'show_settings_on_create' => true, 'as_child' => array( 'only' => 'bt_cost_calculator,bt_cc_multiply,bt_cc_group' ),
			'params' => $bt_cc_text_params
		));
		bt_rc_map( 'bt_cc_select', array( 'name' => esc_html__( 'Cost Calculator Select', 'bt-cost-calculator' ), 'description' => esc_html__( 'Cost calculator select list', 'bt-cost-calculator' ), 'icon' => 'bt_bb_icon_bt_bb_cost_calculator_select', 'show_settings_on_create' => true, 'as_child' => array( 'only' => 'bt_cost_calculator,bt_cc_multiply,bt_cc_group' ),
			'params' => $bt_cc_select_params
		));
		bt_rc_map( 'bt_cc_slider', array( 'name' => esc_html__( 'Cost Calculator Slider', 'bt-cost-calculator' ), 'description' => esc_html__( 'Cost calculator slider', 'bt-cost-calculator' ), 'icon' => 'bt_bb_icon_bt_bb_cost_calculator_slider', 'show_settings_on_create' => true, 'as_child' => array( 'only' => 'bt_cost_calculator,bt_cc_multiply,bt_cc_group' ),
			'params' => $bt_cc_slider_params
		));
		bt_rc_map( 'bt_cc_switch', array( 'name' => esc_html__( 'Cost Calculator Switch', 'bt-cost-calculator' ), 'description' => esc_html__( 'Cost calculator switch checkbox', 'bt-cost-calculator' ), 'icon' => 'bt_bb_icon_bt_bb_cost_calculator_switch', 'show_settings_on_create' => true, 'as_child' => array( 'only' => 'bt_cost_calculator,bt_cc_multiply,bt_cc_group' ),
			'params' => $bt_cc_switch_params
		));
	}
	if ( function_exists( 'vc_map' ) ) {
		if ( class_exists( 'WPBakeryShortCodesContainer' ) ) {
			class WPBakeryShortCode_bt_cost_calculator extends WPBakeryShortCodesContainer {
			}
		}
		if ( class_exists( 'WPBakeryShortCodesContainer' ) ) {
			class WPBakeryShortCode_bt_cc_multiply extends WPBakeryShortCodesContainer {
			}
		}
		if ( class_exists( 'WPBakeryShortCodesContainer' ) ) {
			class WPBakeryShortCode_bt_cc_group extends WPBakeryShortCodesContainer {
			}
		}
		$data = array();
		$data['name']              = esc_html__( 'Cost Calculator', 'bt-cost-calculator' );
		$data['base']              = 'bt_cost_calculator';
		$data['as_parent']         = array( 'except' => 'vc_row,vc_column,vc_row_inner,vc_column_inner,bt_cost_calculator' );
		$data['content_element']   = true;
		$data['js_view']           = 'VcColumnView';
		$data['category']          = 'Content';
		$data['icon']              = 'bt_quote_icon';
		$data['admin_enqueue_css'] = array( plugins_url( 'vc_style.css', __FILE__ ) );
		$data['admin_enqueue_js']  = array( plugins_url( 'vc_js.js', __FILE__ ) );
		$data['description']       = esc_html__( 'Cost calculator container', 'bt-cost-calculator' );
		$data['params'] = $bt_quote_params;
		vc_map( $data );
		$data = array();
		$data['name']              = esc_html__( 'Cost Calculator Multiply', 'bt-cost-calculator' );
		$data['base']              = 'bt_cc_multiply';
		$data['as_parent']         = array( 'except' => 'vc_row,vc_column' );
		$data['as_child']          = array( 'only' => 'bt_cost_calculator' );
		$data['content_element']   = true;
		$data['js_view']           = 'VcColumnView';
		$data['category']          = 'Content';
		$data['icon']              = 'bt_quote_icon_multiply';
		$data['admin_enqueue_css'] = array( plugins_url( 'vc_style.css', __FILE__ ) );
		$data['description']       = esc_html__( 'Cost calculator multiply container', 'bt-cost-calculator' );
		$data['params'] = $bt_cc_multiply_params;
		vc_map( $data );		
		$data = array();
		$data['name']              = esc_html__( 'Cost Calculator Group', 'bt-cost-calculator' );
		$data['base']              = 'bt_cc_group';
		$data['as_parent']         = array( 'except' => 'vc_row,vc_column' );
		$data['as_child']          = array( 'only' => 'bt_cost_calculator' );
		$data['content_element']   = true;
		$data['js_view']           = 'VcColumnView';
		$data['category']          = 'Content';
		$data['icon']              = 'bt_quote_icon_group';
		$data['admin_enqueue_css'] = array( plugins_url( 'vc_style.css', __FILE__ ) );
		$data['description']       = esc_html__( 'Cost calculator group container', 'bt-cost-calculator' );
		$data['params'] = $bt_cc_group_params;
		vc_map( $data );
		$data = array();
		$data['name']              = esc_html__( 'Cost Calculator Item (Deprecated)', 'bt-cost-calculator' );
		$data['base']              = 'bt_cc_item';
		$data['content_element']   = true;
		$data['js_view']           = 'BTCCItem';
		$data['category']          = 'Content';
		$data['as_child']          = array( 'only' => 'bt_cost_calculator,bt_cc_multiply,bt_cc_group' );
		$data['icon']              = 'bt_quote_icon_item';
		$data['admin_enqueue_css'] = array( plugins_url( 'vc_style.css', __FILE__ ) );
		$data['description']       = esc_html__( 'Cost calculator item', 'bt-cost-calculator' );
		$data['params'] = $bt_cc_item_params;
		vc_map( $data );
		$data = array();
		$data['name']              = esc_html__( 'Cost Calculator Text', 'bt-cost-calculator' );
		$data['base']              = 'bt_cc_text';
		$data['content_element']   = true;
		$data['js_view']           = 'BTCCItem';
		$data['category']          = 'Content';
		$data['as_child']          = array( 'only' => 'bt_cost_calculator,bt_cc_multiply,bt_cc_group' );
		$data['icon']              = 'bt_quote_icon_item';
		$data['admin_enqueue_css'] = array( plugins_url( 'vc_style.css', __FILE__ ) );
		$data['description']       = esc_html__( 'Cost calculator text control', 'bt-cost-calculator' );
		$data['params'] = $bt_cc_text_params;
		vc_map( $data );
		$data = array();
		$data['name']              = esc_html__( 'Cost Calculator Select', 'bt-cost-calculator' );
		$data['base']              = 'bt_cc_select';
		$data['content_element']   = true;
		$data['js_view']           = 'BTCCItem';
		$data['category']          = 'Content';
		$data['as_child']          = array( 'only' => 'bt_cost_calculator,bt_cc_multiply,bt_cc_group' );
		$data['icon']              = 'bt_quote_icon_item';
		$data['admin_enqueue_css'] = array( plugins_url( 'vc_style.css', __FILE__ ) );
		$data['description']       = esc_html__( 'Cost calculator select control', 'bt-cost-calculator' );
		$data['params'] = $bt_cc_select_params;
		vc_map( $data );
		$data = array();
		$data['name']              = esc_html__( 'Cost Calculator Slider', 'bt-cost-calculator' );
		$data['base']              = 'bt_cc_slider';
		$data['content_element']   = true;
		$data['js_view']           = 'BTCCItem';
		$data['category']          = 'Content';
		$data['as_child']          = array( 'only' => 'bt_cost_calculator,bt_cc_multiply,bt_cc_group' );
		$data['icon']              = 'bt_quote_icon_item';
		$data['admin_enqueue_css'] = array( plugins_url( 'vc_style.css', __FILE__ ) );
		$data['description']       = esc_html__( 'Cost calculator slider control', 'bt-cost-calculator' );
		$data['params'] = $bt_cc_slider_params;
		vc_map( $data );
		$data = array();
		$data['name']              = esc_html__( 'Cost Calculator Switch', 'bt-cost-calculator' );
		$data['base']              = 'bt_cc_switch';
		$data['content_element']   = true;
		$data['js_view']           = 'BTCCItem';
		$data['category']          = 'Content';
		$data['as_child']          = array( 'only' => 'bt_cost_calculator,bt_cc_multiply,bt_cc_group' );
		$data['icon']              = 'bt_quote_icon_item';
		$data['admin_enqueue_css'] = array( plugins_url( 'vc_style.css', __FILE__ ) );
		$data['description']       = esc_html__( 'Cost calculator switch control', 'bt-cost-calculator' );
		$data['params'] = $bt_cc_switch_params;
		vc_map( $data );
	}
	/*$micro_builder = new BT_Micro_Builder( array( 'post_type' => 'cost-calculator', 'root_id' => 'bt_cost_calculator_builder' ) );*/
	BT_CC_Root::$builder->map( 'bt_cost_calculator', array( 'name' => esc_html__( 'Cost Calculator', 'bt-cost-calculator' ), 'description' => esc_html__( 'Cost calculator container', 'bt-cost-calculator' ), 'container' => 'vertical', 'accept' => array( 'bt_cc_item' => true, 'bt_cc_multiply' => true, 'bt_cc_group' => true, 'bt_hr' => true, 'bt_header' => true, 'bt_text' => true, 'bt_bb_separator' => true, 'bt_bb_headline' => true, 'bt_bb_text' => true , 'bt_cc_text' => true, 'bt_cc_select' => true, 'bt_cc_slider' => true, 'bt_cc_switch' => true, 'bt_cc_raw_html' => true, 'bt_cc_separator' => true), 'toggle' => true, 'root' => true,
		'params' => $bt_quote_params
	));
	BT_CC_Root::$builder->map( 'bt_cc_multiply', array( 'name' => esc_html__( 'Cost Calculator Multiply', 'bt-cost-calculator' ), 'description' => esc_html__( 'Cost calculator multiply container', 'bt-cost-calculator' ), 'container' => 'vertical', 'icon' => 'bt_bb_icon_bt_cc_multiply', 'accept' => array( 'bt_cc_item' => true, 'bt_cc_text' => true, 'bt_cc_select' => true, 'bt_cc_slider' => true, 'bt_cc_switch' => true, 'bt_cc_raw_html' => true, 'bt_cc_separator' => true ), 'show_settings_on_create' => false,
		'params' => $bt_cc_multiply_params
	));
	$bt_cc_group_params = array(
		array( 'param_name' => 'eval', 'type' => 'textarea_object', 'heading' => esc_html__( 'Pseudo-JS Code', 'bt-cost-calculator' ), 'description' => esc_html__( '$1, $2, etc. can be used to reference values of items inside this group; always use return to return the value', 'bt-cost-calculator' ) ),
		array( 'param_name' => 'paypal_label', 'type' => 'textfield', 'heading' => esc_html__( 'PayPal Label', 'bt-cost-calculator' ), 'description' => esc_html__( 'If label is not entered, this group will not be included in PayPal payment', 'bt-cost-calculator' ), 'preview' => true ),
		array( 'param_name' => 'item_el_id', 'type' => 'textfield', 'heading' => esc_html__( 'Custom Id Attribute', 'bt-cost-calculator' ), 'group' => esc_html__( 'Design', 'bt-cost-calculator' ), 'preview' => true ),
		array( 'param_name' => 'item_el_class', 'type' => 'textfield', 'heading' => esc_html__( 'Extra Class Name(s)', 'bt-cost-calculator' ), 'group' => esc_html__( 'Design', 'bt-cost-calculator' ), 'preview' => true ),
		array( 'param_name' => 'item_el_style', 'type' => 'textfield', 'heading' => esc_html__( 'Inline Style', 'bt-cost-calculator' ), 'group' => esc_html__( 'Design', 'bt-cost-calculator' ), 'preview' => true )
	);
	BT_CC_Root::$builder->map( 'bt_cc_group', array( 'name' => esc_html__( 'Cost Calculator Group', 'bt-cost-calculator' ), 'description' => esc_html__( 'Cost calculator group container', 'bt-cost-calculator' ), 'container' => 'vertical', 'icon' => 'bt_bb_icon_bt_cc_group', 'accept' => array( 'bt_cost_calculator' => false, 'bt_cc_multiply' => false, 'bt_cc_group' => false, 'bt_row' => false, 'bt_row_inner' => false, 'bt_column' => false, 'bt_column_inner' => false ), 'accept_all' => true, 'show_settings_on_create' => true,
		'params' => $bt_cc_group_params
	));
    $bt_cc_text_params = array(
		array( 'param_name' => 'name', 'type' => 'textfield', 'heading' => esc_html__( 'Name', 'bt-cost-calculator' ), 'holder' => 'div' ),
		array( 'param_name' => 'description', 'type' => 'textfield', 'heading' => esc_html__( 'Description', 'bt-cost-calculator' ), 'holder' => 'div' ),
        array( 'param_name' => 'value', 'type' => 'textfield', 'heading' => esc_html__( 'Unit', 'bt-cost-calculator' ), 'description' => esc_html__( 'Unit value', 'bt-cost-calculator' ) ),
		array( 'param_name' => 'initial_value', 'type' => 'textfield', 'heading' => esc_html__( 'Initial value', 'bt-cost-calculator' ), 'description' => esc_html__( 'Initial value', 'bt-cost-calculator' ) , 'preview' => true),
		array( 'param_name' => 'condition', 'type' => 'textarea_object', 'heading' => esc_html__( 'Change Event Condition', 'bt-cost-calculator' ), 'description' => esc_html__( 'Clause operator (e.g. ==0 or >0);target element id;action (e.g. fadeTo(\'slow\',0.2));lock target (lock/unlock) separated by new line', 'bt-cost-calculator' ) ),
		array( 'param_name' => 'item_el_id', 'type' => 'textfield', 'heading' => esc_html__( 'Custom Id Attribute', 'bt-cost-calculator' ), 'group' => esc_html__( 'Design', 'bt-cost-calculator' ), 'preview' => true ),
		array( 'param_name' => 'item_el_class', 'type' => 'textfield', 'heading' => esc_html__( 'Extra Class Name(s)', 'bt-cost-calculator' ), 'group' => esc_html__( 'Design', 'bt-cost-calculator' ), 'preview' => true ),
		array( 'param_name' => 'item_el_style', 'type' => 'textfield', 'heading' => esc_html__( 'Inline Style', 'bt-cost-calculator' ), 'group' => esc_html__( 'Design', 'bt-cost-calculator' ), 'preview' => true )
	);
	BT_CC_Root::$builder->map( 'bt_cc_text', array( 'name' => esc_html__( 'Cost Calculator Text', 'bt-cost-calculator' ), 'description' => esc_html__( 'Cost calculator text input', 'bt-cost-calculator' ), 'icon' => 'bt_bb_icon_bt_cc_text', 'show_settings_on_create' => true,
		'params' => $bt_cc_text_params
	));
    $bt_cc_select_params = array(	
		array( 'param_name' => 'name', 'type' => 'textfield', 'heading' => esc_html__( 'Name', 'bt-cost-calculator' ), 'holder' => 'div' ),
		array( 'param_name' => 'description', 'type' => 'textfield', 'heading' => esc_html__( 'Description', 'bt-cost-calculator' ), 'holder' => 'div' ),
		array( 'param_name' => 'value', 'type' => 'textarea', 'heading' => esc_html__( 'Value', 'bt-cost-calculator' ), 'description' => esc_html__( 'name;value;description separated by new line', 'bt-cost-calculator' ) ),
		array( 'param_name' => 'initial_value', 'type' => 'textfield', 'heading' => esc_html__( 'Initial select list index', 'bt-cost-calculator' ), 'description' => esc_html__( 'Initial selected index ( index 0 for Select... item in list )', 'bt-cost-calculator' ) , 'preview' => true),
		array( 'param_name' => 'images', 'type' => 'attach_images', 'heading' => esc_html__( 'Images for Select list', 'bt-cost-calculator' ) ),
		array( 'param_name' => 'img_height', 'type' => 'textfield', 'heading' => esc_html__( 'Images Height in px', 'bt-cost-calculator' ) ),
		array( 'param_name' => 'condition', 'type' => 'textarea_object', 'heading' => esc_html__( 'Change Event Condition', 'bt-cost-calculator' ), 'description' => esc_html__( 'Clause operator (e.g. ==0 or >0);target element id;action (e.g. fadeTo(\'slow\',0.2));lock target (lock/unlock) separated by new line', 'bt-cost-calculator' ) ),
		array( 'param_name' => 'item_el_id', 'type' => 'textfield', 'heading' => esc_html__( 'Custom Id Attribute', 'bt-cost-calculator' ), 'group' => esc_html__( 'Design', 'bt-cost-calculator' ), 'preview' => true ),
		array( 'param_name' => 'item_el_class', 'type' => 'textfield', 'heading' => esc_html__( 'Extra Class Name(s)', 'bt-cost-calculator' ), 'group' => esc_html__( 'Design', 'bt-cost-calculator' ), 'preview' => true ),
		array( 'param_name' => 'item_el_style', 'type' => 'textfield', 'heading' => esc_html__( 'Inline Style', 'bt-cost-calculator' ), 'group' => esc_html__( 'Design', 'bt-cost-calculator' ), 'preview' => true )
	);
	BT_CC_Root::$builder->map( 'bt_cc_select', array( 'name' => esc_html__( 'Cost Calculator Select', 'bt-cost-calculator' ), 'description' => esc_html__( 'Cost calculator select list', 'bt-cost-calculator' ), 'icon' => 'bt_bb_icon_bt_cc_select', 'show_settings_on_create' => true,
		'params' => $bt_cc_select_params
	));
    $bt_cc_slider_params = array(	
		array( 'param_name' => 'name', 'type' => 'textfield', 'heading' => esc_html__( 'Name', 'bt-cost-calculator' ), 'holder' => 'div' ),
		array( 'param_name' => 'description', 'type' => 'textfield', 'heading' => esc_html__( 'Description', 'bt-cost-calculator' ), 'holder' => 'div' ),
		array( 'param_name' => 'value_min', 'type' => 'textfield', 'heading' => esc_html__( 'Min Value', 'bt-cost-calculator' ), 'description' => esc_html__( 'Min value', 'bt-cost-calculator' ), 'preview' => true ),
		array( 'param_name' => 'value_max', 'type' => 'textfield', 'heading' => esc_html__( 'Max Value', 'bt-cost-calculator' ), 'description' => esc_html__( 'Max value', 'bt-cost-calculator' ), 'preview' => true ),
		array( 'param_name' => 'value_step', 'type' => 'textfield', 'heading' => esc_html__( 'Step', 'bt-cost-calculator' ), 'description' => esc_html__( 'Step value', 'bt-cost-calculator' ), 'preview' => true ),
		array( 'param_name' => 'value_unit', 'type' => 'textfield', 'heading' => esc_html__( 'Unit', 'bt-cost-calculator' ), 'description' => esc_html__( 'Unit value', 'bt-cost-calculator' ), 'preview' => true ),
		array( 'param_name' => 'value_offset', 'type' => 'textfield', 'heading' => esc_html__( 'Offset', 'bt-cost-calculator' ), 'description' => esc_html__( 'Offset value', 'bt-cost-calculator' ), 'preview' => true ),
		array( 'param_name' => 'initial_value', 'type' => 'textfield', 'heading' => esc_html__( 'Initial value', 'bt-cost-calculator' ), 'description' => esc_html__( 'Value between min and max values', 'bt-cost-calculator' ) , 'preview' => true),
		array( 'param_name' => 'condition', 'type' => 'textarea_object', 'heading' => esc_html__( 'Change Event Condition', 'bt-cost-calculator' ), 'description' => esc_html__( 'Clause operator (e.g. ==0 or >0);target element id;action (e.g. fadeTo(\'slow\',0.2));lock target (lock/unlock) separated by new line', 'bt-cost-calculator' ) ),
		array( 'param_name' => 'item_el_id', 'type' => 'textfield', 'heading' => esc_html__( 'Custom Id Attribute', 'bt-cost-calculator' ), 'group' => esc_html__( 'Design', 'bt-cost-calculator' ), 'preview' => true ),
		array( 'param_name' => 'item_el_class', 'type' => 'textfield', 'heading' => esc_html__( 'Extra Class Name(s)', 'bt-cost-calculator' ), 'group' => esc_html__( 'Design', 'bt-cost-calculator' ), 'preview' => true ),
		array( 'param_name' => 'item_el_style', 'type' => 'textfield', 'heading' => esc_html__( 'Inline Style', 'bt-cost-calculator' ), 'group' => esc_html__( 'Design', 'bt-cost-calculator' ), 'preview' => true )
	);
	BT_CC_Root::$builder->map( 'bt_cc_slider', array( 'name' => esc_html__( 'Cost Calculator Slider', 'bt-cost-calculator' ), 'description' => esc_html__( 'Cost calculator slider', 'bt-cost-calculator' ), 'icon' => 'bt_bb_icon_bt_cc_slider', 'show_settings_on_create' => true,
		'params' => $bt_cc_slider_params
	));
    $bt_cc_switch_params = array(	
		array( 'param_name' => 'name', 'type' => 'textfield', 'heading' => esc_html__( 'Name', 'bt-cost-calculator' ), 'holder' => 'div' ),
		array( 'param_name' => 'description', 'type' => 'textfield', 'heading' => esc_html__( 'Description', 'bt-cost-calculator' ), 'holder' => 'div' ),
		array( 'param_name' => 'value_off', 'type' => 'textfield', 'heading' => esc_html__( 'Value Off', 'bt-cost-calculator' ), 'description' => esc_html__( 'Value when switched off', 'bt-cost-calculator' ), 'preview' => true ),
		array( 'param_name' => 'value_on', 'type' => 'textfield', 'heading' => esc_html__( 'Value On', 'bt-cost-calculator' ), 'description' => esc_html__( 'Value when switched on', 'bt-cost-calculator' ), 'preview' => true ),
		array( 'param_name' => 'initial_value', 'type' => 'textfield', 'heading' => esc_html__( 'Initial value', 'bt-cost-calculator' ), 'description' => esc_html__( 'Value off or value on', 'bt-cost-calculator' ) , 'preview' => true),
		array( 'param_name' => 'condition', 'type' => 'textarea_object', 'heading' => esc_html__( 'Change Event Condition', 'bt-cost-calculator' ), 'description' => esc_html__( 'Clause operator (e.g. ==0 or >0);target element id;action (e.g. fadeTo(\'slow\',0.2));lock target (lock/unlock) separated by new line', 'bt-cost-calculator' ) ),
		array( 'param_name' => 'item_el_id', 'type' => 'textfield', 'heading' => esc_html__( 'Custom Id Attribute', 'bt-cost-calculator' ), 'group' => esc_html__( 'Design', 'bt-cost-calculator' ), 'preview' => true ),
		array( 'param_name' => 'item_el_class', 'type' => 'textfield', 'heading' => esc_html__( 'Extra Class Name(s)', 'bt-cost-calculator' ), 'group' => esc_html__( 'Design', 'bt-cost-calculator' ), 'preview' => true ),
		array( 'param_name' => 'item_el_style', 'type' => 'textfield', 'heading' => esc_html__( 'Inline Style', 'bt-cost-calculator' ), 'group' => esc_html__( 'Design', 'bt-cost-calculator' ), 'preview' => true )
	);
	BT_CC_Root::$builder->map( 'bt_cc_switch', array( 'name' => esc_html__( 'Cost Calculator Switch', 'bt-cost-calculator' ), 'description' => esc_html__( 'Cost calculator switch checkbox', 'bt-cost-calculator' ), 'icon' => 'bt_bb_icon_bt_cc_switch', 'show_settings_on_create' => true,
		'params' => $bt_cc_switch_params
	));
    $bt_cc_raw_html_params = array(	
		array( 'param_name' => 'raw_html', 'type' => 'textarea_object', 'heading' => esc_html__( 'HTML', 'bt-cost-calculator' ) ),
	);
	BT_CC_Root::$builder->map( 'bt_cc_raw_html', array( 'name' => esc_html__( 'Cost Calculator Raw HTML', 'bt-cost-calculator' ), 'description' => esc_html__( 'Cost calculator raw HTML', 'bt-cost-calculator' ), 'icon' => 'bt_bb_icon_bt_cc_raw_html', 'show_settings_on_create' => true,
		'params' => $bt_cc_raw_html_params
	));
    $bt_cc_separator_params = array(	
		array( 'param_name' => 'top_spacing', 'type' => 'dropdown', 'heading' => esc_html__( 'Top Spacing', 'bt-cost-calculator' ), 'preview' => true,
			'value' => array(
				esc_html__( 'No spacing', 'bt-cost-calculator' ) => '',
				esc_html__( 'Extra small', 'bt-cost-calculator' ) => 'extra_small',
				esc_html__( 'Small', 'bt-cost-calculator' ) => 'small',		
				esc_html__( 'Normal', 'bt-cost-calculator' ) => 'normal',
				esc_html__( 'Medium', 'bt-cost-calculator' ) => 'medium',
				esc_html__( 'Large', 'bt-cost-calculator' ) => 'large',
				esc_html__( 'Extra large', 'bt-cost-calculator' ) => 'extra_large'
			)
		),
		array( 'param_name' => 'bottom_spacing', 'type' => 'dropdown', 'heading' => esc_html__( 'Bottom Spacing', 'bt-cost-calculator' ), 'preview' => true,
			'value' => array(
				esc_html__( 'No spacing', 'bt-cost-calculator' ) => '',
				esc_html__( 'Extra small', 'bt-cost-calculator' ) => 'extra_small',
				esc_html__( 'Small', 'bt-cost-calculator' ) => 'small',		
				esc_html__( 'Normal', 'bt-cost-calculator' ) => 'normal',
				esc_html__( 'Medium', 'bt-cost-calculator' ) => 'medium',
				esc_html__( 'Large', 'bt-cost-calculator' ) => 'large',
				esc_html__( 'Extra large', 'bt-cost-calculator' ) => 'extra_large'
			)
		),				
		array( 'param_name' => 'border_style', 'type' => 'dropdown', 'heading' => esc_html__( 'Border Style', 'bt-cost-calculator' ), 'preview' => true,
			'value' => array(
				esc_html__( 'None', 'bt-cost-calculator' ) => 'none',
				esc_html__( 'Solid', 'bt-cost-calculator' ) => 'solid',
				esc_html__( 'Dotted', 'bt-cost-calculator' ) => 'dotted',
				esc_html__( 'Dashed', 'bt-cost-calculator' ) => 'dashed'
			)
		),
		array( 'param_name' => 'item_el_id', 'type' => 'textfield', 'heading' => esc_html__( 'Custom Id Attribute', 'bt-cost-calculator' ), 'group' => esc_html__( 'Design', 'bt-cost-calculator' ), 'preview' => true ),
		array( 'param_name' => 'item_el_class', 'type' => 'textfield', 'heading' => esc_html__( 'Extra Class Name(s)', 'bt-cost-calculator' ), 'group' => esc_html__( 'Design', 'bt-cost-calculator' ), 'preview' => true ),
		array( 'param_name' => 'item_el_style', 'type' => 'textfield', 'heading' => esc_html__( 'Inline Style', 'bt-cost-calculator' ), 'group' => esc_html__( 'Design', 'bt-cost-calculator' ), 'preview' => true )
	);
	BT_CC_Root::$builder->map( 'bt_cc_separator', array( 'name' => esc_html__( 'Cost Calculator Separator', 'bt-cost-calculator' ), 'description' => esc_html__( 'Cost calculator separator', 'bt-cost-calculator' ), 'icon' => 'bt_bb_icon_bt_cc_separator', 'show_settings_on_create' => true,
		'params' => $bt_cc_separator_params
	));
}
add_action( 'plugins_loaded', 'bt_quote_map_sc' );