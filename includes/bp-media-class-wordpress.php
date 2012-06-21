<?php

/**
 * 
 */

class BP_Media_Host_Wordpress implements BP_Media {
	
	private $id,			//id of the entry
			$name,			//Name of the entry
			$description,	//Description of the entry
			$url,			//URL of the entry
			$type,			//Type of the entry (Video, Image or Audio)
			$owner;			//Owner of the entry.
	
	
	function add_media($name,$description) {
		global $bp;
		include_once(ABSPATH.'wp-admin/includes/file.php');
		include_once(ABSPATH.'wp-admin/includes/image.php');
		 //media_handle_upload('async-upload', $_REQUEST['post_id']);
		$postarr = array(
			'post_status' => 'draft', 
			'post_type' => 'bp_media', 
			'post_content' => $description, 
			'post_title' => $name);
		$post_id=wp_insert_post($postarr);
		
		
//		$id=media_handle_upload('bp_media_file', $post_id);
//		if ( is_wp_error($id) ) {
//			wp_delete_post($post_id, true);
//			//return false;
//		}
		
		
		$file=wp_handle_upload($_FILES['bp_media_file']);
		if ( isset($file['error']) || $file===null ){
			wp_delete_post($post_id, true);
			return false;
		}

		$attachment=array();
		$url = $file['url'];
		$type = $file['type'];
		$file = $file['file'];
		$title = $name;
		$content = $description;
		$attachment =  array(
			'post_mime_type' => $type,
			'guid' => $url,
			'post_title' => $title,
			'post_content' => $content,
			'post_parent' => $post_id,
		);
		$activity_content	=	'<span class="bp_media_title">'.$name.'</span><span class="bp_media_description">'.$description.'</span><span class="bp_media_content">';
		switch($type) {
			case 'video/mp4'	:	$activity_content.='<video src="'.$url.'" width="640" height="480" type="video/mp4" id="bp_media_video_'.$post_id.'" controls="controls" preload="none"></video><script>jQuery("#bp_media_video_'.$post_id.'").mediaelementplayer();</script></span>';
									$activity_url	=trailingslashit(bp_loggedin_user_domain().BP_MEDIA_VIDEOS_SLUG.'/'.$post_id);
									break;
			case 'audio/mp3'	:	$activity_content.='<audio src="'.$url.'" type="audio/mp3" id="bp_media_audio_'.$post_id.'" controls="controls" ></audio><script>jQuery("#bp_media_audio_'.$post_id.'").mediaelementplayer();</script>';
									$activity_url	=trailingslashit(bp_loggedin_user_domain().BP_MEDIA_AUDIO_SLUG.'/'.$post_id);
									break;
		}
		$activity_content .= '</span>';
		$attachment_id = wp_insert_attachment($attachment, $file, $post_id);	
		if ( !is_wp_error($attachment_id) ) {
			wp_update_attachment_metadata( $attachment_id, wp_generate_attachment_metadata( $attachment_id, $file ) );
		}
		$postarr['post_excerpt']= trailingslashit(bp_loggedin_user_domain().BP_MEDIA_IMAGES_SLUG.'/'.$post_id);
		$postarr['ID'] = $post_id;
		$postarr['post_mime_type']=$type;
		$postarr['post_status']='published';
		
		wp_insert_post($postarr);
		bp_media_record_activity(array(
			'action'		=>	sprintf(__("%s uploaded a media."),bp_core_get_userlink(bp_loggedin_user_id() )),
			'content'		=>	$activity_content,
			'primary_link'	=>	$activity_url,
			'type'			=>	'media_upload'
		));
	}
	function remove_media() {
		
	}
	function update_media() {
		
	}
	function display_media() {
		
	}
}
?>