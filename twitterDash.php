<?php
/*
Plugin name: twitterDash
plugin URI: http://www.scoopmedia.be/
Version: 2.1
Author: Gert Poppe
Description: Display your <a href="http://www.twitter.com/">Twitter</a>-page on the Dashboard!
*/

//controleer of de klasse nog niet bestaat, om fouten te voorkomen
if(!class_exists("TwitterDash")) {
	//zoniet, maak ze aan
	class TwitterDash {
		// class variables
		var $adminOptionsName = "twitterDashOptions";
		
		//constructor
		function TwitterDash() {
		
		}
		
		//methods
		function init() {
			$this->getAdminOptions();
		}
		
		function getAdminOptions() {
			$twitterDashAdminOptions = array('username' => '', 'password' => '', 'count' => '5', 'enable_posting' => 'TRUE');
			$twitterDashOptions = get_option($this->adminOptionsName);
			
			if(!empty($twitterDashOptions)) {
				foreach ($twitterDashOptions as $key => $option) {
					$twitterDashAdminOptions[$key] = $option;
				}
			}
			update_option($this->adminOptionsName, $twitterDashAdminOptions);
			return $twitterDashAdminOptions;
		}
		
		function printAdminPage() {
			//opties inladen
			$twitterDashOptions = $this->getAdminOptions();
						
			if(isset($_POST['update_twitterDash'])) {
				if(isset($_POST['username_twitterDash'])) {
					$twitterDashOptions['username'] = $_POST['username_twitterDash'];
				}
				if(isset($_POST['password_twitterDash'])) {
					$twitterDashOptions['password'] = $_POST['password_twitterDash'];
				}
				if(isset($_POST['count_twitterDash'])) {
					$twitterDashOptions['count'] = $_POST['count_twitterDash'];
				}
				if(isset($_POST['enable_posting_twitterDash'])) {
					$twitterDashOptions['enable_posting'] = $_POST['enable_posting_twitterDash'];
				} else {
					$twitterDashOptions['enable_posting'] = 'FALSE';
				}
			
				update_option($this->adminOptionsName, $twitterDashOptions);
			
				echo "<div id='message' class='updated fade'><p><strong>Settings Updated</strong></p></div>";
			}
			
			?>
            	<div class=wrap>
<form method="post" action="<?php echo $_SERVER["REQUEST_URI"]; ?>">
<h2>TwitterDash settings</h2>
<h3>Your twitter username!</h3>
<input type="text" name="username_twitterDash" value="<?php echo($twitterDashOptions['username']); ?>" />
<h3>Your twitter password!</h3>
<input type="password" name="password_twitterDash" value="<?php echo($twitterDashOptions['password']); ?>" />
<h3>How many updates would you like to display?</h3>
<input type="text" name="count_twitterDash" value="<?php echo($twitterDashOptions['count']); ?>" />
<h3>Would you like to enable updating?</h3>
<input type="checkbox" name="enable_posting_twitterDash" <?php $this->checkCheckbox($twitterDashOptions['enable_posting']); ?> value="TRUE" /> I would like to enable updating from the dashboard. 
<div class="submit"><input type="submit" name="update_twitterDash" value="<?php _e('Update Settings', 'twitterDash') ?>" /></div>
</form>
</div>
            <?php
		
		}
		
		function checkCheckbox($test) {
			if($test == 'TRUE') {
				echo "checked='checked'";
			}		
		}
		
		function showContent() {
		
			$options = $this->getAdminOptions();
		
			echo '</div><!-- rightnow --><br class="clear" />&nbsp;<br class="clear" /><div id="dashboard-widgets-wrap"><div id="dashboard-widgets"><div id="dashboard_twitterDash">
<div class="dashboard-widget"><h3 class="dashboard-widget-title"><span>twitterDash following <a href="http://www.twitter.com/'. $options['username'] .'" target="_blank">'. $options['username'] .'</a></span><small><a href="http://www.twitter.com/home" target="_blank">Twitter home</a></small><br class="clear" /></h3> <div class="dashboard-widget-content">';
			if($options['enable_posting'] == "TRUE") {
				$this->insertUpdateBox($options['username'],$options['password']);		
			}
			$this->getStatuses();
			
			echo '</div></div></div></div>';
		}
		
		function loadCSS() {
			include_once("css/style.php");
		}
		
		function tinyUrl($url) {
			$html = file_get_contents("http://tinyurl.com/create.php?url=".$url);
			preg_match('/http:\/\/preview\.tinyurl\.com\/(.*)<\/b>/', $html, $matches);
            return "http://tinyurl.com/".$matches[1];
		}
		
		function makeLinks($tekst) {
		try {
				$aTest = explode("http://",$tekst);
				foreach($aTest as $key => $deel) {
					if($key == "0") {
						$target = $deel;
					} else {
						$url = explode(" ",$deel);
						$target .= '<a href="http://'.$url[0].'" target="_blank">';
						$target .= 'http://'.$url[0];
						$target .= '</a>';
						$target .= " ";
						foreach($url as $key2 => $deel2) {
							if($key2 != 0) {
								$target .= $deel2;
								$target .= " ";
							}
						}
					}
				}
				return $target;
		} catch(Exception $e) {
				return $tekst;
		}
		}
		
		function postUpdate($message,$username,$password) {
			$msg = 'Your message here';

				$out="POST http://twitter.com/statuses/update.json HTTP/1.1\r\n"
  					."Host: twitter.com\r\n"
  					."Authorization: Basic ".base64_encode ($username.':'.$password)."\r\n"
  					."Content-type: application/x-www-form-urlencoded\r\n"
  					."Content-length: ".strlen ("status=$message&source=twitterdash")."\r\n"
					."Connection: Close\r\n\r\n"
					."status=$message&source=twitterdash";

				$fp = fsockopen ('twitter.com', 80);
				fwrite ($fp, $out);
				fclose ($fp); 
		}
		
		function insertUpdateBox($username,$password) {
		
			if(isset($_POST['update_twitterDash'])) {
				if($_POST['update_twitterDash'] != "") {
					$this->postUpdate($_POST['update_twitterDash'],$username,$password);
				}
			}
		
			// user info verkrijgen
			$lnk = "http://twitter.com/users/show/".$username.".xml";
			$xml = simplexml_load_file('http://'.$username.':'.$password.'@twitter.com/users/show/'.$username.'.xml');
			// weergeven van img
			 
			echo "<div class='twitterDash_update'>";
			echo "<a href='http://www.twitter.com/".$xml->screen_name."' target='_blank'><img src='". $xml->profile_image_url ."' class='twitterDash_profile_img'/></a>";
			echo "<b>" . $xml->screen_name . "</b><br />";
			?>
            <form method="post" action="<?php echo $_SERVER["REQUEST_URI"]; ?>">
            <input type="text" name="update_twitterDash" style="width:350px;"  /><input type="submit" value="update!" />
            </form>
            <?php
			echo "</div>";
		}
		
		function makeSyntax($tekst) {
			try {
				$target = "";
				$aTest = explode(" ",$tekst);
				foreach($aTest as $key => $deel) {
					if(substr($deel,0,1) == "@") {
						$target .= '<a href="http://www.twitter.com/'.substr($deel,1).'" target="_blank">';
						$target .= $deel;
						$target .= '</a>';
						$target .= " ";
					} else  {
						$target .= $deel;
						$target .= " ";
					}
				}
				return $target;
		} catch(Exception $e) {
				return $tekst;
		}
		}
		
		function getStatuses() {
			$options = $this->getAdminOptions();
			//testen van die twitter class
			try {
				$sxe = simplexml_load_file('http://'.$options['username'].':'.$options['password'].'@twitter.com/statuses/friends_timeline.xml?count='.$options['count']);
			
				foreach($sxe->status as $status) {
					echo "<div class='twitterDash_update'>";
					echo "<a href='http://www.twitter.com/".$status->user->screen_name."' target='_blank'><img src='". $status->user->profile_image_url ."' class='twitterDash_profile_img'/></a>";
					echo "<b>" . $status->user->screen_name . "</b><br />";
					
					echo $this->makeSyntax($this->makeLinks($status->text));
					echo "</div>";
				}
			} catch(Exception $e) {
				
			}
			
		}	
	}
}  

//initialize the class
if(class_exists("TwitterDash")) {
	$twitterDash = new TwitterDash();
}

//initialize the admin panel
if (!function_exists("twitterDash_ap")) {
	function twitterDash_ap() {
		global $twitterDash;
        if (!isset($twitterDash)) {
            return;
        }
        if (function_exists('add_options_page')) {
    		add_options_page('twitterDash', 'twitterDash', 9, basename(__FILE__), array(&$twitterDash, 'printAdminPage'));
        }
    }   
}


//set up actions and filters
if(isset($twitterDash)) {
	// Actions
	add_action('admin_menu', 'twitterDash_ap');
	add_action('activate_twitterDash/twitterDash.php', array(&$twitterDash, 'init'));
	add_action('admin_head', array(&$twitterDash, 'loadCSS'),1);
	add_action('activity_box_end', array(&$twitterDash, 'showContent'),1);
	// Filters

}
?>