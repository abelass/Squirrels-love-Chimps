<?php

if (!defined("_ECRIRE_INC_VERSION")) return;

/* filtre pour l'inétégration Api Mai!lchimp
 * lists(string apikey, array filters, int start, int limit)
 * http://apidocs.mailchimp.com/api/1.3/lists.func.php
 */
function recuperer_listes($apiKey,$filters='',$start='',$limit='100'){
	
	# API mailchimp
	include_spip('inc/1.3/MCAPI.class');

	//on verifie que les parametres du plugin mailchimp sont initialisées
	if ($apiKey){
		spip_log(__LINE__,'squirrel_chimp');
		spip_log($apiKey);

		// initialisation d'un objet mailchimp
		$api = new MCAPI($apiKey);
		
		//récuperation des listes
		
		$retval = $api->lists($filters,$start,$limit);
		
		$return=array();

		if ($api->errorCode){
			$return['error'] .= "Unable to load lists()!";
			$return['error'] .= "\n\tCode=".$api->errorCode;
			$return['error'] .=  "\n\tMsg=".$api->errorMessage."\n";
		} else {
			$return['data'] = $retval['data'];
			}
		}
		 
		
	return $return;		
}

//Retourne un array  pour filtrer les lists et utilisé par lists() de MailChimp
function array_filtre_lists($mailinglists){

	if(is_array($mailinglists)){
		if(count($mailinglists)>0)$lists=implode(array_keys($mailinglists),',');
	}
	else $lists=$mailinglists;
	return array('list_id'=>$lists);
	}

// filtre pour obtenir les champs spip à diposition 
function champs_spip($tables){
	
	$champs_refuses=array(
		"id_auteur","email","nom_site","nom_site","url_site","login","pass","low_sec","statut","maj","pgp","htpass","en_ligne","imessage","messagerie","alea_actuel","alea_futur","prefs","cookie_oubli","source","extra","webmestre"
		);
	
	$trouver_table = charger_fonction('trouver_table','base');
	$champs=array();
	if(!is_array($tables)){
		$c=$trouver_table($tables);
		$champs= array_keys($c['field']);
	}
	else{
		foreach($tables AS $table){
			$c=$trouver_table($table);	
			$champs=array_merge($champs,array_keys($c['field']));
			}
		}
	
	return array_diff($champs,$champs_refuses);
	}

// filtre pour obtenir un array de corresponance entre champs spip ,et champs MailChimp
function champs_listes($apiKey,$listId,$tables='spip_auteurs'){
	
	$mapping= array();
	
	// initialisation d'un objet mailchimp
	$api = new MCAPI($apiKey);
 
	if(is_array($listId)){
		foreach($listId AS $id){
			$champs=$api->listMergeVars($id);
			$mapping['mailchimp'] = $api->listMergeVars($id);
			}
		}
		else $mapping['mailchimp'] = $api->listMergeVars($listId);
	
	$mapping['spip'] = champs_spip($tables);
	
	return $mapping;
	
}


// Prépare les données pour la synchronisation
function donnees_sync($from='spip_auteurs',$where='',$groupby='',$orderby='',$limit='',$having='',$serveur='',$option=true){
	
	// Les champs spip à traiter
	$champs_sync=champs_pour_concordance();
	
	// les utilisateurs spip
	$sql=sql_select(array_keys($champs_sync),$from,$where,$groupby,$orderby,$limit,$having,$serveur,$option);
	
	// Préparation de l'array a envoyer à mailchimp
	$batch=array();
	$i=0;
	while($data=sql_fetch($sql)){
		$i++;
		foreach($data AS $key=>$value){
			if(array_key_exists($key,$champs_sync))$batch[$i][$champs_sync[$key]]=$value;
			}
		}
	return $batch;
}

// Détermine les champs Spip à prendre en compte pour la concordance 
function champs_pour_concordance(){
	$concordances=lire_config('squirrel_chimp/mapping');
	$concordances_fixes=array('email'=>'EMAIL');
	$champs_sync=array_merge($concordances,$concordances_fixes);

	return $champs_sync;
}


/*Inscription de la liste 
 * listSubscribe(string apikey, string id, string email_address, array merge_vars, string email_type, bool double_optin, bool update_existing, bool replace_interests, bool send_welcome)
 * API Mailchimp http://apidocs.mailchimp.com/api/rtfm/listsubscribe.func.php
 */
 
function inscription_liste_mc($flux,$api,$listId,$email,$donnees_auteur,$email_type='',$optin,$new){
	$retval = $api->listSubscribe($listId, $email,$donnees_auteur,$email_type,$optin,$new);

	include_spip('inc/autoriser');
	
	if ($api->errorCode){
		spip_log(__LINE__,'squirrel_chimp');
		$messageErreur = _T('scl:api_errorcode')."<br/><b>".$api->errorCode."</b><br/>".$api->errorMessage;
		if (autoriser('defaut')){
			spip_log(__LINE__,'squirrel_chimp');
			$flux['data'] = array('message_erreur' => "Plugin Squirrels Love Chimps $messageErreur");
			spip_log("Admin $messageErreur",'squirrel_chimp');
			return $flux;
		} // fin message pour admin
		else {
			spip_log(__LINE__,'squirrel_chimp');
			// que le spiplog si on est juste un user
			spip_log("$messageErreur",'squirrel_chimp');
			return $flux;
		} // autoriser
	} else {
		spip_log(__LINE__,'squirrel_chimp');
		$message_ok .="<br/><br/>"._T('scl:demande_inscription_envoyee_ok', array('email' => "$email"));
		if($optin){
			$message_ok .="<br/>"._T('scl:demande_inscription_envoyee1', array('email' => "$email"));
			$message_ok .="<br/>"._T('scl:demande_inscription_envoyee2');
			$message_ok .="<br/><i>"._T('scl:demande_inscription_envoyee3')."</i>";
			}
		$flux['data']['message_ok']=$message_ok ;
		return $flux;
}
	
}

// Désinscription de la liste
function desinscription_liste_mc($flux,$api,$listId,$email){
	spip_log(__LINE__,'squirrel_chimp');
	$retval = $api->listUnSubscribe($listId, $email);

	if ($api->errorCode){
		spip_log(__LINE__,'squirrel_chimp');
		$messageErreur = _T('scl:api_errorcode')."<br/><b>".$api->errorCode."</b><br/>".$api->errorMessage;
		if (autoriser('defaut')){
			spip_log(__LINE__,'squirrel_chimp');
			$flux['data'] = array('message_erreur' => "Plugin mes_abonnes : $message_ok <br/> Plugin Mailchimp: $messageErreur");
			spip_log("Admin $messageErreur",'squirrel_chimp');
			return $flux;
		} // fin message pour admin
		else {
			spip_log(__LINE__,'squirrel_chimp');
			// que le spiplog si on est juste un user
			spip_log(" $messageErreur",'squirrel_chimp');
			return $flux;
		} // autoriser
	} else {
		spip_log(__LINE__,'squirrel_chimp');
		$message_ok .="<br>"._T('mailchimp:demande_desincription_ok', array('email' => "$email"));
		$flux['data']['message_ok']=$message_ok ;
		return $flux;
	}
	
}

?>
