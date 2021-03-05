<?php

function civi_api($area, $action, $params, $dbg = true, $limit = true) {
	global $wordpress_base;
	#  the next three lines are site specific
	require_once("{$wordpress_base}/wp-content/uploads/civicrm/civicrm.settings.php");
	require_once("{$wordpress_base}/wp-content/plugins/civicrm/civicrm/api/api.php");
	define('ABSPATH' , "{$wordpress_base}/");
	define('WPINC' , "wp-includes");
	# prep the area-specific parameters
	switch ($area) {
		case 'Contact' :
	$params['sequential'] = 1;			#  needed in all apis
	$params['is_deleted'] = 0;			#  exclude deleted contacts
	$params['is_deceased'] = 0;			#  exclude deceased contacts	
			break;
		case 'Activity' :
	$params['is_deleted'] = 0;			#  exclude deleted activities
	$params['is_current_revision'] = 1;	#  exclude obsolete data
	$params['sequential'] = 1;
			break;
		case 'Event' :
	$params['sequential'] = 1;
	$params['is_active'] = 1;			#  exclude disabled events
			break;
		default :
	$params['sequential'] = 1;			
	}
	if ($limit === false) {
		$params['options'] = array("limit" => 0);
	}
	#  confirm that we have a valid action
	switch ($action) {
		case 'get' :
			break;
		case 'create' :
			break;
		case 'update' :
			break;
		case 'delete' :
			break;
		default :
			return false;
	}
	if ($dbg) {debug("calling CiviCRM API for $area, action $action, parameters\n".dump_array($params));}
	
	try{
		$result = civicrm_api3($area, $action, $params); 
	}
	catch (CiviCRM_API3_Exception $e) {
  		// handle error here
  		$errorMessage = $e->getMessage();
  		$errorCode = $e->getErrorCode();
  		$errorData = $e->getExtraParams();
  		return array('error' => $errorMessage, 'error_code' => $errorCode, 'error_data' => $errorData);
	}

	return $result;
	
}
#  ------------------------------------------------------------------------------------------

function get_contactid($data) {
	$params = array(
		'first_name' => $data['first_name'],
		'last_name'  => $data['last_name'],
		'email'      => $data['email'],
	);
	$result = civi_api("contact", 'get', $params);
	debug ("civi_api returned ".dump_array($result));
		if ( ( $result === false ) OR ( $result['is_error'] > 0 ) ) {
		print "error found";
		throw new Exception("internal API error.",ERROR_MAJOR);
	}
	if ( $result['count'] == 1 ) {
		# we have a unique result!   return the data
		print "get_contactid returning unique {$result['values'][0]['id']}";
		return $result['values'][0]['id'];
	}
	if ( $result['count'] > 1) {
		#  we have multiple contacts, return the lowest numbered since highers are likely duplicates
		$out = array();
		$outid = 99999999;    
		foreach ($result['values'] as $contact) {
			if ( ( $contact['contact_id'] > 0 ) and ( $contact['contact_id'] < $outid ) ) {
				$out = $contact;
				$outid = $contact['contact_id'];
			} 
		}
		if ( $outid < 99999999) {
			#  return the lowest numbered one
			return $out['contact_id'];
		} else {
			#  report an error.
			return false;
		}
	} 
}
