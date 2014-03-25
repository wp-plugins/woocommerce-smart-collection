<?php
class DC_Woo_Smart_Collection_Admin {
  
  public $settings;

	public function __construct() {
		//admin script and style
		add_action('admin_enqueue_scripts', array(&$this, 'enqueue_admin_script'));
		
		add_action('dc_Woo_Smart_Collection_dualcube_admin_footer', array(&$this, 'dualcube_admin_footer_for_dc_Woo_Smart_Collection'));
		
		add_action( 'save_post', array(&$this, 'assign_woo_smart_collection') );

		$this->load_class('settings');
		$this->settings = new DC_Woo_Smart_Collection_Settings();
	}
	
	public function assign_woo_smart_collection($product_id) {
	  
	  // If this is just a revision, don't send the email.
    if ( wp_is_post_revision( $product_id ) )
      return;
  
    if( get_post_type($product_id) != 'product' )
      return;
    
    $product_categories = get_terms( 'product_cat', array( 'hide_empty' => 0 ) );
    if(count($product_categories) == 0)
      return;
    
    $smart_cat_settings = get_Woo_Smart_Collection_settings('', 'dc_WC_SC_general');
    
    if(!$smart_cat_settings['is_enable'])
      return;
    
    $product_title = get_the_title( $product_id );
    $product_tags = wp_get_object_terms( $product_id, 'product_tag', array('fields' => 'all') );
    
    $smart_cats = array();
    
    // Choose Samrt Cats from Post Title
    if($smart_cat_settings['is_title']) {
      foreach($product_categories as $product_category) {
        if(strpos(strtolower($product_title), strtolower($product_category->name)) !== false) {
          $smart_cats[] = $product_category->term_id;
        }
      }
    }
    
    // Choose Samrt Cats from associated Tags
    if($smart_cat_settings['is_tag']) {
      if(!empty($product_tags)) {
        foreach($product_tags as $product_tag) {
          foreach($product_categories as $product_category) {
            if(strtolower($product_category->name) == strtolower($product_tag->name)) {
              $smart_cats[] = $product_category->term_id;
            }
          }
        }
      }
    }
    
    if(!empty($smart_cats)) {
      $smart_cats = array_map('intval', $smart_cats);
      $smart_cats = array_unique( $smart_cats );
      $old_smart_cats = (get_post_meta($product_id, '_smart_cats', true)) ? get_post_meta($product_id, '_smart_cats', true) : array();
      if(!empty($old_smart_cats)) wp_remove_object_terms( $product_id, $old_smart_cats, 'product_cat' );
      
      if($smart_cat_settings['is_append']) {
        $smart_cats = array_merge((array)$smart_cats, (array)$old_smart_cats);
        $smart_cats = array_unique( $smart_cats );
      }
        
      wp_set_object_terms( $product_id, $smart_cats, 'product_cat', true );
        
      update_post_meta($product_id, '_smart_cats', $smart_cats);
    }
    
	}

	function load_class($class_name = '') {
	  global $DC_Woo_Smart_Collection;
		if ('' != $class_name) {
			require_once ($DC_Woo_Smart_Collection->plugin_path . '/admin/class-' . esc_attr($DC_Woo_Smart_Collection->token) . '-' . esc_attr($class_name) . '.php');
		} // End If Statement
	}// End load_class()
	
	function dualcube_admin_footer_for_dc_Woo_Smart_Collection() {
    global $DC_Woo_Smart_Collection;
    ?>
    <div style="clear: both"></div>
    <div id="dc_admin_footer">
      <?php _e('Powered by', $DC_Woo_Smart_Collection->text_domain); ?> <a href="http://dualcube.com" target="_blank"><img src="<?php echo $DC_Woo_Smart_Collection->plugin_url.'/assets/images/dualcube.png'; ?>"></a><?php _e('Dualcube', $DC_Woo_Smart_Collection->text_domain); ?> &copy; <?php echo date('Y');?>
    </div>
    <?php
	}

	/**
	 * Admin Scripts
	 */

	public function enqueue_admin_script() {
		global $DC_Woo_Smart_Collection;
		$screen = get_current_screen();
		
		// Enqueue admin script and stylesheet from here
		if (in_array( $screen->id, array( 'toplevel_page_dc-WC-SC-setting-admin' ))) :   
		  $DC_Woo_Smart_Collection->library->load_qtip_lib();
		  $DC_Woo_Smart_Collection->library->load_upload_lib();
		  $DC_Woo_Smart_Collection->library->load_colorpicker_lib();
		  $DC_Woo_Smart_Collection->library->load_datepicker_lib();
		  wp_enqueue_script('admin_js', $DC_Woo_Smart_Collection->plugin_url.'assets/admin/js/admin.js', array('jquery'), $DC_Woo_Smart_Collection->version, true);
		  wp_enqueue_style('admin_css',  $DC_Woo_Smart_Collection->plugin_url.'assets/admin/css/admin.css', array(), $DC_Woo_Smart_Collection->version);
	  endif;
	}
}