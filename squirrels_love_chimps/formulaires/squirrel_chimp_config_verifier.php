<?php

function formulaires_squirrel_chimp_config_verifier_dist($valeurs){
	
	#recuperation de la config
	$apiKey = _request("apiKey");


	spip_log("Plugin mailchimp form vérifier : $apiKey ",'squirrel_chimp') ;

	// initialisation d'un objet mailchimp
	$api = new MCAPI($apiKey);
	
	$result = $api->ping();
	
	//$result = $api->ping();
	//$result = $api->listMembers($listId, 'subscribed', null, 0, 5);

	// L'api a retourné une erreur 
	if ($api->errorCode)
	{ echo 
		$valeurs = array('message_erreur' => _T('squirrelchimp:configurer_erreur_api')."<br/>"._T('mailchimp:api_errorcode')."<br/><b>".$api->errorCode."</b><br/><b>".$api->errorMessage ."</b>");
	}
	elseif ($result != "Everything's Chimpy!"){
	    	$valeurs = array('message_erreur' => _T('squirrelchimp:configurer_erreur_api'));
	}
	
return $valeurs;
}

?>
