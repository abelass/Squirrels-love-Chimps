<?php

if (!defined("_ECRIRE_INC_VERSION")) return;

// filtre qui fournit une listes des differente campagne mailchimp : "http://apidocs.mailchimp.com/api/rtfm/campaigns.func.php"
function liste_campaigns($apikey,$filters='',$start='',$LIMIT='1000'){
	# API mailchimp
	include_spip('inc/1.3/MCAPI.class');
	
	$api = new MCAPI($apikey);
 
	$retval = $api->campaigns($filters,$start,$limit);
	
	if ($api->errorCode){
		$retval['message_erreur'] .= _T('scc:erreur_liste_campaign')."\n";
		$retval['message_erreur'] .= _T('scl:code').$api->errorCode."\n";
		$retval['message_erreur'] .= _T('scl:msg').$api->errorMessage."\n";
	}

	return $retval;	
}	
?>
