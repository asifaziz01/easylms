<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$config['global'] = TRUE;

$config['general']['site_title']		= 'Easy Coaching';
$config['general']['home_url']			= 'https://indiatests.in';
$config['general']['contact_email']		= 'contact@indiatests.in';
$config['general']['max_storage']		= 52428800;
$config['general']['max_file_size']		= 20971520;

$config['gst']['gst_slab_18']		    = 18;
$config['gst']['gst_slab_24']		    = 24;
$config['gst']['gst_slab_28']		    = 28;

$config['upload_dir']					= 'contents/';
$config['temp_dir']						= 'contents/temp/';
$config['sys_dir']						= 'contents/system/';
$config['system_logo']					= 'contents/system/system_logo.png';
$config['coaching_logo']				= 'logo.png';
$config['profile_picture_path']			= 'contents/profile_images/';

define ('ACCESS_CODE', 					'EA202000001');

define ('THEME_PATH', 			    	'themes/dore/');
define ('ANSWER_TEMPLATE', 			    'templates/answer_choices/');
define ('SMS_TEMPLATE', 			    'templates/sms/');
define ('EMAIL_TEMPLATE', 			    'templates/email/');

define ('APP_NAME', 					'Easy Coaching');
define ('BRANDING_TEXT', 				'Inovexia Software Services Pvt Ltd');
define ('BRANDING_URL', 				'https://inovexiasoftware.com');

/* MENU TYPES */
define ('MENUTYPE_SIDEMENU', 				1);
define ('MENUTYPE_DASHBOARD', 				2);
define ('MENUTYPE_FOOTER', 					3);

// TREE CATEGORIES
define ('SYS_TREE_TYPE_QB', 			'TREE_TYPE_QB');
define ('SYS_TREE_TYPE_TEST', 			'TREE_TYPE_TEST');
define ('SYS_TREE_TYPE_STUDENT', 		'TREE_TYPE_STUDENT');
define ('SYS_TREE_TYPE_LESSON', 		'TREE_TYPE_LESSON');

// TREE TYPES
define ('TREE_TYPE_QB', 					1);
define ('TREE_TYPE_TEST', 					2);
define ('TREE_TYPE_STUDENT',			 	3);

define ('RECORDS_PER_PAGE',			 		20);

// Default User Roles
define ('USER_ROLE_SUPER_ADMIN', 			1);
define ('USER_ROLE_ADMIN', 					2);
define ('USER_ROLE_TEACHER', 				3);
define ('USER_ROLE_STUDENT', 				4);
define ('USER_ROLE_COACHING_ADMIN',			5);


// Default User Status
define ('USER_STATUS_DISABLED', 			0);
define ('USER_STATUS_ENABLED', 				1);
define ('USER_STATUS_UNCONFIRMED', 			2);
define ('USER_STATUS_ALL', 					3);

define ('USER_LEVEL_SUPER_ADMIN', 			1);
define ('USER_LEVEL_ADMIN', 				2);
define ('USER_LEVEL_COACHING_ADMIN', 		3);
define ('USER_LEVEL_COACHING_STAFF', 		4);
define ('USER_LEVEL_COACHING_STUDENT', 		5);

// SYS Parameters
define ('SYS_USER_STATUS', 					'USER_STATUS');
define ('SYS_STUDENT_LEVELS', 				'STUDENT_CATEGORY_LEVEL');
define ('SYS_REF_ID_PREFIX', 				'REF_ID_PREFIX');
define ('SYS_TST_ID_PREFIX', 				'TST_ID_PREFIX');

define ('SORT_ALPHA_ASC', 					1);
define ('SORT_ALPHA_DESC', 					2);
define ('SORT_CREATION_ASC', 				3);
define ('SORT_CREATION_DESC', 				4);

@date_default_timezone_set('ASIA/KOLKATA');

$config['allowed_mime_types']	=	[ 'csv', 'psd', 'so', 'sea', 'oda', 'pdf', 'ai', 'eps', 'ps', 'smi', 'smil', 'mif', 'xls', 'ppt', 'pptx', 'wbxml', 'wmlc', 'dcr', 'dir', 'dxr', 'dvi', 'gtar', 'gz', 'gzip', 'swf', 'zip', 'rar', 'mid', 'midi', 'mpga', 'mp2', 'mp3', 'aif', 'aiff', 'aifc', 'ram', 'rm', 'rpm', 'ra', 'rv', 'wav', 'bmp', 'gif', 'jpeg', 'jpg', 'jpe', 'jp2', 'j2k', 'jpf', 'jpg2', 'jpx', 'jpm', 'mj2', 'mjp2', 'png', 'tiff', 'tif', 'txt', 'text', 'rtx', 'rtf', 'xsl', 'mpeg', 'mpg', 'mpe', 'qt', 'mov', 'avi', 'movie', 'doc', 'docx', 'xlsx', '3g2', '3gp', 'mp4', 'm4a', 'f4v', 'flv', 'webm', 'aac', 'm4u', 'm3u', 'vlc', 'wmv', 'au', 'ac3', 'ogg', 'wma', 'ico', 'odc', 'otc', 'odf', 'otf', 'odg', 'otg', 'odi', 'oti', 'odp', 'otp', 'ods', 'ots', 'odt', 'odm', 'ott', 'oth'];
