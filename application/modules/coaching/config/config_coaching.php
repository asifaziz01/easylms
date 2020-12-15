<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

$config['config_coachings'] = '';

if (! defined ('INCLUDE_PATH')) define ('INCLUDE_PATH', 			    'coaching/layout/');


define ('COACHING_ID_PREFIX1', 			'EA');
define ('COACHING_ID_PREFIX2', 			date ('Y'));
define ('COACHING_ID_INCREMENT', 		1);
define ('COACHING_ID_PADDING', 			4);

define ('FREE_SUBSCRIPTION_PLAN_ID', 	1);		// Fixed 
define ('FREE_SUBSCRIPTION_PERIOD', 	30);	// 30 Days

// Coaching Account Status
define ('COACHING_ACCOUNT_ACTIVE', 		1);
define ('COACHING_ACCOUNT_PENDING', 	2);
define ('COACHING_ACCOUNT_DISABLED', 	3);

// Subscription Status
define ('SUBSCRIPTION_STATUS_ACTIVE', 	1);
define ('SUBSCRIPTION_STATUS_PENDING', 	2);
define ('SUBSCRIPTION_STATUS_PAUSED', 	3);
define ('SUBSCRIPTION_STATUS_STOPPED', 	4);

// Customer Calling Status
define ('STATUS_SUBMITTED', 			1);
define ('STATUS_DISCUSSED', 			2);
define ('STATUS_PAYMENT_RELEASED', 		3);
define ('STATUS_PAYMENT_RECEIVED', 		4);
define ('STATUS_APPROVED', 				5);
define ('STATUS_DECLINED', 				6);
define ('STATUS_PENDING', 				7);

define ('ATTENDANCE_PRESENT', 			1);
define ('ATTENDANCE_LEAVE', 			2);
define ('ATTENDANCE_ABSENT', 			3);

/*---// TESTS //---*/

define ('SYS_TEST_TYPE', 					'TEST_TYPE');
define ('SYS_TEST_MODE', 					'TEST_MODE');
define ('SYS_TEST_LEVELS', 					'TEST_CATEGORY_LEVEL');

define ('TEST_TYPE_REGULAR', 				1);
define ('TEST_TYPE_PRACTICE',				2);
define ('TEST_TYPE_PUBLIC',					3);

define ('TEST_MODE_ONLINE', 				1);
define ('TEST_MODE_OFFLINE', 				2);

define ('TEST_STATUS_UNPUBLISHED', 			0);
define ('TEST_STATUS_PUBLISHED', 			1);

define ('RELEASE_EXAM_NEVER', 				1);
define ('RELEASE_EXAM_ONDATE', 				2);
define ('RELEASE_EXAM_IMMEDIATELY', 		3);
define ('RELEASE_EXAM_ALLMARKED', 			4); 
 
define ('TEST_ADDQ_QB', 					1);
define ('TEST_ADDQ_CREATE', 				2);
define ('TEST_ADDQ_UPLOAD', 				3);

define ('TEST_LEVEL_CATEGORY', 				1);
define ('TEST_LEVEL_EXAM', 					2);
define ('TEST_LEVEL_EXAMTYPE', 				3);
define ('TEST_LEVEL_YEAR', 					4); 

define ('ENROLED_IN_TEST', 					1); 
define ('NOT_ENROLED_IN_TEST', 				2);
define ('ARCHIVED_IN_TEST', 				3); 

define ('TEST_TYPE_ONGOING', 				1); 
define ('TEST_TYPE_UPCOMING', 				2); 
define ('TEST_TYPE_PREVIOUS', 				3); 

define ('SUMMARY_REPORT', 					1);
define ('BRIEF_REPORT', 					2);
define ('TOPIC_REPORT', 					3);
define ('DIFFICULTY_REPORT', 				4);
define ('CATEGORY_REPORT', 					5);
define ('DETAIL_REPORT', 					6); 
define ('OVERALL_REPORT', 					7); 

define ('TQ_NOT_ANSWERED', 					0); 
define ('TQ_WRONG_ANSWERED', 				1); 
define ('TQ_CORRECT_ANSWERED', 				2); 

/*---// QB //---*/

/* System Parameters */
define ('SYS_QB_LEVELS', 					'QB_LEVEL');
define ('SYS_QUESTION_TYPES', 				'QUESTION_TYPE');
define ('SYS_QUESTION_CLASSIFICATION', 		'QUESTION_CLASSIFICATION');
define ('SYS_QUESTION_DIFFICULTIES',		'QUESTION_DIFFICULTY');
define ('SYS_QUESTION_CATEGORIES',			'QUESTION_CATEGORY');

/* Lingual */
define ('SYS_QUESTION_LANGUAGE',		  	'QUESTION_LANGUAGE'); 
define ('HINDI',		  					2); 
define ('ENGLISH',	  						1); 

/* QTYPE = question types: */
define ('QUESTION_MCSC',       				1);
define ('QUESTION_TF',   	  				2);
define ('QUESTION_LONG',  	  				3);
define ('QUESTION_MATCH',      				5);
define ('QUESTION_BLANK',	  				6); 
define ('QUESTION_MCMC',		  			7); 
define ('QB_NUM_ANSWER_CHOICES',		  	6);