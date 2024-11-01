<?php
/**
 * @package webClinicPro
 */
 
 
 
class WebClinicPro_URLControl
{
    
    
    /**
	 * fix images, embeds, iframes in content
	 */
	function urlcontrol_relative_content($content)
    {

        static $comb = array(
            '#<(?:img|iframe) .*?src=[\'"]\Khttps?://[^\'"]+#i',
			'#<link [^>]+href=[\'"]\Khttps?://[^\'"]+#i',
			'#<script [^>]*?src=[\'"]\Khttps?://[^\'"]+#i',
			'#url\([\'"]?\Khttps?://[^)]+#i',
		);
		$content = preg_replace_callback($comb, array($this, 'urlcontrol_relative_url'), $content);
        
		return $content;

	}
    
    
    
    
    /**
     * Make URL relative
     */
    function urlcontrol_relative_url($matches)
    {
        
        return $this->urlcontrol_make_it_relative($matches[0]);

    }
    
    
    
    
    /**
     * Make SRC relative
     */
    function urlcontrol_make_srcset_relative($srcset)
    {
        
        foreach ( $srcset as $key => $value ) {
            if ( isset( $value['url'] ) ) {
                $srcset[$key]['url'] = $this->urlcontrol_make_it_relative($value['url']);
            }
        }
        
        return $srcset;
        
    }
    
    
    
    
    /**
     * Make URL relative
     */
    function urlcontrol_make_it_relative($url)
    {
        
        $home_url = esc_url(home_url('/'));     
        $home_url = str_replace( 'https://', '', $home_url);
        $home_url = str_replace( 'http://', '', $home_url);
        $home_url = str_replace( '//', '', $home_url);
        
        $url = str_replace( 'https://'.$home_url, '/', $url );
        $url = str_replace( 'http://'.$home_url, '/', $url );
        $url = str_replace( '//'.$home_url, '/', $url );
        
        return $url;
        
    }
    
    
    
    
    /**
	 * Make URL absolute
	 */
	function urlcontrol_make_it_absolute($url)
    {
        
		if (isset($url[0]) && ($url[0] == '/') && ((strpos($url, esc_url(home_url())) === false) || (strpos($url, $this->site_url) === false))) {
            
            return esc_url(home_url()) . $url;
            
		}
		return $url;
        
	}
    

    
    
}