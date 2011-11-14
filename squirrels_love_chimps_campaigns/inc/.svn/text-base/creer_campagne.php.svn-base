<?php
if (!defined("_ECRIRE_INC_VERSION")) ;

//Cree la campagen avec les infos fournis api: http://apidocs.mailchimp.com/api/rtfm/campaigncreate.func.php
function inc_creer_campagne_dist($apikey,$type='',$options='',$content='', $segment_opts='',$type_opts=''){
	
# API mailchimp
include_spip('inc/1.3/MCAPI.class');	
	
$api = new MCAPI($apikey);

$retval = $api->campaignCreate($type,$options,$content,$segment_opts,$type_opts);

if ($api->errorCode){
	$message_erreur .= "Unable to Create New Campaign!";
	$message_erreur .= "\n\tCode=".$api->errorCode;
	$message_erreur .= "\n\tMsg=".$api->errorMessage."\n";
	spip_log('Erreur créer nouvelle campagne'.$message_erreur,'squirrel_chimp');
} else {
	spip_log('Créer nouvelle campagne ID:'.$retval,'squirrel_chimp');
}
$return=array('message_erreur'=>$message_erreur,'id_campagne'=>$retval);

return $return;
}

?>
