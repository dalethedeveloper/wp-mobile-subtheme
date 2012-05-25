<?php
/*
Plugin Name: Mobile Subtheme for Wordpress
Description: Simple Mobile Template system.  Augments theme selection to look in the current theme's directory for a /mobile/{template-name}.php to use when a mobile device hits your site.
Author: Dale the Developer
Version: 0.1
Tags: mobile, theme, template, select
License: MIT
*/
new wp_mobile_override;

if( is_admin() )
	new wp_mort_admin;

class wp_mobile_override {
    // Courtesy of http://detectmobilebrowsers.com/ and http://code.google.com/p/mobileesp/source/browse/PHP/mdetect.php
    public $ua_regex_full = '/android.+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|opera m(ob|in)i|palm|hpwos|webos|phone|p(ixi|re)\/|plucker|pocket|psp|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i';
    public $ua_regex_short = '/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|e\-|e\/|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(di|rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|xda(\-|2|g)|yas\-|your|zeto|zte\-/i';

    public function __construct() {
		$opts = wp_mort_admin::load_options();
		if( $opts['enabled'] !== 'yes' or is_admin() or (defined('DOING_AJAX') and DOING_AJAX) )
			return;

		// Caching plugins will never reach this block, so this logic should be duplicated
		// in the wp-config.php, smart plugins like Quick Cache let you configure logic
		// to access the cached pages.  In our case we have it configured to use the custom
		// cookie wpmort_mobile to generate the MD5 salt for cached page names.
		/*
			define('WP_MORT_IS_MOBILE',
			preg_match('/iP(hone|od)|Android|BlackBerry|IEMobile|Kindle|NetFront|Silk-Accelerated|webOS|Fennec|Minimo|Opera M(obi|ini)|Blazer|Dolfin|Dolphin|Skyfire|Zune/',$_SERVER['HTTP_USER_AGENT'])
				? true : false);
			if( WP_MORT_IS_MOBILE and !isset($_COOKIE['wpmort_enabled']) ) {
				$_COOKIE['wpmort_enabled'] = 'yes';
				setcookie('wpmort_enabled','yes');
			}
		 */
		if( !defined('WP_MORT_IS_MOBILE') ) {
			$mobile_regex = (empty($opts['regex']) ? '/iP(hone|od)|Android|BlackBerry|IEMobile|Kindle|NetFront|Silk-Accelerated|webOS|Fennec|Minimo|Opera M(obi|ini)|Blazer|Dolfin|Dolphin|Skyfire|Zune/' : $opts['regex']);
			define('WP_MORT_IS_MOBILE',(preg_match($mobile_regex,$_SERVER['HTTP_USER_AGENT']) ? true : false));
		}

 		// Include child functions.php and our main /mobile/functions.php (CSS done via @import)
		if( is_child_theme() and file_exists(get_stylesheet_directory().'/mobile/functions.php') )
			require_once get_stylesheet_directory().'/mobile/functions.php';

		// Now our core functions.php
		if( file_exists(get_template_directory().'/mobile/functions.php') )
			require_once get_template_directory().'/mobile/functions.php';

        add_filter('rewrite_rules_array', array($this,'manifest_rewrite') );
        add_action('init', array($this,'handle_cookies') );

        $curr_theme = is_child_theme() ? get_stylesheet_directory() : get_template_directory();

        if( is_dir($curr_theme . '/mobile') and
                ( (WP_MORT_IS_MOBILE and empty($_COOKIE['wpmort_enabled']) ) or
                  (isset($_COOKIE['wpmort_enabled']) and $_COOKIE['wpmort_enabled'] == 'yes') )
          ) {
            define('WP_MORT_ACTIVE',true);
            define('WP_MORT_TEMPLATE_DIR',$curr_theme.'/mobile');
            define('WP_MORT_TEMPLATE_URI',  str_replace(get_option('siteurl'),'',get_stylesheet_directory_uri()).'/mobile');
            add_filter('template_include',array($this,'check_mobile_template'));
            add_filter('query_vars', array($this,'manifest_queryvars') );
            add_filter('wp_headers', array($this,'manifest_output') );
        } else
            add_action('wp_footer',array($this,'show_mobile_toggle'),50);
    }

    public function check_mobile_template($template) {
//        var_dump($template);die;
        $mobile_tpl = WP_MORT_TEMPLATE_DIR . '/' . basename($template);

        // Override index.php as the front page if it exists
        if(is_front_page() and !file_exists($mobile_tpl) and file_exists(WP_MORT_TEMPLATE_DIR.'/index.php') )
            $template = WP_MORT_TEMPLATE_DIR.'/index.php';

        return file_exists($mobile_tpl) ? $mobile_tpl : $template;
    }

    public function handle_cookies() {
        if( !is_admin() and isset($_GET['mobile_override']) and !empty($_GET['mobile_override']) ) {
            if($_GET['mobile_override'] == 'go' )
                setcookie('wpmort_enabled','yes');
            if($_GET['mobile_override'] == 'no' )
                setcookie('wpmort_enabled','no');

            wp_redirect(empty($_SERVER['HTTP_REFERER']) ? get_bloginfo('home') : $_SERVER['HTTP_REFERER']);
            exit();
        }
    }

    /**
     * Only called from do_action('wp_footer') found in full templates (ie. not mobile)
     *
     * Action: wp_footer
     */
    public function show_mobile_toggle() {
        if(WP_MORT_IS_MOBILE)
            echo '<div style="width:100%;height:40px;position:absolute;bottom:0;"><a href="',self::toggle_mobile_href(),'">View Mobile Site</a></div>';
    }

    public static function toggle_mobile_href() {
        return $_SERVER['REQUEST_URI'].(strpos($_SERVER['REQUEST_URI'],'?')?'':'?').'mobile_override='.(defined('WP_MORT_ACTIVE')?'g':'n').'o';
    }

    /**
     * Add a query var as a flag for /default.appcache
     *
     * Filter: query_vars
     */
    public function manifest_queryvars($qvars) {
        $qvars[] = 'show_mobile_manifest';
        return $qvars;
    }
    /**
     * Add a rewrite rules for /default.appcache
     *
     * Filter: rewrite_rules_array
     */
    public function manifest_rewrite($wprw = array()) {
        return array('default.appcache.?' => 'index.php?show_mobile_manifest=1') + $wprw;
    }

    /**
     * Output our manifest file, with proper mime type
     *
     * Test: curl --cookie "wp-mobile_override=yes"  http://localhost/sitetwo/default.appcache/
     */
    public function manifest_output($headers) {
        global $wp;
        if( !empty($wp->query_vars) and isset($wp->query_vars['show_mobile_manifest']) and $wp->query_vars['show_mobile_manifest'] ) {
            $cache_file = WP_MORT_TEMPLATE_DIR.'/default.appcache';
            $cache_entries = file_exists($cache_file) ? file_get_contents($cache_file) : '';

            // Make files in subdir relative to WP Template directory
            $cache = explode("\n",$cache_entries);
            $in_cache = false;
            foreach($cache as &$line) {
                if( strpos($line,'CACHE') === 0 )
                    $in_cache = true;
                elseif( strpos($line,'NETWORK') === 0 or strpos($line,'FALLBACK') === 0 )
                    $in_cache = false;
                elseif( $in_cache and !empty($line) ) {
                    echo WP_PLUGIN_DIR;
                    $rel_file = ($line[0] == '/' ?'':'/') . $line;
                    if( file_exists(WP_MORT_TEMPLATE_DIR.$rel_file))
                        $line = WP_MORT_TEMPLATE_URI.$rel_file;
                    elseif( $line[0] !== '#' and !file_exists(ABSPATH . $rel_file) )
                        $line = "#{$line} - Removed by mobile_override, does not exist.";
                }
            }
            @header('Content-Type: text/cache-manifest');
            exit( implode("\n",$cache) );
        }
    }
}



/**
* Admin options for WP Mobile Override for Themes (MORT)
*   Based on generic WP admin: https://gist.github.com/1474525
*/
class wp_mort_admin {
	protected $nonce_string = 'wp-mort-noncense';
	public static $plugin_prefix = 'wp-mort-';

	/**
	 * Plugin option defaults
	 */
	public static $option_defaults = array(
					'enabled' => 'no'
				);
	/**
	 * Loaded on init with defaults + set in wp_options
	 */
	protected $options = array();

	public function __construct() {
		add_action('admin_menu', array($this, 'admin_menus'));
		add_action('wp_ajax_'.$this->plugin_prefix, array($this,'ajax_handler'));
		add_action('admin_head',array($this,'ajax_script'));
		$this->options = $this->load_options();
	}

	public static function load_options() {
		return array_merge(self::$option_defaults,get_option(self::$plugin_prefix.'options',array()));
	}

	/**
	 * Register Admin page and filtering
	 */
	public static function plugin_opt( $opt = null ) {
		$opts = self::load_options();
		if( empty($opt) )
			return $opts;
		elseif( isset(self::$option_defaults[$opt]) )
			return $opts[$opt];
		else
			return null;
	}

	/**
	 * Register Admin page and filtering
	 */
	public function admin_menus() {
		add_theme_page(
			'Mobile Theme',
			'Mobile Theme',
			'edit_theme_options',
			'wpmort',
			array($this,'show_theme_subpage')
		);
	}

	/**
	 * Handle AJAX calls to and fro, assuming jQuery is doing the hard work.
	 */
	public function ajax_handler() {
		check_ajax_referer($this->nonce_string,'security');

		$response = array('message' => $_POST['generic_opt_text']);

		header('Content-Type: application/json');
		exit( json_encode($response) );
	}

	/**
	 * Output javascript with noncing for AJAX requests
	 */
	public function ajax_script() {
		$nonce = wp_create_nonce($this->nonce_string);
		$formname = self::$plugin_prefix . 'form';

		echo <<<JS

	<script type="text/javascript">
		jQuery(document).ready(function($) {
			$('form[name="{$formname}"]').delegate('input.button-primary.ajax','click',function(){
				var input = $(this).closest('form').serialize(),
				parent = $(this).parent(),
				loading = $('.ajax-feedback',parent);

				if( loading.css('visibility') !== 'visible' ) {
					$('div.updated',parent).remove();
					loading.css('visibility','visible');
					$.ajax({
						type: 'POST',
						url: ajaxurl,
						data: input + '&action={$this->ajax_slug}&security={$nonce}',
						dataType: 'json',
						timeout : 30000,
						success: function(data, textStatus, jqXHR) {
							if( data ) {
								if( data.message )
									$('<div class="updated"><p><strong>' + data.message + '</strong></p></div>').appendTo( parent ).delay(1000).fadeOut();

								if( data.error )
									$('<div class="updated"><p><strong>Error: ' + data.error + '</strong></p></div>').appendTo( parent ).delay(1000).fadeOut();

								if( data.reload )
									window.location.reload();
							}
						},
						complete: function(jqXHR, textStatus) {
								loading.css('visibility','hidden');
								if( textStatus !== 'success' )
									$('<div class="updated"><p><strong>Unable to completed that action, please try again.</strong></p></div>').appendTo( parent );
							}
					});
				}
				return false;
			});
		});
	</script>

JS;
	}

	/**
	 * Save $_POST options on posting
	 * @return bool TRUE on saved, FALSE on not saved
	 */
	public function save_settings($not_ajax = true) {
		$noncename = self::$plugin_prefix . 'form-nonce';
		if( $_POST and !empty($_POST[$noncename]) and wp_verify_nonce( $_POST[$noncename],$this->nonce_string ) ) {
			$opts = $this->load_options();
			foreach($opts as $k => &$v) {
				if( isset($_POST[$k]) )
					$v = $_POST[$k];
			}

			update_option(self::$plugin_prefix.'options',$opts);

			// Build rewrites when enabled (plugin uses filter 'rewrite_rules_array')
			if( $opts['enabled'] == 'yes' ) {
				global $wp_rewrite;
				$wp_rewrite->flush_rules();
			}

			if( $not_ajax )
				echo '<div class="updated"><p><strong>Settings Saved.</strong></p></div>';

			return true;
		}
		return false;
	}

	/**
	 * Output an admin form wrapping our interior options panes
	 */
	public function output_form( $content ) {
		$nonceid = wp_create_nonce($this->nonce_string);
		$noncename = self::$plugin_prefix . 'form-nonce';
		$formname = self::$plugin_prefix . 'form';
		echo <<<HTML
		<div class="wrap">
			<form name="{$formname}" method="post" action="">
				{$content}
				<input type="hidden" name="{$noncename}" id="{$noncename}" value="{$nonceid}" />
			</form>
		</div>
HTML;
	}

	/**
	 * Quick check for permissions
	 * @param type $cap Capability
	 */
	public function check_perm($cap) {
		if( !current_user_can($cap) ) {
			$msg = <<<MSG
			<div class="wrap">
				<h2>Permission Denied</h2>
				<p>
				You do not have permission to change these settings.  Please contact your site administrator.
				</p>
			</div>
MSG;
			wp_die($msg);
		}
	}

	/**
	 * Settings page for Generic Admin
	 */
	public function show_theme_subpage() {
		$loading_img = esc_url( admin_url( 'images/wpspin_light.gif' ) );
		$this->check_perm('edit_theme_options');
		$this->save_settings();
		$opts = $this->load_options();

		$mort_enabled = $opts['enabled'];
        $mort_yes = ($mort_enabled == 'yes') ? 'checked="checked" ' : '';
        $mort_no = ($mort_enabled == 'no') ? 'checked="checked" ' : '';

		$body = <<<HTML
		<h2>Mobile Override for Themes</h2>
		<p>
			Someone should write some documentations
		</p>
		<table class="form-table">
			<tr valign="top">
				<th scope="row">Enable Mobile Override for Current Theme</th>
					<td>
						<input type="radio" name="enabled" value="yes" {$mort_yes} /> Yes <br/>
						<input type="radio" name="enabled" value="no" {$mort_no} /> No
					</td>
				</tr>
		</table>
		<p class="submit">
			<input type="submit" name="Submit" class="button-primary" value="Save Changes" />
<!--
			<img src="{$loading_img}" class="ajax-feedback" title="" alt="" />
			<input type="submit" name="Submit" class="button-primary" value="Save Changes" />
-->
		</p>
HTML;
		$this->output_form($body);
	}
}
