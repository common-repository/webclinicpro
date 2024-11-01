<?php
/**
 * @package webClinicPro
 */
 
 
class WebClinicPro_OptionsView
{
    
    
     /**
     * Register settings variables.
     */
    function webclinicpro_register_settings()
    {
        
        //register_setting( 'webclinicpro-settings-group', 'webclinicpro_status' );
        register_setting( 'webclinicpro-settings-group', 'webclinicpro_subscription' );
        register_setting( 'webclinicpro-settings-group', 'webclinicpro_subscriber_key' );
        register_setting( 'webclinicpro-settings-group', 'webclinicpro_block_seal' );
        register_setting( 'webclinicpro-settings-group', 'webclinicpro_force_ssl' );
        register_setting( 'webclinicpro-settings-group', 'webclinicpro_mixed_content' );
        register_setting( 'webclinicpro-settings-group', 'webclinicpro_relative_url' );
        
        register_setting( 'webclinicpro-checklist-group', 'webclinicpro_sslcert_check' );
        register_setting( 'webclinicpro-checklist-group', 'webclinicpro_firewall_check' );
        register_setting( 'webclinicpro-checklist-group', 'webclinicpro_login_check' );
        register_setting( 'webclinicpro-checklist-group', 'webclinicpro_backup_check' );
        register_setting( 'webclinicpro-checklist-group', 'webclinicpro_comments_check' );
        register_setting( 'webclinicpro-checklist-group', 'webclinicpro_mixedssl_check' );
        register_setting( 'webclinicpro-checklist-group', 'webclinicpro_relativeurl_check' );
        register_setting( 'webclinicpro-checklist-group', 'webclinicpro_cache_check' );
        
    }

    
    
    
    
    /**
     * Load Options page
     */
    function webclinicpro_options_page()
    {
        
        settings_fields( 'webclinicpro-settings-group' );
        do_settings_sections( 'webclinicpro-settings-group' );
        
        if ($_REQUEST['settings-updated']) {
            
            $pu = parse_url("http://" . $_SERVER['SERVER_NAME']);
            $array = explode(".", $pu['host']);
            $domain = (array_key_exists(count($array) - 2, $array) ? $array[count($array) - 2] : "") . "." . $array[count($array) - 1];
        
            $block = array();
            $data = array(
                "key" => esc_attr( get_option('webclinicpro_subscriber_key') ),
                "style" => 1,
                "domain" => $domain,
            );        

            $file = "https://portal.webclinicpro.com/api/validate.php";
            
            $args = array(
                'method' => 'POST',
                'timeout' => 45,
                'redirection' => 5,
                'httpversion' => '1.0',
                'blocking' => true,
                'headers' => array(),
                'body' => $data,
                'cookies' => array()
                );
        
            $response = wp_remote_post( $file, $args );
            $response = json_decode($response['body']);
                
            echo "<script>console.log(". json_encode($response->result) .");</script>";
            if (isset($response->result)) {
                
                if($response->result->success) {
                    if (!get_option('webclinicpro_status')) {
                        update_option('webclinicpro_status', 1);
                        update_option('webclinicpro_subscription', $response->result->subscription);
                        add_settings_error('webclinicpro_options', '', "Plugin Activated!", 'notice');
                        settings_errors('webclinicpro_options');
                    } else {
                        add_settings_error('webclinicpro_success', '', 'Options Saved', 'notice');
                        settings_errors('webclinicpro_success');
                    }
                } else {
                    update_option('webclinicpro_status', 0);
                    update_option('webclinicpro_subscription', NULL);
                    add_settings_error('webclinicpro_options', '', 'Plugin Activation Failed!', 'error');
                    settings_errors('webclinicpro_options');
                }
            } else {
                update_option('webclinicpro_status', 0);
                update_option('webclinicpro_subscription', NULL);
                add_settings_error('webclinicpro_options', '', 'Plugin Activation Failed!', 'error');
                settings_errors('webclinicpro_options');	        
            }
            
        } else {
            if (!get_option('webclinicpro_status')) {
                
                add_settings_error('webclinicpro_options', '', 'API Key Required to Activate Plugin', 'notice');
                settings_errors('webclinicpro_options');	
                
            }
        }

        


        $active_tab = isset( $_GET[ 'tab' ] ) ? $_GET[ 'tab' ] : 'configuration';
        
        
        ?>
        <div class="wrap">
            <h2>webClinic Pro Options</h2>
            
            <h2 class="nav-tab-wrapper">
                <a href="?page=webclinicpro&tab=configuration" class="nav-tab <?php echo $active_tab == 'configuration' ? 'nav-tab-active' : ''; ?>">Configuration</a>
              <?php /*
                <a href="?page=webclinicpro&tab=checklist" class="nav-tab <?php echo $active_tab == 'checklist' ? 'nav-tab-active' : ''; ?>">Checklist</a>
              */ ?>
              <?php /*
                <a href="?page=webclinicpro&tab=summary" class="nav-tab <?php echo $active_tab == 'summary' ? 'nav-tab-active' : ''; ?>">Summary</a>
              */ ?>
            </h2>
            
            <?php 
            if ($active_tab == 'configuration') {
                
                $this->webclinicpro_configuration_view();
                
            } else if ($active_tab == 'checklist') { 
            
                $this->webclinicpro_checklist_view();
                
            } else if ($active_tab == 'summary') {
            
                $this->webclinicpro_summary_view();
                
            }
            ?>

            <div class="wrap">
            Call us for support at 1-800-771-3950 or open a ticket through the customer portal at <a href="https://portal.webclinicpro.com" target="_blank">https://portal.webclinicpro.com</a>.
            </div>
        </div>
        <?php
        
    }
    
    
    
    
    /**
     * Display configuration page
     */
    function webclinicpro_configuration_view()
    {
        
        ?>
        <form method="post" action="options.php">
                
            <?php settings_fields( 'webclinicpro-settings-group' ); ?>
            <?php do_settings_sections( 'webclinicpro-settings-group' );	 ?>
            <table class="form-table">
                <tr valign="top">
                <th scope="row">webClinic Pro Subscriber KEY</th>        
                <td><input type="text" name="webclinicpro_subscriber_key" value="<?php echo esc_attr( get_option('webclinicpro_subscriber_key') ); ?>" /></td>
                </tr>
                
                <?php if (get_option('webclinicpro_status') == 1) { ?>
                    <tr valign="top">
                        <th scope="row">Package</th>
                        <td><input type="text" name="webclinicpro_subscription" readonly="readonly" value="<?php echo esc_attr( get_option('webclinicpro_subscription') ); ?>" /></td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Force SSL</th>
                        <td><input type="checkbox" name="webclinicpro_force_ssl" value="1" <?php checked( 1 == esc_attr( get_option('webclinicpro_force_ssl') ) ); ?> /></td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Secure Content (Simple)</th>
                        <td><input type="checkbox" name="webclinicpro_mixed_content" value="1" <?php checked(1 == esc_attr( get_option('webclinicpro_mixed_content') ) ); ?> /></td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Use Relative URLs</th>
                        <td><input type="checkbox" name="webclinicpro_relative_url" value="1" <?php checked(1 == esc_attr( get_option('webclinicpro_relative_url') ) ); ?> /></td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Protection Seal</th>
                        <td><input type="checkbox" name="webclinicpro_block_seal" value="1" <?php checked( 1 == esc_attr( get_option('webclinicpro_block_seal') ) ); ?> /></td>
                    </tr>
                </tr>
                <?php } ?>
            </table>
            
            <?php submit_button(); ?>
            
        </form>
        <?php
                
    }
    
    
    
    
    /**
     * Display Checklist Page
     */
    function webclinicpro_checklist_view()
    {
        
        ?>
        <form method="post" action="options.php">
        
            <?php settings_fields( 'webclinicpro-checklist-group' ); ?>
            <?php do_settings_sections( 'webclinicpro-checklist-group' ); ?>
            
            <h2 style="margin:1em 0;">Security</h2>
            <hr />
            <table class="form-table">
                
                <tr valign="top">
                    <th scope="row">SSL Cert</th>
                    <td><input type="checkbox" name="webclinicpro_sslcert_check" value="1" <?php checked(1 == esc_attr( get_option('webclinicpro_sslcert_check') ) ); ?> /></td>
                </tr>
                <tr valign="top">
                    <th scope="row">Firewall</th>
                    <td><input type="checkbox" name="webclinicpro_firewall_check" value="1" <?php checked(1 == esc_attr( get_option('webclinicpro_firewall_check') ) ); ?> /></td>
                </tr>
                <tr valign="top">
                    <th scope="row">Backups</th>
                    <td><input type="checkbox" name="webclinicpro_backup_check" value="1" <?php checked(1 == esc_attr( get_option('webclinicpro_backup_check') ) ); ?> /></td>
                </tr>
                <tr valign="top">
                    <th scope="row">Login Secure <br>(WordFence)</th>
                    <td><input type="checkbox" name="webclinicpro_login_check" value="1" <?php checked(1 == esc_attr( get_option('webclinicpro_login_check') ) ); ?> /></td>
                </tr>
                <tr valign="top">
                    <th scope="row">Comments Secure <br>(Disable Comments)</th>
                    <td><input type="checkbox" name="webclinicpro_comments_check" value="1" <?php checked(1 == esc_attr( get_option('webclinicpro_comments_check') ) ); ?> /></td>
                </tr>
                <tr valign="top">
                    <th scope="row">Content Secure <br>(Insecure Content Fixer)</th>
                    <td><input type="checkbox" name="webclinicpro_mixedssl_check" value="1" <?php checked(1 == esc_attr( get_option('webclinicpro_mixedssl_check') ) ); ?> /></td>
                </tr>
                
            </table>
            
            <h2 style="margin:1em 0;">Performance</h2>
            <hr />
            <table class="form-table">
                
                <tr valign="top">
                    <th scope="row">Relative URLs</th>
                    <td><input type="checkbox" name="webclinicpro_relativeurl_check" value="1" <?php checked(1 == esc_attr( get_option('webclinicpro_relativeurl_check') ) ); ?> /></td>
                </tr>
                <tr valign="top">
                    <th scope="row">Caching <br>(W3 Total Cache)</th>
                    <td><input type="checkbox" name="webclinicpro_cache_check" value="1" <?php checked(1 == esc_attr( get_option('webclinicpro_cache_check') ) ); ?> /></td>
                </tr>
                
                
            </table>
            <?php submit_button(); ?>
            
        </form>
        <?php
        
    }
        
    
    
    
    /**
     * Display Summary Page
     */
    function webclinicpro_summary_view()
    {
        
        ?>
        <h2>Summary</h2>
        <?php
        
    }
   
    
    
    
    
    /**
     * Returns the base domain for use with API.
     */
    function webclinicpro_get_url()
    {
        
        $pu = parse_url("http://" . $_SERVER['SERVER_NAME']);
        $array = explode(".", $pu['host']);
        return (array_key_exists(count($array) - 2, $array) ? $array[count($array) - 2] : "") . "." . $array[count($array) - 1];
        
    }
    
    
}