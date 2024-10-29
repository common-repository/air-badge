<?php
/*
Plugin Name: AIR Badge
Plugin URI: http://www.peterelst.com/blog/2008/04/19/air-badge-wordpress-plugin
Description: Allows you to define a custom AIR Install Badge on your Wordpress. For use this plugin: [airbadge] application name, full URL to yourapplication.air, application version, badge image.jpg[/airbadge].
Version: 0.7
Author: Peter Elst 
Author URI: http://www.peterelst.com
*/

/*  
Copyright 2008 Peter Elst (email: info@peterelst.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

define("wp_airbadge_swfobject_default", "true", true);
define("wp_airbadge_flashplayer_default", "<strong>Please upgrade your Flash Player</strong> This is the content that would be shown if the user does not have Flash Player 9.0.115 or higher installed.", true);
define("wp_airbadge_titlecolor_default", "#00AAFF", true);
define("wp_airbadge_buttonlabelcolor_default", "#00AAFF", true);
define("wp_airbadge_appnamecolor_default", "#00AAFF", true);
define("wp_airbadge_skiptransition_default", "false", true);

add_option(wp_airbadge_swfobject, wp_airbadge_swfobject_default, 'Embed SWFObject');
add_option(wp_airbadge_flashplayer, wp_airbadge_flashplayer_default, 'Flash Player not installed message');
add_option(wp_airbadge_titlecolor, wp_airbadge_titlecolor_default, 'Badge title color');
add_option(wp_airbadge_buttonlabelcolor, wp_airbadge_buttonlabelcolor_default, 'Badge button label color');
add_option(wp_airbadge_appnamecolor, wp_airbadge_appnamecolor_default, 'Badge application name color');
add_option(wp_airbadge_skiptransition, wp_airbadge_skiptransition_default, 'Skip transition');

function badgeParse($text) {
    return preg_replace_callback('|\[airbadge\](.+?),(.+?),(.+?),(.+?)\[/airbadge\]|i', 'handleBadgeParse', $text);
}

function handleBadgeParse($match) {
	
	$div_suffix = substr(uniqid(rand(), true),0,4);

	$code = "<div id=\"flashcontent".$div_suffix."\" style=\"width:215px; height:180px;\">".get_option(wp_airbadge_flashplayer)."</div>";
	$code .= "<script type=\"text/javascript\">\n";
	$code .= "<!-- // <![CDATA[\n";
	$code .= "var so = new SWFObject(\"".get_settings('siteurl')."/wp-content/plugins/air-badge/AIRInstallBadge.swf\", \"Badge\", \"215\", \"180\", \"9.0.115\", \"#FFFFFF\");\n";
	$code .= "so.useExpressInstall(\"".get_settings('siteurl')."/wp-content/plugins/air-badge/expressinstall.swf\");\n";
	$code .= "so.addVariable(\"airversion\", \"1.0\");\n";
	$code .= "so.addVariable(\"appname\", \"".urlencode(trim($match[1]))."\");\n";
	$code .= "so.addVariable(\"appurl\", \"".trim($match[2])."\");\n";
	$code .= "so.addVariable(\"appid\", \"".urlencode(trim($match[1]))."\");\n";
	$code .= "so.addVariable(\"pubid\", \"\");\n";
	$code .= "so.addVariable(\"appversion\", \"".urlencode(trim($match[3]))."\");\n";
	$code .= "so.addVariable(\"imageurl\", \"".trim($match[4])."\");\n";
	$code .= "so.addVariable(\"appinstallarg\", \"installed from web\");\n";
	$code .= "so.addVariable(\"applauncharg\", \"launched from web\");\n";
	$code .= "so.addVariable(\"helpurl\", \"help.html\");\n";
	$code .= "so.addVariable(\"hidehelp\", \"true\");\n";
	$code .= "so.addVariable(\"skiptransition\", \"".get_option(wp_airbadge_skiptransition)."\");\n";
	$code .= "so.addVariable(\"titlecolor\", \"".get_option(wp_airbadge_titlecolor)."\");\n";
	$code .= "so.addVariable(\"buttonlabelcolor\", \"".get_option(wp_airbadge_buttonlabelcolor)."\");\n";
	$code .= "so.addVariable(\"appnamecolor\", \"".get_option(wp_airbadge_appnamecolor)."\");\n";
	$code .= "so.addVariable(\"str_err_airswf\", \"<u>Running locally?</u><br/><br/>The AIR proxy swf won't load properly when this is run from the local file system.\");\n";
	
	$code .= "so.write(\"flashcontent".$div_suffix."\");\n";
	$code .= "// ]]> -->\n";
	$code .= "</script>\n";

	return $code;

}

function addSWFObjectJavaScript() {
	if(get_option(wp_airbadge_swfobject) == "true") {
		echo "\n<script src=\"" . get_settings('siteurl') . "/wp-content/plugins/air-badge/swfobject.js\" type=\"text/javascript\"></script>\n";
	}
}

function addOptionsPage() {
	add_options_page('AIR Badge', 'AIR Badge', 8, basename(__FILE__), 'badgeOptionsPage');
}

function badgeOptionsPage() {

	if (isset($_POST['wp_airbadge_update'])) {
		
		check_admin_referer();
		
		$use_swfobject = $_POST[wp_airbadge_swfobject];
		
		if ($use_swfobject == 'use') {
			update_option(wp_airbadge_swfobject, "true");
		} else {
			update_option(wp_airbadge_swfobject, "false");
		}
		
		$skip_transition = $_POST[wp_airbadge_skiptransition];
		
		if ($skip_transition == 'use') {
			update_option(wp_airbadge_skiptransition, "true");
		} else {
			update_option(wp_airbadge_skiptransition, "false");
		}
		
		update_option(wp_airbadge_titlecolor, $_POST[wp_airbadge_titlecolor]);
		update_option(wp_airbadge_buttonlabelcolor, $_POST[wp_airbadge_buttonlabelcolor]);
		update_option(wp_airbadge_appnamecolor, $_POST[wp_airbadge_appnamecolor]);
		update_option(wp_airbadge_flashplayer, $_POST[wp_airbadge_flashplayer]);
		
		echo "<div class='updated'><p><strong>AIR Badge options updated</strong></p></div>";
		
	}

?>

<form method="post" action="options-general.php?page=wp-airbadge.php">
		<div class="wrap">
			<h2>AIR Badge</h2>
			
				<table class="form-table">
					<tr>
						<th scope="row" valign="top" align="left">
							<label>Embed SWFObject:</label>
						</th>
						<td width="10"></td>
						<td>
							<?php
							echo "<input type='checkbox' ";
							echo "name='".wp_airbadge_swfobject."' ";
							echo "id='".wp_airbadge_swfobject."' ";
							echo "value='use' ";
							if(get_option(wp_airbadge_swfobject) == "true") {
								echo "checked";
							}
							echo " />\n";
							?>
							Disable checkbox if you already have the SWFObject.js included
						</td>
					</tr>
					<tr>
						<th scope="row" valign="top" align="left">
							<label>Flash Player not installed message:</label>
						</th>
						<td width="10"></td>
						<td>
							<textarea name="wp_airbadge_flashplayer" id="wp_airbadge_flashplayer" rows="5" cols="50"><?php echo get_option(wp_airbadge_flashplayer) ?></textarea>
						</td>	
					</tr>					
					<tr>
						<th scope="row" valign="top" align="left">
							<label>Badge title color:</label>
						</th>
						<td width="10"></td>
						<td>
							<input type="text" name="wp_airbadge_titlecolor" id="wp_airbadge_titlecolor" value="<?php echo get_option(wp_airbadge_titlecolor) ?>" />
						</td>	
					</tr>
					<tr>
						<th scope="row" valign="top" align="left">
							<label>Badge button label color:</label>
						</th>
						<td width="10"></td>
						<td>
							<input type="text" name="wp_airbadge_buttonlabelcolor" id="wp_airbadge_buttonlabelcolor" value="<?php echo get_option(wp_airbadge_buttonlabelcolor) ?>" />
						</td>	
					</tr>
					<tr>
						<th scope="row" valign="top" align="left">
							<label>Badge application name color:</label>
						</th>
						<td width="10"></td>
						<td>
							<input type="text" name="wp_airbadge_appnamecolor" id="wp_airbadge_appnamecolor" value="<?php echo get_option(wp_airbadge_appnamecolor) ?>" />
						</td>	
					</tr>
					<tr>
						<th scope="row" valign="top" align="left">
							<label>Use transition effect:</label>
						</th>
						<td width="10"></td>
						<td>
							<?php
							echo "<input type='checkbox' ";
							echo "name='".wp_airbadge_skiptransition."' ";
							echo "id='".wp_airbadge_skiptransition."' ";
							echo "value='use' ";
							if(get_option(wp_airbadge_skiptransition) == "true") {
								echo "checked";
							}
							echo " />\n";
							?>
							Disable checkbox if you don't want the badge transition effect
						</td>
					</tr>																					
					<tr align="left">
						<th></th>
						<td></td>
						<td align="left">
								<div style="align:left"><input name="wp_airbadge_update" value="Save Changes" type="submit" /></div>
						</td>
					</tr>
				</table>
				
		</div>
</form>		

<?php

}

add_action('wp_head', 'addSWFObjectJavaScript');
add_filter('the_content', 'badgeParse');
add_action('admin_menu', 'addOptionsPage');

?>