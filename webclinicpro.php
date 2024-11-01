<?php
/**
 * @package webClinicPro
 */
/*
Plugin Name: webClinic Pro
Plugin URI: https://www.webclinicpro.com/
Description: Implementation of security for webClinic Pro customers.
Version: 1.1.0
Author: webClinic Pro
Author URI: https://www.webclinicpro.com
License: GPLv2 or later
Text Domain: webclinicpro
License: GPLv2 or later

Copyright 2015 webClinicPro
*/



class WebClinicPro
{
    
    
    /** @var string The plugin version number */
	var $version = '1.1.0';
	
	
	/** @var array The plugin settings array */
	var $settings = array();
    
    
    
    
    /*
	*  Constructor
	*
	*  This function will construct all the neccessary actions, filters and functions for the webclinicpro plugin to work
	*
	*/
	function __construct()
    {
		
		// vars
		$this->settings = array(
			'name'				=> __('webClinic Pro', 'webclinicpro'),
			'version'			=> $this->version,
		);
		
		// actions
		add_action('init', array($this, 'init'), 1);
		
		// filters
//		add_filter('webclinicpro/get_info', array($this, 'get_info'), 1, 1);

		// includes
		$this->include_stuff();
		
	}
    
    
    
    
    /*
	*  include_stuff
	*
	*  This function will include core files before the theme's functions.php file has been excecuted.
	*  
	*/
	function include_stuff()
	{
        
        include_once('core/ssl_control.php');
        include_once('core/url_control.php');
        
        
        if (is_admin()) {
            include_once('core/options_view.php');
        }

	}
    
    
    
    
   /*
	*  init
	*
	*  This function is called during the 'init' action and will do things such as:
	*  create post_type, register scripts, add actions / filters
    *
	*/
	function init()
	{
        
        // admin only
		if( is_admin() ) {
			add_action('admin_menu', array($this,'admin_menu'));
		}
        
        // Handle Plugin duties
        if (get_option('webclinicpro_status')) {
            
            
            /**
             *  SSL Duties
             */
            $ssl_control = new WebClinicPro_SSLControl();
            
            // Force SSL
            if (get_option('webclinicpro_force_ssl')) {
                add_action('init', array($ssl_control, 'sslcontrol_force_ssl'));
            }
            // Mixed Content
            if (get_option('webclinicpro_mixed_content')) {
                add_filter('the_title', array($ssl_control, 'sslcontrol_fix_content'), 998);
                add_filter('the_content', array($ssl_control, 'sslcontrol_fix_content'), 999);
                add_filter('the_tags', array($ssl_control, 'sslcontrol_fix_content'));
                add_filter('the_excerpt', array($ssl_control, 'sslcontrol_fix_content'));
            }
            
            
            /**
             *  URL Duties
             */
            $url_control = new WebClinicPro_URLControl();
            
            if (get_option('webclinicpro_relative_url')) {
                
                // Relative Links
                add_filter('the_permalink', array($url_control, 'urlcontrol_make_it_relative'));
                add_filter('post_link', array($url_control, 'urlcontrol_make_it_relative'));
                add_filter('post_type_link', array($url_control, 'urlcontrol_make_it_relative'), 10, 2);
                
                // Filters to catch absolute page links
                add_filter('page_link', array($url_control, 'urlcontrol_make_it_relative'));
                add_filter('page_type_link', array($url_control, 'urlcontrol_make_it_relative'), 10, 2);

                // Archive Links
                add_filter('get_archives_link', array($url_control, 'urlcontrol_make_it_relative'));
                
                // Author Links
                add_filter('author_link', array($url_control, 'urlcontrol_make_it_relative'));

                // Category Links
                add_filter('category_link', array($url_control, 'urlcontrol_make_it_relative'));

                //Filters to make the scripts and style urls to relative
                add_filter('script_loader_src', array($url_control, 'urlcontrol_make_it_relative'));
                add_filter('style_loader_src', array($url_control, 'urlcontrol_make_it_relative'));

                //Filter to make the media(image) src to relative
                add_filter('wp_get_attachment_url', array($url_control, 'urlcontrol_make_it_relative'));
                add_filter('wp_calculate_image_srcset', array($url_control, 'urlcontrol_make_srcset_relative'));

                
                // Filter to catch absolute links within content
                add_filter('the_content', array($url_control, 'urlcontrol_relative_content'));
                
                
                // Use Absolute URL's for XML Sitemap
                if ( defined( 'WPSEO_VERSION' ) ) {
                    
                    add_filter('wpseo_sitemap_entry', array($url_control, 'urlcontrol_make_it_absolute'), 999);
                    add_filter('wpseo_xml_sitemap_post_url', array($url_control, 'urlcontrol_make_it_absolute'));
                    
                    //add_filter('wpseo_sitemap_post_type_archive_link', array($url_control, 'urlcontrol_make_it_absolute'));
                    //add_filter('wpseo_xml_sitemap_img_src', array($url_control, 'urlcontrol_make_it_absolute'));
                    
                    // Author Links
                    remove_filter('author_link', array($url_control, 'urlcontrol_make_it_relative'));
                    add_filter('author_link', array($url_control, 'urlcontrol_make_it_absolute'));

                    // Category Links
                    remove_filter('category_link', array($url_control, 'urlcontrol_make_it_relative'));
                    add_filter('category_link', array($url_control, 'urlcontrol_make_it_absolute'));
                    
                    //remove_filter('page_link', array($url_control, 'urlcontrol_make_it_relative'));
                    //add_filter('page_link', array($url_control, 'urlcontrol_make_it_absolute'));
                    
                }
                
            }
        }
        
    }
    
    
    
    
    /*
	*  admin_menu
	*/
	function admin_menu()
	{
        
        $option_view = new WebClinicPro_OptionsView();
        
		add_menu_page(__("webClinic Pro",'webclinicpro'), __("webClinic Pro",'webclinicpro'), 'manage_options', 'webclinicpro', array($option_view,'webclinicpro_options_page'), '/wp-content/plugins/webclinicpro/images/wcp-square_light-sm.png');
        add_action('admin_init', array($option_view, 'webclinicpro_register_settings') );
        
	}
    
    
}




/*
*  webclinicpro
*
*  The main function responsible for returning the webclinicpro Instance to functions everywhere.
*  Use this function like a global variable, except without needing to declare the global.
*
*/
function webclinicpro()
{
    
    global $webclinicpro;
    
    if( !isset($webclinicpro) )
    {
        $webclinicpro = new webclinicpro();
    }
    
    return $webclinicpro;
    
}


// initialize
webclinicpro();
