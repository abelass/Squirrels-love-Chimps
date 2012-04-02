<?php

// Securité
if (!defined("_ECRIRE_INC_VERSION")) return;

# API mailchimp
include_spip('inc/1.3/MCAPI.class');

#pour ecrire_config 
include_spip('inc/config');

include_spip('squirrel_chimp_lists_fonctions');

// Définit le fuseau horaire par défaut à utiliser, GMT est le fuseau utilisé par mailchimp.
date_default_timezone_set('GMT');


 function formulaires_squirrel_chimp_abonnement_liste_plus_charger_dist($listes=''){

	$donnees_personnelles=lire_config('squirrel_chimp/mapping');

	$champs=array();
	foreach($donnees_personnelles as $id_liste=>$donnees){
		foreach($donnees as $champs_spip=>$champ_mc){
			if($champ_mc)$champs[$champs_spip]=$champ_mc;
			}
		}

	// filtre les mailinglistes
	
	$filters=$listes?$listes:lire_config('squirrel_chimp/mailinglists');

	$filters=array_filtre_lists($filters);
	
	// Eviter des erreurs sur le formulaire si le plugin n'est pas configuré

	$valeurs = array(
		'champs'=>'',
		'email'=>'',
		'email2'=>'',
		'mailinglists'=>'',
		'donnees_personnelles'=>$champs,
		'filters'=>$filters,
		);

	if(is_array($champs)){
		foreach($champs AS $key=>$value){
			$valeurs[$key]='';
			}	
		}
		
			
	if(is_array($champs))$valeurs['_hidden'].='<input type="hidden" name="champs" value="'.implode(',',array_flip($champs)).'">';
	$valeurs['_hidden'].='<input type="hidden" name="email_type" value="html">';	
	return $valeurs;
}
 
 
function formulaires_squirrel_chimp_abonnement_liste_plus_verifier_dist($listes='')
{
	$valeurs = array();
	$email=_request('email');
	$email2=_request('email2');
	$listes=_request('mailinglists');
	$email_type=_request('email_type');
	$date=date('Y-m-d');
	$lang=_request('lang');
	
	// teste basique sur le mail, le reste c'est mailchimp qui s'en charge
	foreach(array('email','email2','mailinglists') as $obligatoire)
			if (!_request($obligatoire)) $valeurs[$obligatoire] = _T('spip:info_obligatoire');	

	if($email AND $email!=$email2)$valeurs['email2'] = _T('scl:email2_identique');
			

	// Les configurations
	$donnees_personnelles=lire_config('squirrel_chimp/mapping');
	$apiKey = lire_config("squirrel_chimp/apiKey");
	$optin = lire_config('squirrel_chimp/ml_opt_in')?false:true; //yes, send optin emails
	
	// Composer l'array des donnes pour mailchimp
	$donnees_auteur=array();
		
	$valeurs_mc=array();
	
	foreach($donnees_personnelles as $id_liste=>$donnees){
		foreach($donnees as $champs_spip=>$champ_mc){
			$valeurs_mc[$champ_mc]=_request($champs_spip);
			$valeurs_spip[$champs_spip]=$valeurs_mc[$champ_mc];
			}
		}
	
		
	if ($apiKey){
		# API mailchimp
		include_spip('inc/1.3/MCAPI.class');

		// initialisation d'un objet mailchimp
		$api = new MCAPI($apiKey);

    if (count($valeurs)) {
		$valeurs['message_erreur'] = _T('spip:avis_erreur');
	    }	

		// Inscription dans mailchimp
		if ($email AND is_array($listes)){
			
			$id_auteur=sql_getfetsel('id_auteur','spip_auteurs','email='.sql_quote($email));

			// By default this sends a confirmation email - you will not see new members
			// until the link contained in it is clicked!
			// listSubscribe(string apikey, string id, string email_address, array merge_vars, string email_type, bool double_optin, bool update_existing, bool replace_interests, bool send_welcome)
			
			foreach($listes AS $id_spip => $id_mailchimp){
				$valeurs=inscription_liste_mc($valeurs,$api,$id_mailchimp,$email,$valeurs_mc,$email_type,$optin,true);
				$valeurs=$valeurs['data'];
				
				if(!$valeurs['message_erreur']){
						$statut=sql_getfetsel('statut','spip_auteurs_listes','id_liste='.sql_quote($id_spip).' AND id_auteur='.$id_auteur);
					if($id_auteur){
						if($statut=="a_valider")sql_updateq('spip_auteurs_listes',array('statut'=>'valide','maj'=>$date),'id_liste='.$id_spip.' AND id_auteur='.$id_auteur);
						elseif(!$statut){
													
						$valeurs=array(
								'id_auteur'=>$id_auteur,
								'id_liste'=>$id_spip,					
								'statut'=>'valide',
								'maj'=>$date,
								'date_inscription'=>$date,
								'date_syncro'=>$date,
								'format'=>$email_type,												
								);	
							
							
							sql_insertq('spip_auteurs_listes',$valeurs);
						}
						}
					else{
						$champs=donnees_sync_simple($id_liste,$valeurs_mc,'mc');
						
						$lang=$lang?$lang:lire_config('langue_site');
						
						$champs_additionnels=array(
							'email'=>$email,
							'statut'=>'6forum',
							'maj'=>$date,
							'date_syncro'=>$date,
							'format'=>$email_type,
							'lang'=>$lang,
							);
		
						$champs_sync=$champs[1];
						if(!$champs_sync['nom']){
							$explode=explode('@',$email);
							$champs_sync['nom']=$explode[0];
							}
															
						//On actualise la bd
						$champs=array_merge($champs_sync,$champs_additionnels);	
						$id_auteur=sql_insertq('spip_auteurs',$champs);
						
						$valeurs=array(
								'id_auteur'=>$id_auteur,
								'id_liste'=>$id_spip,					
								'statut'=>'valide',
								'maj'=>$date,
								'date_inscription'=>$date,
								'date_syncro'=>$date,
								'format'=>$email_type,												
								);						
						sql_insertq('spip_auteurs_listes',$valeurs);
						$id_auteur='';							
						}
					}
				
				
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
			spip_log("Admin"._T('sclp:config_erreur'));
			return $valeurs;
		}
		else {
			spip_log(__LINE__);
			// que le spiplog si on est juste un user
			spip_log(_T('sclp:config_erreur'));
			return $valeurs;
		} // autoriser

		spip_log(__LINE__);
	} // if ( $apiKey and $listId )	{		
	
			
	
	$valeurs['editable']=false;

	return $valeurs;
}

// pas vraiment utilisé, la vérification clôture le tout

function formulaires_squirrel_chimp_abonnement_liste_traiter_plus_dist($listes=''){
	
	$valeurs = array();

	#Retour succes 
	
	$valeurs['message_ok'] = _T('scl:abbonnement_ok');


	return $valeurs;
}

?>
