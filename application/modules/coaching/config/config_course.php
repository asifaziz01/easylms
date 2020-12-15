<?php if (!defined('BASEPATH')) {exit('No direct script access allowed');}

$config['config_course'] = '';

// Lesson status
define ('LESSON_STATUS_ALL', 					-1);
define ('LESSON_STATUS_PUBLISHED', 				1);
define ('LESSON_STATUS_UNPUBLISHED', 			0);

// Lesson Attachments
define ('LESSON_ATT_YOUTUBE', 					1);
define ('LESSON_ATT_EXTERNAL', 					2);
define ('LESSON_ATT_UPLOAD', 					3);

// Lesson Durations
define ('LESSON_DURATION_MIN', 					1);
define ('LESSON_DURATION_HOUR', 				2);
define ('LESSON_DURATION_WEEK', 				3);

// Teachers Type
define ('TEACHERS_ASSIGNED', 					1);
define ('TEACHERS_NOT_ASSIGNED', 				2);

define ('COURSE_ENROLMENT_DIRECT', 				1);
define ('COURSE_ENROLMENT_BATCH', 				2);

define ('SCHEDULE_REPEAT_DAILY', 				1);
define ('SCHEDULE_REPEAT_WEEKLY', 				2);

define ('COURSE_CONTENT_TEST', 					1);
define ('COURSE_CONTENT_CHAPTER', 				2);

define('COURSE_STATUS_INACTIVE', 				0);
define('COURSE_STATUS_ACTIVE', 					1);
define('COURSE_STATUS_TRASH', 					2);

define('CATEGORY_STATUS_ALL', 					-1);
define('CATEGORY_STATUS_INACTIVE', 				0);
define('CATEGORY_STATUS_ACTIVE', 				1);
define('CATEGORY_STATUS_TRASH', 				2);