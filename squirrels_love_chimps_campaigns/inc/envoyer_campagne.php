<?php
if (!defined("_ECRIRE_INC_VERSION")) ;

// Envoyer un campagne api: http://apidocs.mailchimp.com/api/rtfm/campaignsendnow.func.php
function inc_envoyer_campagne_dist($apikey,$cid){
	
# API mailchimp
include_spip('inc/1.3/MCAPI.class');	
	
$api = new MCAPI($apikey);
 
$retval = $api->campaignSendNow($cid);

if ($api->errorCode){
	$message_erreur .= "Unable to Send Campaign!";
	$message_erreur .= "\n\tCode=".$api->errorCode;
	$message_erreur .= "\n\tMsg=".$api->errorMessage."\n";
} else {
	$message_ok .= "Campaign Sent!\n";
}
$return = array('message_erreur'=>$message_erreur,'message_ok'=>$message_ok);

return $return;
}

?>
