<?php
/*
 Plugin Name: Multiple Admin Email Addresses
 Plugin URI: http://???
 Description: Allows setting comma separated list of admin emails in general options.
 Version: 1.1.2
 Author: Nimrod Cohen
 Author URI: https://www.linkedin.com/in/nimrodcohen/
 License: GPL2
 */

class MultiAdminEmails
{
	public function __construct()
	{
		if(get_bloginfo('version') >= 4.9)
		{
			add_filter('admin_init' ,[$this,'register_fields']);
			add_filter('pre_update_option_multiple_admin_emails', [$this, 'sanitize_multiple_emails_49'], 10, 2);
			add_filter('option_admin_email',[$this,'get_real_addresses']);
			add_action('update_option_multiple_admin_emails',[$this,'remove_hashes'],10,3);
		}
		else
			add_filter('pre_update_option_admin_email',[$this,'sanitize_multiple_emails'],10,2);
	}

	public function remove_hashes($option,$old_value,$value)
	{
		delete_option( 'adminhash' );
		delete_option( 'new_admin_email' );
	}

	public function register_fields()
	{
		register_setting( 'general', 'multiple_admin_emails');
		add_settings_field('multiple_admin_emails', '<label for="multiple_admin_emails">'.__('Multiple Admin Emails' , 'multiple_admin_emails' ).'</label>' ,[$this,'fields_html'], 'general' );
	}

	public function fields_html()
	{
		$value = get_option( 'multiple_admin_emails', '' );
		echo '<input type="text" id="multiple_admin_emails" name="multiple_admin_emails" class="regular-text ltr" value="' . $value . '" />';
		echo '<p class="description" id="multiple-admin-emails-description">This address overrides the Admin Email. You can add one or more emails seperated by commas</p>';
	}

	public function get_real_addresses($value)
	{
		$multi = get_option('multiple_admin_emails');

		if(strlen($multi) == 0)
			return $value;
		return $multi;
	}

	public function sanitize_multiple_emails_49($value,$oldValue)
	{
		$result = "";
		$emails = explode(",",$value);
		foreach($emails as $email)
		{
			$email = trim($email);
			$email = sanitize_email( $email );
			if(!is_email($email))
				return $oldValue;
			$result .= $email.",";

		}

		if(strlen($result == ""))
			return $oldValue;
		$result = substr($result,0,-1);

		return $result;
	}

	public function sanitize_multiple_emails($value,$oldValue)
	{
		if(!isset($_POST["admin_email"]))
			return $value;
		else
			$emails = $_POST["admin_email"];

		$result = "";
		$emails = explode(",",$emails);
		foreach($emails as $email)
		{
			$email = trim($email);
			$email = sanitize_email( $email );
			if(!is_email($email))
				return $value;
			$result .= $email.",";

		}

		if(strlen($result == ""))
			return $value;
		$result = substr($result,0,-1);

		return $result;
	}
}

$multiAdminEmails = new MultiAdminEmails();
