<?php
/**
 * @package webClinicPro
 */


class WebClinicPro_SSLControl
{
 
    
    /**
     * Force HTTPS
     */
    function sslcontrol_force_ssl()
    {
        
        // Force use of correct Remote Address.
        // If the WAF passes more then one IP address then grab the first.
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $temp = $_SERVER['HTTP_X_FORWARDED_FOR'];
            if (strpos($temp, ',') !== false) {
                $temp = substr($temp, 0, strpos($temp, ','));
                $_SERVER['REMOTE_ADDR'] = $temp;
                $_ENV['REMOTE_ADDR'] = $temp;
            }
            else {
                $_SERVER['REMOTE_ADDR'] = $_SERVER['HTTP_X_FORWARDED_FOR'];
                $_ENV['REMOTE_ADDR'] = $_SERVER['HTTP_X_FORWARDED_FOR'];
            }
        }
        if (isset($_SERVER['HTTP_X_FORWARDED_HOST'])) {
            $temp = $_SERVER['HTTP_X_FORWARDED_FOR'];
            if (strpos($temp, ',') !== false) {
                $_SERVER['HTTP_X_FORWARDED_HOST'] = $temp;
                $_ENV['HTTP_X_FORWARDED_HOST'] = $temp;
            }
        }
        if (isset($_SERVER['HTTP_X_FORWARDED_SERVER'])) {
            $temp = $_SERVER['HTTP_X_FORWARDED_FOR'];
            if (strpos($temp, ',') !== false) {
                $_SERVER['HTTP_X_FORWARDED_SERVER'] = $temp;
                $_ENV['HTTP_X_FORWARDED_SERVER'] = $temp;
            }
        }
        

        if (!is_ssl()) {
            // The REQUEST URI var contains the current URL
            if (0 === strpos($_SERVER['REQUEST_URI'], 'http')) {

                // change URL scheme
                wp_redirect(set_url_scheme($_SERVER['REQUEST_URI'], 'https' ), 301);

            // HOST/URI
            } else {

                //  target URL
                wp_redirect('https://'. $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'], 301);
                
            }

            exit();
        }
        
    }
    
    
    
    
    /**
	 * fix images, embeds, iframes in content
	 */
	function sslcontrol_fix_content($content)
    {
		
        static $comb = array(
			'#<(?:img|iframe) .*?src=[\'"]\Khttp://[^\'"]+#i',
			'#<link [^>]+href=[\'"]\Khttp://[^\'"]+#i',
            '/<link [^>]*?href=[\'"]\K(http:\/\/)(?=[^\'"]+)/i',
			'#<script [^>]*?src=[\'"]\Khttp://[^\'"]+#i',
			'#url\([\'"]?\Khttp://[^)]+#i',
            '/url\([\'"]?\K(http:\/\/)(?=[^)]+)/i',
            '/<meta property="og:image" [^>]*?content=[\'"]\K(http:\/\/)(?=[^\'"]+)/i',
            '/<form [^>]*?action=[\'"]\K(http:\/\/)(?=[^\'"]+)/i',
		);
		$content = preg_replace_callback($comb, array($this, 'sslcontrol_src_callback'), $content);

        // Embed
		static $embed_comb = array(
			'#<object .*?</object>#is',
			'#<embed .*?(?:/>|</embed>)#is',
			'#<img [^>]+srcset=["\']\K[^"\']+#is',
		);
		$content = preg_replace_callback($embed_comb, array($this, 'sslcontrol_embed_callback'), $content);

		return $content;
        
	}
    
    
    
    
    /**
	 * callback for sslcontrol_fix_content() regex replace for URLs
	 */
	public function sslcontrol_src_callback($matches)
    {
        
		return 'https' . substr($matches[0], 4);
        
	}
    
    
    
    
	/**
	 * callback for sslcontrol_fix_content() regex replace for embeds
	 */
	public function sslcontrol_embed_callback($matches)
    {
        
		// match from start of http: URL until either end quotes, space, or query parameter separator, thus allowing for URLs in parameters
		$content = preg_replace_callback('#http://[^\'"&\? ]+#i', array($this, 'sslcontrol_src_callback'), $matches[0]);

		return $content;
        
	}
    
    
    
    
    
    
    
}