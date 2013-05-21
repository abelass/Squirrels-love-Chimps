<?php

// Securité
if (!defined("_ECRIRE_INC_VERSION")) return;



 function formulaires_creer_liste_charger_dist($listes_mailchimp=''){
	 
	 if(!$listes_mailchimp){
	 
	# API mailchimp
	include_spip('inc/1.3/MCAPI.class');
	
	#pour ecrire_config 
	include_spip('inc/config');
	
	#les fonctions de squirrel_chimp_lists
	include_spip('squirrel_chimp_lists_fonction');
		
		$apikey=lire_config('squirrel_chimp/apiKey');
		
		$listes=recuperer_listes($apikey);
		
		
	if($listes['error']){
		$erreur=$listes['error'];
		$listes_mailchimp=array();
		}
	else $listes_mailchimp=$listes['data'];
	
	}
	
	$valeurs = array(
		'titre'=>'',
		'lang'=>'',
		'errors'=>$erreur,		
		'listes_mailchimp'=>$listes_mailchimp,
		'liste_mailchimp'=>'',
		);	
	

	
	return $valeurs;
}


function formulaires_creer_liste_verifier_dist($listes_mailchimp=''){
	$erreurs = array();
	

	// verifier que les champs obligatoires sont bien la :

    foreach(array('titre','lang') as $champ) {
        	if (!_request($champ)) {
            		$erreurs [$champ] = _T('spip:info_obligatoire');
        		}
    	}
	return $erreurs;
}

 

function formulaires_creer_liste_traiter_dist($listes_mailchimp=''){
	
	$valeurs = array(
		'titre'=>_request('titre'),
		'lang'=>_request('titre'),	
		'listes_mailchimp'=>$listes_mailchimp,
		'liste_mailchimp'=>'',
		);	

	$valeurs['objets']=$objets;	
	
	$objet_courant=$objets[$part];
	
	$objet_courant_champ=$objet_courant['config'];
	
	
	//Ecriture des parametres dans META 

	if(!is_array($objet_courant_champ)){
		ecrire_config("squirrel_chimp/$objet_courant_champ",_request($objet_courant_champ));
		if(_request('apiKey'))$log .= $objet_courant_champ.'='._request($objet_courant_champ)."\n";
		}
	else{
		foreach($objet_courant_champ AS $objet2){
			ecrire_config("squirrel_chimp/$objet2", _request($objet2) );
			if(_request($objet2))$log .= $objet2.'='._request($objet2)."\n";
			}
		
		}

	spip_log ("Plugin mailchimp/ sauvegarde de la configuartion meta: $log. $objet_courant_champ",'squirrel_chimp');


	#Retour succes 
	
	$valeurs['message_ok'] = _T('squirrelchimp:retour_test_api');


	return $valeurs;
}

?>
