<?php

if (!defined("_ECRIRE_INC_VERSION")) return;



//Retourne un array  pour filtrer les lists spip et utilisé par lists() de MailChimp
function array_filtre_lists($mailinglists){

	if(is_array($mailinglists)){
		if(count($mailinglists)>1)$lists=$mailinglists;
        else {$lists=implode(',',$mailinglists); echo 1;}
	}
	else $lists=explode(',',$mailinglists);
	return array('list_id'=>$lists);
	}

function tables_dispos(){
	include_spip('inc/config');
	
	// On cherche les tables à prendre en compte

	$t=explode(',',lire_config('squirrel_chimp/tables'));
	
	$tables=array();
	if($t){
		foreach($t as $table){
			if($table)$tables[]=$table;
			}
		}
	if(!$tables)$tables=array('spip_auteurs');	
	
	return $tables;
	}	

// filtre pour obtenir les champs spip à disposition 
function champs_spip(){
	
	include_spip('inc/config');	
	$tables=tables_dispos();
	$champs_extras=lire_config('squirrel_chimp/champs');
	$trouver_table = charger_fonction('trouver_table','base');
	$champs=array();
	spip_log($tables,'squirrel_chimp_lists');
	foreach($tables AS $key=>$table){
		if($table){spip_log(1,'squirrel_chimp_lists');
			$c=$trouver_table($table);
		spip_log($c,'squirrel_chimp_lists');
			if(is_array($c['field']))$champs['tout'][$table]=array_keys($c['field']);
			if($extras=$champs_extras[$table]){
				$champs[$table]=$extras;
				}
			else{	
				$table=$table?$table:0;
				if(is_array($c['field']) AND count($c['field'])>0){
					$c=array_keys($c['field']);
					$champs[$table]=$c;	
					}	
				}
			$c='';
			}
		}
	return $champs;
	}

// filtre pour obtenir un array de corresponance entre champs spip ,et champs MailChimp
function champs_listes($apiKey,$listId,$multi=''){
	
	$mapping= array();

	// initialisation d'un objet mailchimp
	$api = new MCAPI($apiKey);

	if(is_array($listId)){
		foreach($listId AS $id){
			$champs=$api->listMergeVars($id);

			if($multi) $mapping['mailchimp'][$id] = $champs;
			else $mapping['mailchimp'] = $champs;
			}
		}
		else {
			if($multi) $mapping['mailchimp'][$listId]  = $api->listMergeVars($listId);
			else $mapping['mailchimp']  = $api->listMergeVars($listId);	
			}
	
	$mapping['spip'] = champs_spip();
	
	return $mapping;
	
}

// filtre pour obtenir le champs MailChimp
function champs_liste($apiKey='',$listId,$multi=''){
	include_spip('inc/config');	
	// initialisation d'un objet mailchimp	
	if(!$api){
		$apikey=lire_config('squirrel_chimp/apiKey');
		$api = new MCAPI($apikey);
		}

	if(!is_array($listId)){
		  $champs= $api->listMergeVars($listId);
		}

	
	return$champs;
	
}

// cherche les champs d'une table
/*
function champs_table($tables=''){


	$tables=tables_dispos($tables);

	$champs_dispos=lire_config('squirrel_chimp/champs');
	echo serialize($champs);
	$champs=array();	
	if(is_array($tables)){
			foreach($tables AS $table){
			if($table)$champs[$table]=$champs_dispos[$table];
			}
		}
	echo serialize($champs);
	return $champs;
	
	}*/


// Prépare les données pour la synchronisation
function donnees_sync($id_liste_spip='',$table='',$identifiant='',$where_add=''){
	
	//Les données de la configuration
	include_spip('inc/config');

	$tables=tables_dispos();
	$champs_extras=lire_config('squirrel_chimp/champs');


	//Préparation de la requette
	$identifiant_defaut='id_auteur';
	
	$from=implode(',',$tables);	

	$where_principal=$identifiant_principal.'='.$identifiant;
	$where_secondaire=array();	
	$champs=array();
	$i=0;
	foreach($tables AS $table){
		$i++;
		if($i==1)$table_principale=$table;
		else $where_secondaire[$i]=$table_principale.'.'.$identifiant_defaut.'='.$table.'.'.$identifiant_defaut;
		if($champs_extras[$table]){
			foreach($champs_extras[$table] as $champ){
				$champs[$champ]=$table.'.'.$champ;
				}
			}
		}
			
	if(!$champs)$champs='*';		
	else $champs['email']='spip_auteurs.email';	
	
	$identifiant_joints=implode(' AND ',$where_secondaire);
	if($identifiant)$identifiant_principal=$table_principale.'.'.$identifiant_defaut.'='.$identifiant;
	$w=array($identifiant_joints,$identifiant_principal,$where_add);
	$where_1=array();
	foreach($w AS $data)	{
		if($data)$where_1[]=$data;
		}
	
	if (is_array($where))$where=implode(' AND ',$where_1);
	
	
	$$champs=$champs?implode(',',$champs):'*';


	// La concordance entre les champs
	$champs_sync=champs_pour_concordance($id_liste_spip);

	
	// les utilisateurs spip
	$sql=sql_select($champs,$from,$where,$groupby,$orderby,$limit);
	
	// Préparation de l'array a envoyer à mailchimp
	$batch=array();
	$i=0;
	while($data=sql_fetch($sql)){
		$i++;
		foreach($data AS $key=>$value){
			if(!is_array($champs_sync[$key])){
				if(array_key_exists($key,$champs_sync))$batch[$i][$champs_sync[$key]]=$value;
				}
			}
		}

	return $batch;
}

// Détermine les champs Spip à prendre en compte pour la concordance 
function champs_pour_concordance($id_liste=''){
	include_spip('inc/config');	
	
	$concordances=lire_config('squirrel_chimp/mapping');
	
	if($id_liste)$concordances=$concordances[$id_liste];
	if(!$concordances)$concordances=array();

	$concordances_fixes=array('email'=>'EMAIL');
	$champs_sync=array_merge($concordances_fixes,$concordances);

	return $champs_sync;
}

/* filtre pour l'intégration Api Mailchimp
 * lists(string apikey, array filters, int start, int limit)
 * http://apidocs.mailchimp.com/api/1.3/lists.func.php
 */
function recuperer_listes($apiKey,$filters='',$start='0',$limit='100'){
	
	# API mailchimp
	include_spip('inc/1.3/MCAPI.class');

	//on verifie que les parametres du plugin mailchimp sont initialisées
	if ($apiKey){
		spip_log(__LINE__,'squirrel_chimp');
		spip_log($apiKey);

		// initialisation d'un objet mailchimp
		$api = new MCAPI($apiKey);
		
		//récuperation des listes
		
		$retval = $api->lists($filters);
		
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


/*Inscription de la liste 
 * listSubscribe(string apikey, string id, string email_address, array merge_vars, string email_type, bool double_optin, bool update_existing, bool replace_interests, bool send_welcome)
 * API Mailchimp http://apidocs.mailchimp.com/api/rtfm/listsubscribe.func.php
 */
 
function inscription_liste_mc($flux='',$api,$listId,$email,$donnees_auteur,$email_type='',$optin,$update_existing=true,$replace_interests=false,$send_welcome=false){
	
	$retval = $api->listSubscribe($listId, $email,$donnees_auteur,$email_type,$optin,$update_existing,$replace_interests,$send_welcome);

	
	if ($api->errorCode){
		spip_log('inscription_liste','squirrel_chimp_lists');
		$messageErreur = _T('scl:api_errorcode')."<br/><b>".$api->errorCode."</b><br/>".$api->errorMessage;
		if (autoriser('defaut')){

			$flux['data'] = array('message_erreur' => "Plugin Squirrels Love Chimps $messageErreur");
			spip_log("Admin $messageErreur",'squirrel_chimp_lists');
			return $flux;
		} // fin message pour admin
		else {
			spip_log(__LINE__,'squirrel_chimp_lists');
			// que le spiplog si on est juste un user
			spip_log("$messageErreur",'squirrel_chimp_lists');
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

}
			return $flux;
}

/*Désinscription de la liste
 * listUnsubscribe(string apikey, string id, string email_address, boolean delete_member, boolean send_goodbye, boolean send_notify)
 * http://apidocs.mailchimp.com/api/1.3/listunsubscribe.func.php
 */
function desinscription_liste_mc($flux='',$api,$listId,$email,$delete_member=false,$send_goodbye=false,$send_notify=true){
	spip_log(__LINE__,'squirrel_chimp');
	$retval = $api->listUnSubscribe($listId, $email,$delete_member,$send_goodbye,$send_notify);

	if ($api->errorCode){

		$messageErreur = _T('scl:api_errorcode')."<br/><b>".$api->errorCode."</b><br/>".$api->errorMessage;
		if (autoriser('defaut')){

			$flux['data'] = array('message_erreur' => "Plugin mes_abonnes : $message_ok <br/> Plugin Mailchimp: $messageErreur");
			spip_log("Admin $messageErreur",'squirrel_chimp_lists');
			return $flux;
		} // fin message pour admin
		else {

			// que le spiplog si on est juste un user
			spip_log(" $messageErreur",'squirrel_chimp_lists');
			return $flux;
		} // autoriser
	} else {
		$message_ok .="<br>"._T('mailchimp:demande_desincription_ok', array('email' => "$email"));
		$flux['data']['message_ok']=$message_ok ;
		return $flux;
	}
	
}

?>
