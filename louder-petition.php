<?php
 /*
Plugin Name: Louder petition
Plugin URI: http://www.louder.org.uk/plugins_wp.php
Description: Display Petition Tool from Louder campaign
Version: 1.0 beta
Author: Adam Sargant
Author URI: http://www.adamsargant.net
License: GPL2

Copyright 2010  Adam Sargant  (email : adam@sargant.net)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
function widget_louderpetition_register(){
	function widget_louderpetition($args) {
		extract($args);
		$louderpetition_campaignslug=get_option('louderpetition_campaignslug');
		$louderurl="http://www.louder.org.uk/api/getpetitiondata?key=FZWsTqAUKJgMAzuZVzASwGYe&campaignslug=$louderpetition_campaignslug";
		//only display if the file actually exists, if SOMETHING is returned
		if(louderpetition_curl_file_exists($louderurl)){
			$loudercampaignxml=simplexml_load_file($louderurl);
			//only display if it is NOT an error message that is returned
			if(!$loudercampaignxml->errormessage){
				?>
				<?php echo $before_widget; ?>
				<?php echo $before_title
				."Louder.org.uk Petition"
				. $after_title; ?>
				<?php
					$siteurl=get_option('siteurl');
					$petitionvrcode=generatePassword(4,0);
					$output="<h3>".$loudercampaignxml->petitiontitle."</h3><p>".$loudercampaignxml->petitiondescription."</p><form action=\"http://www.louder.org.uk/$louderpetition_campaignslug/\" name=\"petition\" method=\"post\" onsubmit=\"return validate_form();\">";
					$output.="<table class=\"tablealign\"><tr><td class=\"tabletextcorrect\">Name</td>"; 
					$output.="<td><input type=\"text\" name=\"field1\" size=\"14\" /></td></tr>";
					$output.="<tr><td class=\"tabletextcorrect\">Email</td>"; 
					$output.="<td><input type=\"text\" name=\"field2\" size=\"14\" /></td></tr>";
					$output.="<tr><td class=\"tabletextcorrect\">Confirm Email</td>"; 
					$output.="<td><input type=\"text\" name=\"field3\" size=\"14\" /></td></tr>";
					$output.="<tr><td class=\"tabletextcorrect\">Town</td>"; 
					$output.="<td><input type=\"text\" name=\"field4\" size=\"14\" /></td></tr>";
					$output.="<tr><td class=\"tabletextcorrect\">Postal Code</td>"; 
					$output.="<td><input type=\"text\" name=\"field5\" size=\"14\" /></td></tr>";
					$output.="<tr><td class=\"tabletextcorrect\">Country</td>"; 
					$output.="<td><input type=\"text\" name=\"field6\" size=\"14\" /></td></tr>";
					$output.="<tr><td class=\"tabletextcorrect\">Enter code:</td>"; 
					$output.="<td><strong>$petitionvrcode</strong> <input type=\"text\" name=\"field7\" size=\"6\" />
					<input type=\"hidden\" name=\"wpvrcode\" value=\"$petitionvrcode\" />
					<input type=\"hidden\" name=\"source\" value=\"wp_petition_widget\" />
					<input type=\"hidden\" name=\"siteurl\" value=\"$siteurl\" /></td></tr>";
					$output.="<tr><td></td>"; 
					$output.="<td><input type=\"submit\" name=\"petition_action\" value=\"Sign Petition\" /></td></tr>";
					$output.="</table></form>";
					echo $output;
				echo $after_widget; ?>
				<?php
			}
		}
	}
	


	register_sidebar_widget('Louder Petition Widget','widget_louderpetition');
	function widget_louderpetition_options() {
		if ($_POST['louderpetition_campaignslug']) {
			$louderpetition_campaignslug=$_POST['louderpetition_campaignslug'];
			update_option('louderpetition_campaignslug',$louderpetition_campaignslug);
		}
		$louderpetition_campaignslug=get_option('louderpetition_campaignslug');
		$louderurl="http://www.louder.org.uk/api/getpetitiondata?key=FZWsTqAUKJgMAzuZVzASwGYe&campaignslug=$louderpetition_campaignslug";
		if(louderpetition_curl_file_exists($louderurl)){
			$loudercampaignxml=simplexml_load_file($louderurl);
			if($loudercampaignxml->errormessage){
				echo "<p style=\"color:red;\">$loudercampaignxml->errormessage</p>";
			}
			echo '<label for="louderpetition_campaignslug">Louder Campaign Slug* : <input id="louderpetition_campaignslug" name="louderpetition_campaignslug" type="text" value="'.$louderpetition_campaignslug.'" /></label><br />*The campaign slug is the part of the campaign url as follows www.louder.org.uk/<em>campaignslug</em>/ without any backslashes';
		}
		else{
			echo "<p style=\"color:red;\">The API could not be retrieved. This may be because the site is unavailable. Please notify the plugin author on <a href=\"mailto:adamsargant@gmail.com\">adamsargant@gmail.com</a></p>";
		}
	}
	register_widget_control('Louder Petition Widget',  'widget_louderpetition_options');

}
add_action('init', widget_louderpetition_register);

function louderpetition_curl_file_exists($url){
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_NOBODY, true);
	curl_exec($ch);
	$retcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	// $retcode > 400 -> not found, $retcode = 200, found.
	curl_close($ch);
	if($retcode==200){
		return TRUE;
	}
	else{
		return FALSE;
	}
}

function generatePassword($length=9, $strength=0) {
    $vowels = 'aeuy';
    $consonants = 'bdghjmnpqrstvz';
    if ($strength & 1) {
        $consonants .= 'BDGHJLMNPQRSTVWXZ';
    }
    if ($strength & 2) {
        $vowels .= "AEUY";
    }
    if ($strength & 4) {
        $consonants .= '23456789';
    }
    if ($strength & 8) {
        $consonants .= '@#$%';
    }

    $password = '';
    $alt = time() % 2;
    for ($i = 0; $i < $length; $i++) {
        if ($alt == 1) {
            $password .= $consonants[(rand() % strlen($consonants))];
            $alt = 0;
        } else {
            $password .= $vowels[(rand() % strlen($vowels))];
            $alt = 1;
        }
    }
    return $password;
}
?>
