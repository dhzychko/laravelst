<?php

require_once( 'class-btbb-light-list-table.php' );
require_once( 'class-btbb-light-item.php' );

if ( ! class_exists( 'BTBB_Light' ) ) {
	
	class BTBB_Light {

		private $slug;
		private $edit_slug;
		private $single_name;
		private $plural_name;
		private $icon;
		private $home_url;
		private $doc_url;
		private $support_url;
		private $shortcode;

		private $map;
		private $elements;
		private $bt_bb_array;

		function __construct( $arr ) {
			$this->slug = $arr['slug'];
			$this->edit_slug = $arr['slug'] . '-' . 'edit';
			$this->single_name = $arr['single_name'];
			$this->plural_name = $arr['plural_name'];
			$this->icon = $arr['icon'];
			$this->home_url = $arr['home_url'];
			$this->doc_url = $arr['doc_url'];
			$this->support_url = $arr['support_url'];
			$this->shortcode = $arr['shortcode'];

			$this->map = array();
			$this->elements = array();
			$this->bt_bb_array = array();
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue' ), 20 ); // after BB
			add_action( 'admin_head', array( $this, 'map_js' ) );
			add_action( 'admin_footer', array( $this, 'js_settings' ) );
			add_action( 'admin_footer', array( $this, 'translate' ) );
			add_action( 'admin_menu', array( $this, 'admin_menu' ) );
			add_action( 'admin_menu', array( $this, 'calculator_page' ) );
			add_action( 'admin_notices', array( $this, 'admin_notices' ) );
			add_action( 'init', array( $this, 'create_post_type' ) );

			add_shortcode( $this->shortcode, array( $this, 'add_shortcode' ) );

			add_action( 'init', array( $this, 'create_calculator_table_base' ) );
		}



		function create_calculator_table_base() {

			global $wpdb;
		
			$charset_collate = "DEFAULT CHARACTER SET {$wpdb->charset} COLLATE {$wpdb->collate}";
		
			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		
			$table_name = $wpdb->prefix . 'calculcator_base';
		
			$sql1 = "CREATE TABLE $table_name (
			`id` int(11) NOT NULL,
			`created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
			`payment_id` int(12) DEFAULT NULL,
			`form_summ` float DEFAULT NULL,
			`quote_line` text,
			`destination_city` varchar(220) DEFAULT NULL,
			`total_quote` int(225) DEFAULT NULL,
			`calculator_insurance` varchar(220) DEFAULT NULL,
			`name_form_email_me` varchar(50) DEFAULT NULL,
			`email_form_email_me` varchar(220) DEFAULT NULL,
			`phone_form_email_me` int(30) DEFAULT NULL,
			`address_form_email_me` varchar(220) DEFAULT NULL,
			`message_form_email_me` text,
			`date_collection` varchar(50) DEFAULT NULL,
			`name_collection` varchar(50) DEFAULT NULL,
			`email_collection` varchar(225) DEFAULT NULL,
			`phone_collection` int(30) DEFAULT NULL,
			`address_1_collection` varchar(220) DEFAULT NULL,
			`address_2_collection` varchar(220) DEFAULT NULL,
			`city_collection` varchar(50) DEFAULT NULL,
			`postcode_collection` int(20) DEFAULT NULL,
			`country_destination` varchar(50) DEFAULT NULL,
			`name_destination` varchar(50) DEFAULT NULL,
			`email_destination` varchar(220) DEFAULT NULL,
			`phone_destination` int(30) DEFAULT NULL,
			`address_1_destination` varchar(220) DEFAULT NULL,
			`address_2_destination` varchar(220) DEFAULT NULL,
			`city_destination` varchar(50) DEFAULT NULL,
			`postcode_destination` int(20) DEFAULT NULL,
			`message_payment` text
			) $charset_collate;";
			dbDelta( $sql1 );
		}





		/**
		 * Enqueue
		 */

		function enqueue() {
			$screen = get_current_screen();
			if ( ! strpos( $screen->base, $this->edit_slug ) ) {
				return;
			}

			wp_enqueue_style( 'bt-bb-light-font-awesome.min', plugins_url( 'css/font-awesome.min.css', __FILE__ ) );
			wp_enqueue_style( 'bt-bb-light', plugins_url( 'css/style.crush.css', __FILE__ ) );
			wp_enqueue_style( 'bt-bb-light-override', plugins_url( 'css/override.css', __FILE__ ) );

			wp_enqueue_script( 'bt-bb-light-react', plugins_url( 'react.min.js', __FILE__ ) );
			wp_enqueue_script( 'bt-bb-light', plugins_url( 'script.min.js', __FILE__ ), array( 'jquery' ), true );
			wp_enqueue_script( 'bt-bb-light-jsx', plugins_url( 'build/jsx.min.js', __FILE__ ), array( 'jquery' ), true );
			wp_enqueue_script( 'bt-bb-light-misc', plugins_url( 'misc.min.js', __FILE__ ), array( 'jquery' ), true );
			wp_enqueue_script( 'bt-bb-light-autosize', plugins_url( 'autosize.min.js', __FILE__ ) );

			wp_enqueue_script( 'wp-color-picker' );

			wp_enqueue_style( 'wp-color-picker' );
		}

		/**
		 * Translate
		 */

		function translate() {
			echo '<script>';
				echo 'window.bt_bb_text = [];';
				echo 'window.bt_bb_text.toggle = "' . __( 'Toggle', 'bt-cost-calculator' ) . '";';
				echo 'window.bt_bb_text.add = "' . __( 'Add', 'bt-cost-calculator' ) . '";';
				echo 'window.bt_bb_text.edit = "' . __( 'Edit', 'bt-cost-calculator' ) . '";';
				echo 'window.bt_bb_text.edit_content = "' . __( 'Edit Content', 'bt-cost-calculator' ) . '";';
				echo 'window.bt_bb_text.clone = "' . __( 'Clone', 'bt-cost-calculator' ) . '";';
				echo 'window.bt_bb_text.delete = "' . __( 'Delete', 'bt-cost-calculator' ) . '";';
				echo 'window.bt_bb_text.layout_error = "' . __( 'Layout error!', 'bt-cost-calculator' ) . '";';
				echo 'window.bt_bb_text.add_element = "' . __( 'Add Element', 'bt-cost-calculator' ) . '";';
				echo 'window.bt_bb_text.select_layout = "' . __( 'Select Layout', 'bt-cost-calculator' ) . '";';
				echo 'window.bt_bb_text.select = "' . __( 'Select', 'bt-cost-calculator' ) . '";';
				echo 'window.bt_bb_text.submit = "' . __( 'Submit', 'bt-cost-calculator' ) . '";';
				echo 'window.bt_bb_text.copy = "' . __( 'Copy', 'bt-cost-calculator' ) . '";';
				echo 'window.bt_bb_text.copy_plus = "' . __( 'Copy +', 'bt-cost-calculator' ) . '";';
				echo 'window.bt_bb_text.paste = "' . __( 'Paste', 'bt-cost-calculator' ) . '";';
				echo 'window.bt_bb_text.export = "' . __( 'Export', 'bt-cost-calculator' ) . '";';
				echo 'window.bt_bb_text.import = "' . __( 'Import', 'bt-cost-calculator' ) . '";';
				echo 'window.bt_bb_text.not_allowed = "' . __( 'Not allowed!', 'bt-cost-calculator' ) . '";';
				echo 'window.bt_bb_text.manage_cb = "' . __( 'Manage Clipboard', 'bt-cost-calculator' ) . '";';
				echo 'window.bt_bb_text.filter = "' . __( 'Filter...', 'bt-cost-calculator' ) . '";';
				echo 'window.bt_bb_text.sc_mapper = "' . __( 'Shortcode Mapper', 'bt-cost-calculator' ) . '";';
				echo 'window.bt_bb_text.insert_mapping = "' . __( 'Insert Mapping', 'bt-cost-calculator' ) . '";';
				echo 'window.bt_bb_text.save = "' . __( 'Save', 'bt-cost-calculator' ) . '";';
				echo 'window.bt_bb_text.switch_editor = "' . __( 'Switch Editor', 'bt-cost-calculator' ) . '";';
				echo 'window.bt_bb_text.custom_css = "' . __( 'Custom CSS', 'bt-cost-calculator' ) . '";';
				echo 'window.bt_bb_text.switch_editor_confirm = "' . __( 'Are you sure you want to switch editor?', 'bt-cost-calculator' ) . '";';
				echo 'window.bt_bb_text.general = "' . __( 'General', 'bt-cost-calculator' ) . '";';
			echo '</script>';
		}

		/**
		 * Settings
		 */

		function js_settings() {
			$screen = get_current_screen();
			if ( ! strpos( $screen->base, $this->edit_slug ) ) {
				return;
			}
			
			echo '<script>';
				echo 'window.bt_bb_settings = [];';
				echo 'window.bt_bb_settings.tag_as_name = "0";';

				echo 'window.BTAJAXURL = "' . esc_js( admin_url( 'admin-ajax.php' ) ) . '";';

				echo 'window.bt_bb.is_bb_content = true;';

			echo '</script>';
		}

		/**
		 * Map shortcodes (js)
		 */

		function map_js() {
			
			if ( is_admin() ) { // back end
				$screen = get_current_screen();
				if ( ! strpos( $screen->base, $this->edit_slug ) ) {
					return;
				}
			}
			
			echo '<script>';
				foreach( $this->elements as $base => $params ) {
					$proxy = new BTBB_Light_Map_Proxy( $base, $params, $this->map );
					$proxy->js_map();
				}
			echo '</script>';
		}

		/**
		 * Map shortcodes
		 */
		function map( $base, $params ) {
			$i = 0;
			if ( isset( $params['params'] ) ) {
				foreach( $params['params'] as $param ) {
					if ( ! isset( $param['weight'] ) ) {
						$params['params'][ $i ]['weight'] = $i;
					}
					$i++;
				}
			}
			$this->elements[ $base ] = $params;
		}

		/**
		 * Prints the box content.
		 * 
		 * @param WP_Post $post The object for the current post/page.
		 */
		function show( $post_content ) {

			$this->do_shortcode( $post_content );

			$json_content = json_encode( $this->bt_bb_array );

			echo '<div id="bt_bb_sectionid"><div class="inside">';
			
			echo '<div id="bt_bb"></div><div id="bt_bb_add_root"><i></i></div>';
			
			echo '<div id="bt_bb_dialog" class="bt_bb_dialog">';
				echo '<div class="bt_bb_dialog_header"><div class="bt_bb_dialog_close"></div><span></span></div>';
				echo '<div class="bt_bb_dialog_header_tools"></div>';
				echo '<div class="bt_bb_dialog_content">';
				echo '</div>';
				echo '<div class="bt_bb_dialog_tinymce">';
					echo '<div class="bt_bb_dialog_tinymce_editor_container">';
						wp_editor( '' , 'bt_bb_tinymce', array( 'textarea_rows' => 12 ) );
					echo '</div>';
					echo '<input type="button" class="bt_bb_dialog_button bt_bb_edit button button-small" value="' . __( 'Submit', 'bt-cost-calculator' ) . '">';
				echo '</div>';
			echo '</div>';

			echo '<div id="bt_bb_main_toolbar">';
			echo '<i class="bt_bb_undo" title="' . __( 'Undo', 'bt-cost-calculator' ) . '"></i>';
			echo '<i class="bt_bb_redo" title="' . __( 'Redo', 'bt-cost-calculator' ) . '"></i>';
				echo '<span class="bt_bb_separator">|</span>';
			echo '<i class="bt_bb_paste_root" title="' . __( 'Paste', 'bt-cost-calculator' ) . '"></i>';
			echo '<span class="bt_bb_cb_items"></span>';
			echo '<i class="bt_bb_manage_clipboard" title="' . __( 'Clipboard Manager', 'bt-cost-calculator' ) . '"></i>';
				echo '<span class="bt_bb_separator">|</span>';
			echo '<i class="bt_bb_save bt_bb_disabled" title="' . __( 'Save', 'bt-cost-calculator' ) . '"></i>';
			echo '</div>';

			echo '</div></div>';

			add_action( 'admin_footer', array( new BTBB_Light_Data_Proxy( $json_content ), 'js' ) );

		}

		function do_shortcode( $content ) {
			global $shortcode_tags;
			if ( ! ( ( empty( $shortcode_tags ) || ! is_array( $shortcode_tags ) ) ) ) {
				$pattern = get_shortcode_regex();
				
				$callback = new BTBB_Light_Callback( $this->bt_bb_array, $this->map );
				
				$preg_cb = preg_replace_callback( "/$pattern/s", array( $callback, 'bt_bb_do_shortcode_tag' ), $content );
			}
		}

		function add_shortcode( $atts ) {
			$a = shortcode_atts( array(
				'id' => ''
			), $atts );

			if ( $atts['id'] != '' ) {
				$args = array(
					'include' => $atts['id'],
					'post_type' => $this->slug,
				);
				$posts_array = get_posts( $args );
			}
			
			if ( isset( $posts_array[0]->post_content ) ) {
				return do_shortcode( $posts_array[0]->post_content );
			} else {
				return null;
			}
			
		}

		// create post type
		function create_post_type() {
			register_post_type( $this->slug,
				array(
					'labels' => array(
						'name' => $this->plural_name,
						'singular_name' => $this->single_name
					),
					'rewrite' => false,
					'query_var' => false,
				)
			);
		}

		// admin menu
		function admin_menu() {
			global $_wp_last_object_menu;

			$_wp_last_object_menu++;

			add_menu_page( $this->single_name,
				$this->single_name,
				'edit_posts', 
				$this->slug,
				array( $this, 'admin_management_page' ), 
				$this->icon,
				$_wp_last_object_menu );

			$edit = add_submenu_page( $this->slug,
				__( 'Edit ', 'bt-cost-calculator' ) . $this->single_name,
				$this->plural_name,
				'edit_posts', $this->slug,
				array( $this, 'admin_management_page' ) );

			add_action( 'load-' . $edit, array( $this, 'load_admin' ) );

			add_submenu_page( $this->slug,
				__( 'Add New ', 'bt-cost-calculator' ) . $this->single_name,
				__( 'Add New', 'bt-cost-calculator' ),
				'edit_posts', $this->edit_slug,
				array( $this, 'admin_edit_page' ) );

		}

		// Calculator page
		function calculator_page(){    
			$page_title = 'Table Calculator';   
			$menu_title = 'Table Data';   
			$capability = 'edit_posts';   
			$menu_slug  = 'extra-post-info';   
			$function   = array( $this, 'calculator_table_admin_board' );   
			$icon_url   = 'dashicons-media-code';   
			$position   = 8;
		
			add_menu_page( 
				$page_title,
				$menu_title,                   
				$capability,                   
				$menu_slug,                   
				$function,                   
				$icon_url,                   
				$position 
			); } 
		
			function calculator_table_admin_board(){
        
				?>
				<table id="calculater_table_id" class="calculater_table" cellpadding="10" style="margin: 30px 0; padding: 0 15px">
					<thead style="text-align: left; background: #0073AA; color: #fff;">
					<tr>
						<th>Order id</th>
						<th>Requested collection date</th>
						<th>Name Collection</th>
						<th>Email Collection</th>
						<th>Phone Collection</th>
						<th>Address Collection</th>
						<th>City Collection</th>
						<th>Postcode Collection</th>
						<th>Country Destination</th>
						<th>Name Destination</th>
						<th>Email Destination</th>
						<th>Phone Destination</th>
						<th>Address Destination</th>
						<th>City Destination</th>
						<th>Postcode Destination</th>
						<th>Insurance</th>						
						<th>Total items</th>
						<th>Total sum</th>
						<th style="width:10%;">Items</th>
					</tr>
					</thead>
					<tbody>
						<?php
						global $wpdb;
						$results = $wpdb->get_results ( "SELECT * FROM rmje_calculcator_base ORDER BY created_at DESC" );
						//  echo '<pre>' . print_r($results, true) . '</pre>';
						// var_dump($results);
						foreach ($results as $result) {
							?>						
								<tr>
									<td ><?php echo $result->payment_id ?></td>
									<td><?php echo $result->date_collection ?></td>
									<td><?php echo $result->name_collection ?></td>
									<td><?php echo $result->email_collection ?></td>
									<td><?php echo $result->phone_collection ?></td>
									<td><?php echo $result->address_1_collection . ' ' . $result->address_2_collection ?></td>
									<td><?php echo $result->city_collection ?></td>
									<td><?php echo $result->postcode_collection ?></td>
									<td><?php echo $result->country_destination ?></td>
									<td><?php echo $result->name_destination ?></td>
									<td><?php echo $result->email_destination ?></td>
									<td><?php echo $result->phone_destination ?></td>
									<td><?php echo $result->address_1_destination . ' ' . $result->address_2_destination ?></td>
									<td><?php echo $result->city_destination ?></td>
									<td><?php echo $result->postcode_destination ?></td>
									<td style="min-width:200px;"><?php echo $result->calculator_insurance ?></td>
									<td><?php echo $result->total_quote ?></td>
									<td><?php echo $result->form_summ ?></td>
									<td style="min-width:300px;"><?php $qaaz = json_decode($result->quote_line, true); 

									 	foreach ($qaaz as $item) {
											 if ($item[value] != 0) {
												echo $item[name] . ' - ';
												echo $item[value] . '</br>';
											 }
										 }

									?></td>
								</tr>							
							<?php
						}
						?>
					</tbody>
				</table>
				<style>
					#calculater_table_id tr:nth-child(odd) td{
						background: #fff;
					}

				</style>
				<?php
			}		
		}


		// cpt admin
		function load_admin() {
			$current_screen = get_current_screen();
			add_filter( 'manage_' . $current_screen->id . '_columns', array( 'BTBB_Light_List_Table', 'define_columns' ) );

			// save
			if ( isset( $_POST['action'] ) && $_POST['action'] == 'save' ) {
				$post_id = isset( $_GET['post'] ) ? $_GET['post'] : -1;
				$post_title = $_POST['post_title'] != '' ? $_POST['post_title'] : __( 'Untitled', 'bt-cost-calculator' );
				$post_content = stripslashes( $_POST['post_content'] );
				$query = array();
				if ( $post_id == -1 ) { // new post
					$post_id = wp_insert_post( array(
						'post_type' => $this->slug,
						'post_status' => 'publish',
						'post_title' => $post_title,
						'post_content' => trim( $post_content ),
					) );
					if ( $post_id ) {
						$query['message'] = 'created';
					}
				} else { // update post
					$post_id = wp_update_post( array(
						'ID' => (int) $post_id,
						'post_status' => 'publish',
						'post_title' => $post_title,
						'post_content' => trim( $post_content ),
					) );
					if ( $post_id ) {
						$query['message'] = 'saved';
					}
				}

				if ( $post_id ) {
					$query['post'] = $post_id;
					$redirect_to = add_query_arg( $query, menu_page_url( $this->edit_slug, false ) );
				} else {
					$redirect_to = add_query_arg( $query, menu_page_url( $this->slug, false ) );
				}

				wp_safe_redirect( $redirect_to );

				exit();

			}

			// delete
			else if ( isset( $_GET['action'] ) && $_GET['action'] == 'delete' ) {

				$posts = empty( $_POST['post_ID'] ) ? (array) $_GET['post'] : (array) $_POST['post_ID'];

				$is_deleted = false;

				foreach ( $posts as $post_id ) {

					if ( ! current_user_can( 'delete_posts', $post_id ) ) {
						wp_die( __( 'You are not allowed to delete posts.', 'bt-cost-calculator' ) );
					}

					$deleted = wp_delete_post( $post_id, true );
					if ( $deleted ) {
						$is_deleted = true;
					}

				}

				$query = array();

				if ( $is_deleted ) {
					if ( count( $posts ) > 1 ) {
						$query['message'] = 'posts_deleted';
					} else { 
						$query['message'] = 'post_deleted';
					}
				}

				$redirect_to = add_query_arg( $query, menu_page_url( $this->slug, false ) );

				wp_safe_redirect( $redirect_to );

				exit();

			}
		}

		// management page
		function admin_management_page() {

			// table
			$list_table = new BTBB_Light_List_Table( $this->slug, $this->shortcode );
			$list_table->prepare_items();
			?>
			<div class="wrap">

			<h1 class="wp-heading-inline"><?php
				echo esc_html( $this->plural_name );
			?></h1>

			<?php
				if ( current_user_can( 'edit_posts' ) ) {
					echo sprintf( '<a href="%1$s" class="add-new-h2">%2$s</a>',
						esc_url( menu_page_url( $this->edit_slug, false ) ),
						esc_html( __( 'Add New', 'bt-cost-calculator' ) ) );
				}

				if ( ! empty( $_REQUEST['s'] ) ) {
					echo sprintf( '<span class="subtitle">'
						. __( 'Search results for &#8220;%s&#8221;', 'bt-cost-calculator' )
						. '</span>', esc_html( $_REQUEST['s'] ) );
				}
			?>

			<hr class="wp-header-end">

			<form method="get" action="">
				<input type="hidden" name="page" value="<?php echo esc_attr( $_REQUEST['page'] ); ?>" />
				<?php $list_table->search_box( __( 'Search', 'bt-cost-calculator' ), $this->slug ); ?>
				<?php $list_table->display(); ?>
			</form>

			</div>
			<?php
		}

		// edit page
		function admin_edit_page() {

			$post_type = $this->slug;

			$post_title = '';
			$post_content = '';

			$post_id = isset( $_GET['post'] ) ? $_GET['post'] : -1;
			
			if ( $post_id > 0 ) {
				$post = get_post( $post_id );
				$post_title = $post->post_title;
				$post_content = $post->post_content;
			}

			?>

			<div class="wrap">

			<h1 class="wp-heading-inline"><?php
				if ( $post_id == -1 ) {
					echo __( 'Add New ', 'bt-cost-calculator' ) . $this->single_name;
				} else {
					echo __( 'Edit ', 'bt-cost-calculator' ) . $this->single_name;
				}
			?></h1>

			<form method="post" action="<?php echo esc_url( add_query_arg( array( 'post' => $post_id ), menu_page_url( $this->slug, false ) ) ); ?>" id="' . $this->slug . '-form">

				<?php wp_nonce_field(); ?>
				
				<input type="hidden" id="post_ID" name="post_ID" value="<?php echo $post_id; ?>">
				<input type="hidden" id="hiddenaction" name="action" value="save">
				<input type="hidden" id="post_content" name="post_content" value="">

				<div id="poststuff">
				<div id="post-body" class="metabox-holder columns-2">
				<div id="post-body-content">
				<div id="titlediv">
				<div id="titlewrap">

					<label id="title-prompt-text" for="title" class="screen-reader-text"><?php esc_html_e( 'Enter title here', 'bt-cost-calculator' ); ?></label>
					<input type="text" name="post_title" size="30" value="<?php echo $post_title; ?>" id="title" spellcheck="true" autocomplete="off">

				</div><!-- #titlewrap -->

				<?php if ( $post_id > 0 ) { ?>

				<div class="inside">
					<p class="description">
					<label for="bt_bb_light_shortcode"><?php echo __( 'Copy this shortcode and paste it into your post, page, or text widget content:', 'bt-cost-calculator' ); ?></label>
					<span><input type="text" id="bt_bb_light_shortcode" onfocus="this.select();" readonly="readonly" class="large-text code" value="[<?php echo esc_html( $this->shortcode ); ?> id=&quot;<?php echo $post_id; ?>&quot;]"></span>
					</p>
				</div>

				<?php } ?>

				</div><!-- #titlediv -->
				</div><!-- #post-body-content -->

				<div id="postbox-container-1" class="postbox-container">

				<?php if ( current_user_can( 'edit_posts', $post_id ) ) { ?>
					<div id="submitdiv" class="postbox">
					<h2><span><?php esc_html_e( 'Status', 'bt-cost-calculator' ); ?></span></h2>
					<div class="inside">
					<div class="submitbox" id="submitpost">

					<div id="major-publishing-actions">

					<div id="publishing-action">
						<span class="spinner"></span>
						<input id="save" type="submit" class="button-primary" value="<?php esc_html_e( 'Save', 'bt-cost-calculator' ); ?>" disabled>
					</div>
					<div class="clear"></div>
					</div><!-- #major-publishing-actions -->
					</div><!-- #submitpost -->
					</div>
					</div><!-- #submitdiv -->
				<?php } ?>

				<div id="informationdiv" class="postbox">
				<h2 class="hndle"><span><?php esc_html_e( 'Info', 'bt-cost-calculator' ); ?></span></h2>
				<div class="inside">
				<ul>
				<li><a href="<?php echo esc_url_raw( $this->home_url ); ?>" target="_blank"><?php esc_html_e( 'Home page', 'bt-cost-calculator' ); ?></a></li>
				<li><a href="<?php echo esc_url_raw( $this->doc_url ); ?>" target="_blank"><?php esc_html_e( 'Documentation', 'bt-cost-calculator' ); ?></a></li>
				<li><a href="<?php echo esc_url_raw( $this->support_url ); ?>" target="_blank"><?php esc_html_e( 'Support', 'bt-cost-calculator' ); ?></a></li>
				</ul>
				</div>
				</div><!-- #informationdiv -->

				</div><!-- #postbox-container-1 -->

				<div id="postbox-container-2" class="postbox-container">

				<div id="bt-bb-light-editor" class="postbox">
					<?php $this->show( $post_content ); ?>
				</div>

				</div><!-- #postbox-container-2 -->

				</div><!-- #post-body -->
				<br class="clear" />
				</div><!-- #poststuff -->

			</form>

			<script>
				window.bt_bb_light_post_type = '<?php echo $post_type; ?>';

				if ( '' === jQuery( '#title' ).val() ) {
					jQuery( '#title' ).focus();
				}
				var $title = jQuery( '#title' );
				var $titleprompt = jQuery( '#title-prompt-text' );

				if ( '' === $title.val() ) {
					$titleprompt.removeClass( 'screen-reader-text' );
				}

				$titleprompt.click( function() {
					jQuery( this ).addClass( 'screen-reader-text' );
					$title.focus();
				} );

				$title.blur( function() {
					if ( '' === jQuery( this ).val() ) {
						$titleprompt.removeClass( 'screen-reader-text' );
					}
				} ).focus( function() {
					$titleprompt.addClass( 'screen-reader-text' );
					jQuery( '#save' ).prop( 'disabled', false );
					jQuery( 'i.bt_bb_save' ).removeClass( 'bt_bb_disabled' );
				} ).keydown( function( e ) {
					$titleprompt.addClass( 'screen-reader-text' );
					jQuery( this ).unbind( e );
					jQuery( '#save' ).prop( 'disabled', false );
					jQuery( 'i.bt_bb_save' ).removeClass( 'bt_bb_disabled' );
				} );
			</script>

			<?php

		}

		// admin notices
		function admin_notices() {
			if ( empty( $_REQUEST['message'] ) ) {
				return;
			}

			if ( 'created' == $_REQUEST['message'] ) {
				$updated_message = esc_html__( 'Post created.', 'bt-cost-calculator' );
			} elseif ( 'saved' == $_REQUEST['message'] ) {
				$updated_message = esc_html__( 'Post saved.', 'bt-cost-calculator' );
			} elseif ( 'post_deleted' == $_REQUEST['message'] ) {
				$updated_message = esc_html__( 'Post deleted.', 'bt-cost-calculator' );
			} elseif ( 'posts_deleted' == $_REQUEST['message'] ) {
				$updated_message = esc_html__( 'Posts deleted.', 'bt-cost-calculator' );
			}

			if ( ! empty( $updated_message ) ) {
				echo sprintf( '<div id="message" class="notice notice-success is-dismissible"><p>%s</p></div>', esc_html( $updated_message ) );
				return;
			}
		}

	}
	


if ( ! class_exists( 'BTBB_Light_Map_Proxy' ) ) {

	class BTBB_Light_Map_Proxy {
		function __construct( $base, $params, &$map ) {
			$this->base = $base;
			$params['base'] = $base;
			$this->params = $params;
		}

		public function js_map() {
			if ( shortcode_exists( $this->base ) ) {
				if ( isset( $this->params['admin_enqueue_css'] ) ) {
					foreach( $this->params['admin_enqueue_css'] as $item ) {
						wp_enqueue_style( 'bt_bb_admin_' . uniqid(), $item );
					}
				}
				echo 'window.bt_bb_map["' . $this->base . '"] = window.bt_bb_map_primary.' . $this->base . ' = ' . json_encode( $this->params ) . ';';
				$map[ $this->base ] = $this->params;
			}
		}
	}
	
}

/**
 * Initial data.
 */

if ( ! class_exists( 'BTBB_Light_Data_Proxy' ) ) {

	class BTBB_Light_Data_Proxy {
		function __construct( $data ) {
			$this->data = $data;
		}
		public function js() {
			echo '<script>window.bt_bb_data = { title: "_root", base: "_root", key: "' . uniqid( 'bt_bb_' ) . '", children: ' . $this->data . ' };</script>';
		}
	}
	
}

if ( ! class_exists( 'BTBB_Light_Callback' ) ) {

	class BTBB_Light_Callback {

		private $bt_bb_array;

		function __construct( &$bt_bb_array, &$map ) {
			$this->bt_bb_array = &$bt_bb_array;
		}

		function bt_bb_do_shortcode_tag( $m ) {

			// allow [[foo]] syntax for escaping a tag
			if ( $m[1] == '[' && $m[6] == ']' ) {
				return $m[0];
			}

			$tag = $m[2];
			$attr = shortcode_parse_atts( $m[3] );

			if ( is_array( $attr ) ) {
				$this->bt_bb_array[] = array( 'title' => $tag, 'base' => $tag, 'key' => str_replace( '.', '', uniqid( 'bt_bb_', true ) ), 'attr' => json_encode( $attr ), 'children' => array() );
			} else {
				$this->bt_bb_array[] = array( 'title' => $tag, 'base' => $tag, 'key' => str_replace( '.', '', uniqid( 'bt_bb_', true ) ), 'children' => array() );
			}

			if ( isset( $m[5] ) && $m[5] != '' ) {
				// enclosing tag - extra parameter
				$pattern = get_shortcode_regex();
				
				if ( isset( $map[ $m[2] ]['accept']['_content'] ) && $map[ $m[2] ]['accept']['_content'] ) {
					$r = $m[5];
				} else {
					$callback = new BTBB_Light_Callback( $this->bt_bb_array[ count( $this->bt_bb_array ) - 1 ]['children'], $map );
					$r = preg_replace_callback( "/$pattern/s", array( $callback, 'bt_bb_do_shortcode_tag' ), $m[5] );
					$r = trim( $r );
				}
			
				if ( $r != '' ) {
					$this->bt_bb_array[ count( $this->bt_bb_array ) - 1 ]['children'][0] = array( 'title' => '_content', 'base' => '_content', 'content' => $r, 'children' => array() );
				}
			}
		}	
	}	
	
}

