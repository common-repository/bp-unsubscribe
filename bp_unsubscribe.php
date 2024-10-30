<?php
/*
Plugin Name: BP Unsubscribe
Plugin URI: http://www.aheadzen.com
Description: Unsubscribe plugin to stop emails coming from buddypress.
Version: 1.0.6
Author: Ask Oracle Team
Author URI: http://ask-oracle.com/

Copyright: © 2014-2015 ASK-ORACLE.COM
License: GNU General Public License v3.0
License URI: http://www.gnu.org/licenses/gpl-3.0.html
*/

$plugin_dir_path = dirname(__FILE__);
$plugin_dir_url = plugins_url('', __FILE__);

	
class az_bp_unsubscribe {
	public function __construct() {
		add_action('bp_init',array($this,'bp_init_fun'),999);
		add_action('bp_notification_settings',array($this,'notification_settings'),0);
		add_action('bp_core_notification_settings_after_save',array($this,'save_notificaiton_data'),9999);
		add_shortcode('az_unsubscribe_emails', array($this,'unsubscribe_emails_shortcode'));
		
		add_filter('bp_activity_at_message_notification_message',array($this,'az_bp_activity_at_message_notification_message'),99,5);
		add_filter('bp_activity_new_comment_notification_message',array($this,'az_bp_activity_at_message_notification_message'),99,5);
		add_filter('bp_activity_new_comment_notification_comment_author_message',array($this,'az_bp_activity_new_comment_notification_comment_author_message'),99,5);
		
		add_filter('groups_at_message_notification_message',array($this,'az_groups_at_message_notification_message'),99,6);
		
		add_filter('friends_notification_new_request_message',array($this,'az_bp_activity_at_message_notification_message'),99,5);
		add_filter('friends_notification_accepted_request_message',array($this,'az_friends_notification_accepted_request_message'),99,4);
		
		add_filter('groups_notification_group_updated_message',array($this,'az_groups_notification_group_updated_message'),99);
		add_filter('groups_notification_membership_request_completed_message',array($this,'az_groups_notification_group_updated_message'),99);
		add_filter('groups_notification_new_membership_request_message',array($this,'az_groups_notification_new_membership_request_message'),99);
		add_filter('groups_notification_group_invites_message',array($this,'az_groups_notification_new_membership_request_message'),99);
		
		
		add_filter('messages_notification_new_message_message',array($this,'az_messages_notification_new_message_message'),99,6);
		
		add_filter('az_voter_mail_content',array($this,'az_messages_notification_voter_mail_content'),99,4);
		
		add_filter('bp_follow_notification_message',array($this,'az_bp_follow_notification_message'),99,4);
		
		
		/** New filter for buddypress 2.5+ **/
		
		$this->user_id = 0;
	}
	
	public function bp_init_fun()
	{
		load_plugin_textdomain('aheadzen', false, basename( dirname( __FILE__ ) ) . '/languages');
		
		$this->user_id = $this->get_user_id();
		if($_GET['az_unsubscribe']=='all'){
			$this->az_subscribe_unsubscribe('no');			
		}elseif($_GET['az_subscribe']=='all'){
			$this->az_subscribe_unsubscribe('yes');		
		}
		//$user_notifications = $this->get_all_notification_settings();print_r($user_notifications);
	}
	
	
	
	public function get_user_id(){
		if(current_user_can('administrator')){
			$user_id = bp_displayed_user_id();
		}else{
			$user_id = get_current_user_id();
		}
		
		 apply_filters('azbp_unsubscribe_user_id',$user_id);
		 return $user_id;
	}
	
	public function az_subscribe_unsubscribe($theval='yes'){
		$displayed_user_id = bp_displayed_user_id();
		$this->user_id = $this->get_user_id();
		
		if($displayed_user_id==0){
			if($this->user_id){
				$current_user = $this->get_user_notification_link($this->user_id);
				wp_redirect($current_user);exit;
			}else{
				bp_core_no_access();exit;
			}
		}
		
		if($displayed_user_id!=$this->user_id){
			if(current_user_can('administrator')){
				$this->user_id = $displayed_user_id;				
			}else{
				$current_user = $this->get_user_notification_link($this->user_id);
				wp_redirect($current_user);
			}
		}
		
		$user_notifications = $this->get_all_notification_settings();
		if($user_notifications){
			foreach($user_notifications as $key => $val){
				bp_update_user_meta($this->user_id,$key,$theval);
			}
		}
	}
	
	public function get_all_notification_settings()
	{
		$notifications = array();
		$usermeta = bp_get_user_meta($this->user_id,'',false);
		if($usermeta){
			foreach($usermeta as $key => $val){
				if($key!='notification_emails_all' && strstr($key,'notification_')){
					$notifications[$key] = $val[0];
				}				
			}
		}
		
		$core_notification_settings = array(
			'notification_messages_new_message',
			'notification_activity_new_mention',
			'notification_activity_new_reply',
			'notification_groups_invite',
			'notification_groups_group_updated',
			'notification_groups_admin_promotion',
			'notification_groups_membership_request',
			'notification_membership_request_completed',
			'notification_friends_friendship_request',
			'notification_friends_friendship_accepted',
			'notification_starts_following',
			'notification_like_votes',
		);
		$core_notification_settings = apply_filters('az_bp_unsubscribe_notification_keys',$core_notification_settings);
		
		for($i=0;$i<count($core_notification_settings);$i++){
			if(!$notifications[$core_notification_settings[$i]]){
				$notifications[$core_notification_settings[$i]] = 'yes';
			}
		}
		
		return $notifications;
	}
	
	public function notification_settings() {		
	?>

	<table class="notification-settings" id="activity-notification-settings">
		<thead>
			<tr>
				<th class="icon">&nbsp;</th>
				<th class="title"><?php _e( 'All Emails', 'aheadzen' ) ?></th>
				<th class="yes"><?php _e( 'Yes', 'aheadzen' ) ?></th>
				<th class="no"><?php _e( 'No', 'aheadzen' )?></th>
			</tr>
		</thead>

		<tbody>
			<tr id="notification_emails_all_settings">
				<td>&nbsp;</td>
				<td><?php _e( "Receive All Email Notifications", 'aheadzen' ) ?></td>
				<td class="yes"><input type="radio" name="notifications[notification_emails_all]" id="notification_emails_all_settings_yes" value="yes" <?php //checked( $emails_all, 'yes', true ) ?>/></td>
				<td class="no"><input type="radio" name="notifications[notification_emails_all]" id="notification_emails_all_settings_no" value="no" <?php //checked( $emails_all, 'no', true ) ?>/></td>
			</tr>

			<?php do_action( 'az_all_emails_screen_notification_settings' ) ?>
		</tbody>
	</table>
	<?php
	}
	
	public function save_notificaiton_data() {
		$user_id = $this->user_id;
		$settings = array();
		if(!$_POST['notifications']['notification_emails_all'])return;		
		
			foreach($_POST['notifications'] as $key => $val){
				if(strstr($key,'notification_')){
					$key_val = $_POST['notifications']['notification_emails_all']=='yes' ? 'yes' : 'no';
					$settings[$key] = $key_val;
				}
			}

		bp_settings_update_notification_settings($user_id,$settings);
	}
	
	public function get_user_notification_link($user_id){
		$settings_slug = function_exists( 'bp_get_settings_slug' ) ? bp_get_settings_slug() : 'settings';
		$notification_url = bp_core_get_user_domain($user_id) . $settings_slug. '/notifications/';
		return add_query_arg('az_unsubscribe','all',$notification_url );
	}
	
	public function get_unsubscribe_link($settings_link){
		return sprintf(__('<br /><br /><a href="%s?az_unsubscribe=all">Unsubscribe</a> from these emails.','aheadzen'),$settings_link);
	}
	
	public function az_bp_activity_at_message_notification_message($message, $poster_name, $content, $message_link, $settings_link){
		$message .= $this->get_unsubscribe_link($settings_link);
		return $message;
	}
	
	public function az_bp_activity_new_comment_notification_comment_author_message($message, $poster_name, $content, $settings_link, $thread_link){
		$message .= $this->get_unsubscribe_link($settings_link);
		return $message;
	}
	
	public function az_groups_at_message_notification_message($message, $group, $poster_name, $content, $message_link, $settings_link){
		$message .= $this->get_unsubscribe_link($settings_link);
		return $message;
	}
	
	public function az_friends_notification_accepted_request_message($message, $friend_name, $friend_link, $settings_link){
		$message .= $this->get_unsubscribe_link($settings_link);
		return $message;
	}
	
	public function az_messages_notification_new_message_message($message, $sender_name, $subject, $content, $message_link, $settings_link){
		$message .= $this->get_unsubscribe_link($settings_link);
		return $message;
	}
	
	public function az_groups_notification_group_updated_message($args){
		$args[0] .= $this->get_unsubscribe_link($args[3]);		
		return $args;
	}
	
	public function az_groups_notification_new_membership_request_message($args){
		$args[0] .= $this->get_unsubscribe_link($args[5]);		
		return $args;
	}
	
	public function az_messages_notification_voter_mail_content($message,$subject,$settings_link,$userdata){
		$settings_link = bp_core_get_user_domain($userdata->ID);	
		$settings_link .= function_exists( 'bp_get_settings_slug' ) ? bp_get_settings_slug() : 'settings';
		$settings_link .= '/notifications/';
		$message .= $this->get_unsubscribe_link($settings_link);
		return $message;
	}
	
	public function az_bp_follow_notification_message($message, $follower_name, $follower_link, $r){
		$settings_link = bp_core_get_user_domain( $r['leader_id'] ) . BP_SETTINGS_SLUG . '/notifications/';
		$message .= $this->get_unsubscribe_link($settings_link);
		return $message;
	}
	
	/*******************************
	shotcode :: [az_unsubscribe_emails user_id=100]
	****************************/
	function unsubscribe_emails_shortcode($atts) {
		$atts['shortcode']=1;
		$user_id = $atts['user_id'];
		if($user_id) $this->user_id = $user_id;
			
		$return_url = '';
		if($this->user_id>0){
			$return_url =  $this->get_user_notification_link($this->user_id);					
		}else{
			$return_url = add_query_arg('az_unsubscribe','all',site_url());
		}
		return $return_url;
	}
}

new az_bp_unsubscribe();


function az_messages_set_tokens($tokens, $tokens2, $args)
{
	/*echo '<pre>';
	print_r($tokens);
	print_r($tokens2);
	print_r($args);
	*/
	$thelink = '';
	$settingsArr = explode('/messages/view/',$tokens['message.url']);
	if($settingsArr && $settingsArr[0]){
		$thelink = $settingsArr[0].'/';
	}elseif($tokens['receiver-user.id']){
		$thelink = bp_core_get_user_domain($tokens['receiver-user.id']);		
	}
	$thelink .= function_exists( 'bp_get_settings_slug' ) ? bp_get_settings_slug() : 'settings';
	$tokens['askunsubscribetoken'] = $thelink.'/notifications/?az_unsubscribe=all';
	return $tokens;
}
add_filter('bp_email_set_tokens','az_messages_set_tokens',99,3);


add_filter('bp_email_set_content_html','az_messages_content_plaintext',9);
add_filter('bp_email_set_content_plaintext','az_messages_content_plaintext',9);
function az_messages_content_plaintext($content)
{
	$content .= __('<br /><a href="{{{askunsubscribetoken}}}">Unsubscribe</a> from these emails.','aheadzen');
	return $content;
}

