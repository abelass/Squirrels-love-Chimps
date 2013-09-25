<?php

// Securité
if (!defined("_ECRIRE_INC_VERSION")) return;

# API mailchimp
include_spip('inc/1.3/MCAPI.class');

#pour ecrire_config 
include_spip('inc/config');




 function formulaires_squirrel_chimp_abonnement_liste_charger_dist($listes=''){

	$donnees_personnelles=lire_config('squirrel_chimp/mapping');
	
	// filtre les mailinglistes
	
	$filters=$listes?$listes:lire_config('squirrel_chimp/mailinglists');

	$filters=array_filtre_lists($filters);
    
	// Eviter des erreurs sur le formulaire si le plugin n'est pas configuré

	$valeurs = array(
		'email'=>'',
		'email2'=>'',
		'mailinglists'=>'',
		'donnees_personnelles'=>$donnees_personnelles,
		'filters'=>$filters,
		);

	if(is_array($donnees_personnelles)){
		foreach($donnees_personnelles AS $value){
			$valeurs[$value]='';
			}	
		}
			
	
	return $valeurs;
}
 
 
function formulaires_squirrel_chimp_abonnement_liste_verifier_dist($listes='')
{
    include_spip('inc/config');
    $config=lire_config('squirrel_chimp/',array());
	$valeurs = array();
	$email=_request('email');
	$email2=_request('email2');
	$listes=_request('mailinglists');
	
	// teste basique sur le mail, le reste c'est mailchimp qui s'en charge
	foreach(array('email','email2','mailinglists') as $obligatoire)
			if (!_request($obligatoire)) $valeurs[$obligatoire] = _T('spip:info_obligatoire');	

	if($email AND $email!=$email2)$valeurs['email2'] = _T('scl:email2_identique');
			

	// Les configurations
	$donnees_personnelles=$config['mapping'];
	$apiKey = $config['apiKey'];
	$optin = $config['ml_opt_in']?false:true; //yes, send optin emails
	
	// Composer l'array des donnes pour mailchimp
	
	$donnees_auteur=array('email'=>_request('email'));
    if(is_array($donnees_personnelles)){    
    	foreach($donnees_personnelles AS $value){
    		$donnees_auteur[$value]=_request($value);
    		}
        }
		
	if ($apiKey){
		# API mailchimp
		include_spip('inc/1.3/MCAPI.class');
		
		// Les Fonctions
		include_spip('squirrel_chimp_lists_fonctions');
        
		spip_log($apiKey,'squirrel_chimp_lists');

		// initialisation d'un objet mailchimp
		$api = new MCAPI($apiKey);

    if (count($valeurs)) {
		$valeurs['message_erreur'] = _T('spip:avis_erreur');
	    }	

		// Inscription dans mailchimp
		if ($email AND $listes){
			spip_log(__LINE__,'squirrel_chimp_lists');
			// By default this sends a confirmation email - you will not see new members
			// until the link contained in it is clicked!
			// listSubscribe(string apikey, string id, string email_address, array merge_vars, string email_type, bool double_optin, bool update_existing, bool replace_interests, bool send_welcome)
			
			foreach($listes AS $listId){
			    spip_log('id:'.$listId,'squirrel_chimplists');
				$valeurs=inscription_liste_mc($valeurs,$api,$listId,$email,$donnees_auteur,$email_type,$optin,true);
				$valeurs=$valeurs['data'];
			}

		} // $statut=='subscribe'
	} //($apiKey and $listId)
	else {
		// n'effrayons pas l utilisateur classique
		spip_log(__LINE__);
		if (autoriser("configurer", "mailchimp")){
			spip_log(__LINE__);
			//erreur il faut configurer le plugin mailchimp
			$valeurs = array('message_erreur' => _T('mailchimp:config_erreur'));
			spip_log("Admin"._T('scl:config_erreur'));
			return $valeurs;
		}
		else {
			spip_log(__LINE__);
			// que le spiplog si on est juste un user
			spip_log(_T('scl:config_erreur'));
			return $valeurs;
		} // autoriser

		spip_log(__LINE__);
	} // if ( $apiKey and $listId )	{		
	
			
	
	$valeurs['editable']=false;

	return $valeurs;
}

// pas vraiment utilisé, la vérification clôture le tout

function formulaires_squirrel_chimp_abonnement_liste_traiter_dist($listes=''){
	
	$valeurs = array();

	#Retour succes 
	
	$valeurs['message_ok'] = _T('scl:abbonnement_ok');


	return $valeurs;
}

?>
