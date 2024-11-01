<?php
/*
Plugin Name: WP Really Simple Health 
Plugin URI: http://www.tautologicalcode.net
Description: Show uptime, cpu server load and memory usage of your linux server on toolbar
Version: 1.0
Author: Tiberius14
Author URI: http://www.tautologicalcode.net

WP Really Simple Health - Show uptime, cpu server load and memory usage of your linux server on toolbar
Copyright (c) 2012 Tiberius14

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
*/


// Checks Wordpress version
function requires_wordpress_version() {
	global $wp_version;
	$plugin = plugin_basename( __FILE__ );
	$plugin_data = get_plugin_data( __FILE__, false );
 
	if ( version_compare($wp_version, "3.3", "<" ) ) {
		if( is_plugin_active($plugin) ) {
			deactivate_plugins( $plugin );
			wp_die( "'".$plugin_data['Name']."' requires WordPress 3.3 or higher! Deactivating Plugin.<br /><br />Back to <a href='".admin_url()."'>WordPress admin</a>." );
		}
	}
}
add_action( 'admin_init', 'requires_wordpress_version' );

// Class starts here
if (!class_exists("Class_CpUptiMem")) {
	class Class_CpUptiMem {
		var $adminOptionsName = "CpUptiMem_Options";

		// Class create
		function Class_CpUptiMem() { 
	
		}

		// Class initialize
		function init() {
			$this->getAdminOptions();
		}

		// Admin Options Array Initialization
		function getAdminOptions() {
			$MyAdmOpt = array(
				'Show_Cpu_Tired' => '1',
				'Show_M_Use' => '1',
				'Show_UpUpUp' => '1');
			$MyOptions = get_option($this->adminOptionsName);
			if (!empty($MyOptions)) {
				foreach ($MyOptions as $key => $option)
					$MyAdmOpt[$key] = $option;
			}				
			update_option($this->adminOptionsName, $MyAdmOpt);
			return $MyAdmOpt;
		}

		// Admin Options Array Update		
		function CpUptiMem_Opts() {
		$MyOptions = $this->getAdminOptions();
		if (isset($_POST['Opts_submit'])) { 
			$MyOptions['Show_UpUpUp'] = @$_POST['MyOpts_Show_Uptime'];
			$MyOptions['Show_M_Use'] = @$_POST['MyOpts_Show_Mem_Use'];
			$MyOptions['Show_Cpu_Tired'] = @$_POST['MyOpts_Show_Cpu'];
			update_option($this->adminOptionsName, $MyOptions);
		?>
			<div id="message" class="updated fade"><p>Option Saved!</p></div>
		<?php
		} ?>
		    	
		<!-- Options page html start -->
		<div class="wrap">
			<div id="icon-options-general" class="icon32"></div>
			<h2>WP Really Simple Health Settings</h2> 
			<div class="metabox-holder has-right-sidebar">
				<div class="inner-sidebar">
					<div class="postbox">
						<h3><span>About this plugin</span></h3>
						<div class="inside">
							<ul>
								<li><a href="http://www.tautologicalcode.net/">Plugin Home Page</a></li>
								<li><a href="http://wordpress.org/extend/plugins/wp-really-simple-health-10/">Plugin Home Page at Wordpress.org</a></li>
								<li><a href="http://www.tautologicalcode.net/contacts/">Report a bug</a></li>
								<li><a href="http://www.tautologicalcode.net/contacts/">Suggest a feature</a></li>
							</ul>
						</div>
					</div>
					<div class="postbox">
						<h3><span>Like this plugin ?</span></h3>
						<div class="inside">
								<ul>
									<li><a href="http://wordpress.org/extend/plugins/wp-really-simple-health-10/">Vote it at Wordpress.org</a></li>
									<li><a href="http://wordpress.org/extend/plugins/wp-really-simple-health-10/">Rate it at Wordpress.org</a></li>
								</ul><br>
							<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
								<input type="hidden" name="cmd" value="_donations">
								<input type="hidden" name="business" value="ppdonation@tautologicalcode.net">
								<input type="hidden" name="currency_code" value="USD">
								<input type="hidden" name="bn" value="PP-DonationsBF:btn_donateCC_LG_global.gif:NonHosted">
								<input type="image" src="https://www.paypal.com/en_US/i/btn/btn_donateCC_LG_global.gif" border="0" name="submit" alt="">
								<img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1">
							</form>	
						</div>
					</div>
				</div> <!-- .inner-sidebar -->
				<div id="post-body">
					<div id="post-body-content">
						<div class="postbox">
						<h3><span>Options</span></h3>
							<div class="inside">
								<form method="post" action="<?php echo $_SERVER["REQUEST_URI"]; ?>">
								<br/><br/>
								<input type="checkbox" name="MyOpts_Show_Uptime" value="1" <?php checked( $MyOptions['Show_UpUpUp'], 1 ); ?> />
								<label for="Show_Uptime"><strong>Display the server uptime</strong></label><br/><br/>				
								<input type="checkbox" name="MyOpts_Show_Mem_Use" value="1" <?php checked($MyOptions['Show_M_Use'],1); ?> />
								<label for="Show_Mem"><strong>Display the actual Ram memory use</strong></label><br/><br/>
						
		       						<input type="checkbox" name="MyOpts_Show_Cpu" value="1" <?php checked($MyOptions['Show_Cpu_Tired'],1); ?> />
								<label for="Show_Cpu_Load"><strong>Display Cpu loading in the last minute</strong></label><br/><br/><br/>
       						
								<input class="button-primary" type="submit" name="Opts_submit" id="Opts_submit" value="<?php _e('Update Settings', 'Class_CpUptiMem') ?>" /><br/><br/>
								</form>
							</div> <!-- .inside -->
						</div>
					</div> <!-- #post-body-content -->
				</div> <!-- #post-body -->
			</div> <!-- .metabox-holder -->
		</div> <!-- .wrap -->
		<!-- Options page html end -->
	  <?php }


		// Uptime calc 
		public function CpUptime() {
			$serverresult = @exec('uptime');
			preg_match("/averages?: ([0-9\.]+),[\s]+([0-9\.]+),[\s]+([0-9\.]+)/",$serverresult,$average);
			$uptime = explode(' up ', $serverresult);
			$uptime = explode(',', $uptime[1]);
			$uptime = $uptime[0].', '.$uptime[1];
			$buh = strtok( exec( "cat /proc/uptime" ), "." );
			$days = sprintf( "%2d", ($buh/(3600*24)) );
			$hours = sprintf( "%2d", ( ($buh % (3600*24)) / 3600) );
			$min = sprintf( "%2d", ($buh % (3600*24) % 3600)/60 );
			$sec = sprintf( "%2d", ($buh % (3600*24) % 3600)%60 );
			$upresults = "Up from $days d, $hours hrs, $min mins, $sec secs";
			return $upresults;
		}

		// Cpu load calc 
		public function CpuLoad() {
			$serverresult = @exec('uptime');
			preg_match("/averages?: ([0-9\.]+),[\s]+([0-9\.]+),[\s]+([0-9\.]+)/",$serverresult,$average);
			$srv_load = "Cpu $average[1] %";
			return $srv_load;
		}

		// Ram using calc
		public function MemCalcs() {
			$memlimit = ini_get('memory_limit') ;
			$memused = round(memory_get_usage() / 1024 / 1024, 2);
			$mem_use_percent = round($memused* 100 / $memlimit)."%";
			$mem_using = "Ram $memused of $memlimit ($mem_use_percent)";
			return $mem_using;
		}


		// Shows results on Admin Bar
		function Print_Admin_Bar($wp_admin_bar) {
			global $wp_admin_bar,$upresults,$srv_load,$mem_using; 
			if ( !is_super_admin() || !is_admin_bar_showing() )
				return;

			$MyOptions = $this->getAdminOptions();	
			if ($MyOptions['Show_UpUpUp']) :
				$wp_admin_bar->add_menu( array(
					'id'    => 'my-item1',
					'title' => $this->CpUptime(),
				));
			endif;

			if ($MyOptions['Show_M_Use']) :
				$wp_admin_bar->add_menu( array(
					'id'    => 'my-item2',
					'title' => $this->MemCalcs(),
				));
			endif;

			if ($MyOptions['Show_Cpu_Tired']) :
				$wp_admin_bar->add_menu( array(
					'id'    => 'my-item3',
					'title' => $this->CpuLoad(),
				));
			endif;
	
			}
		}

		// If uninstall delete options from database 
    		if ( function_exists('register_uninstall_hook') )
 		   register_uninstall_hook(__FILE__, 'remove_opts_from_db');

	        function remove_opts_from_db() {
		    delete_option('CpUptiMem_Options');
	        }
} // End Class


// Class instance
if (class_exists("Class_CpUptiMem")) {
	$Class_CpUptiMem_instance = new Class_CpUptiMem();
}

// Options Page
function add_CpUptiMem_page() {  
	global $Class_CpUptiMem_instance;
	if (!isset($Class_CpUptiMem_instance)) {
		return;
	}
	global $my_admin_page;
	$my_admin_page = add_options_page('WP Really Simple Health', 'WP Really Simple Health','manage_options', basename(__FILE__), array(&$Class_CpUptiMem_instance, 'CpUptiMem_Opts'));
	add_action('load-'.$my_admin_page, 'my_admin_add_help_tab');
}

function my_admin_add_help_tab () {
    global $my_admin_page;
    $screen = get_current_screen();
    if ( $screen->id != $my_admin_page )
        return;

    // Add my_help_tab if current screen is My Admin Page
    $screen->add_help_tab( array(
        'id'	=> 'my_help_tab',
        'title'	=> __('WP Really Simple Health Help'),
        'content'	=> '<p>' . __( 'Check out the documentation and support forums for help with this plugin.' ) . '</p>'. '<a href="http://example.org/docs\">Documentation</a><br /><a href="http://example.org/support\">Support forums</a>',
    ) );
}

// Actions
if (isset($Class_CpUptiMem_instance)) {
	add_action('admin_menu', 'add_CpUptiMem_page');
	add_action('activate_wp-really-simple-health/wp-really-simple-health1-0.php',  array(&$Class_CpUptiMem_instance, 'init')); 
	add_action('wp_before_admin_bar_render', array(&$Class_CpUptiMem_instance, 'Print_Admin_Bar'));
}

?>
