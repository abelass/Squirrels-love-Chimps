<?php
if (!defined("_ECRIRE_INC_VERSION")) return;

function syncro_spip_mc($liste_spip,$liste_mc,$derniere_syncro){
	spip_log('syncro_spip_mc','sclp');
		$donnes_liste_spip=array();

	while($data=sql_fetch($liste_spip)){
		
		$donnes_liste_spip[$data['email']]=$data;

		}
	spip_log($donnes_liste_spip,'sclp');
	}


function syncro_mc_spip($liste_spip,$liste_mc,$derniere_syncro){
	$donnes_liste_spip=array();

	while($data=sql_fetch($liste_spip)){
		
		$donnes_liste_spip[$data['email']]=$data;

		}

	//spip_log($donnes_liste_spip,'sclp');
	foreach($liste_mc['data'] AS $membre){



		}
	}
	
// Prépare les données pour la synchronisation
function donnees_sync_simple($id_liste,$donnees,$base='spip'){

	// Les champs spip à traiter
	$champs_sync=champs_pour_concordance($id_liste);

	if($base!='spip')$champs_sync=array_flip($champs_sync[0]);

	//spip_log($donnees ,'sclp');
	// Préparation de l'array a envoyer à mailchimp
	$batch=array();
	$i=0;
	foreach($donnees AS $key=>$data){
		//spip_log($key ,'sclp');	
		//spip_log($data ,'sclp');			
		if(!is_array($data))$data =$donnees;
		if($base!='spip')$data=$data['merges'];
		$i++;
		foreach($data AS $key=>$value){
			if(!is_array($champs_sync[$key])){
				if(array_key_exists($key,$champs_sync))$batch[$i][$champs_sync[$key]]=$value;
				}
			}
		}

	return $batch;
}


// Désinscription en masse des abonnées
function desinscription_batch_spip($id_liste_spip,$desabonnements){
	
	foreach($desabonnements as $id_auteur=>$timestamp){
		$val=array('statut'=>'a_valider','maj'=>$timestamp,'date_syncro'=>$timestamp);
		sql_updateq('spip_auteurs_listes',$val,'id_liste='.$id_liste_spip.' AND id_auteur='.$id_auteur);
		}
	return;
	}
	
// Inscription en masse dans spip
function inscription_batch_spip($id_liste_spip,$abonnements){
	//pour ecrire_config 
	include_spip('inc/config');
	$malinglists=lire_config('squirrel_chimp/mailinglists');
	$id_liste_mc=$malinglists[$id_liste_spip];

	foreach($abonnements as $id_auteur=>$donnees){
		
		$update='';
		if(intval($id_auteur)){		
			$val=array('statut'=>'valide','maj'=>$donnees['timestamp'],'date_syncro'=>$donnees['timestamp']);
			$update=sql_updateq('spip_auteurs_listes',$val,'id_liste='.$id_liste_spip.' AND id_auteur='.$id_auteur);
			}
		else{

			// l'auteur existe
			if($id_auteur){

				//On actualise si il a déjà été insscrit à la mailinglist
				$test=sql_getfetsel('maj','spip_auteurs_listes','id_liste='.$id_liste_spip.' AND id_auteur='.$id_auteur);
				spip_log($test, 'sclp');
				$val=array('statut'=>'valide','maj'=>$donnees['timestamp'],'date_syncro'=>$donnees['timestamp']);
				if($test){
					sql_updateq('spip_auteurs_listes',$val,'id_liste='.$id_liste_spip.' AND id_auteur='.$id_auteur);
					}
				//Sinon on le rajoute dans les listes
				else{
					spip_log($id_liste_spip, 'sclp');
					$val['id_liste']=$id_liste_spip;
					$val['id_auteur']=$id_auteur;
					$val['date_inscription']=$donnees['timestamp'];
					sql_insertq('spip_auteurs_listes',$val);
					}
				}
			else{

				// On cherche les infos du membre mailchimp
				$member_info=membres_liste_info_mc($api,$id_liste_mc,$donnees['email']);
				
				// On cherche la correpsondance des champs
				$champs=donnees_sync_simple($id_liste,$member_info['data'],'mc');

				
				$lang=$member_info['data'][0]['language']?$member_info['data'][0]['language']:lire_config('langue_site');
				$champs_additionnels=array(
					'email'=>$donnees['email'],
					'statut'=>'6forum',
					'maj'=>$donnees['timestamp'],
					'date_syncro'=>$donnees['timestamp'],
					'format'=>$member_info['data'][0]['email_type'],
					'lang'=>$lang,
					'id_mailchimp'=>$member_info['data'][0]['id'],
					);

				$champs_sync=$champs[1];
				if(!$champs_sync['nom']){
					$explode=explode('@',$donnees['email']);
					$champs_sync['nom']=$explode[0];
					}
													
				//On actualise la bd
				$champs=array_merge($champs_sync,$champs_additionnels);	
				$id_auteur=sql_insertq('spip_auteurs',$champs);
				
				$valeurs=array(
						'id_auteur'=>$id_auteur,
						'id_liste'=>$id_liste_spip,
						'id_mailchimp'=>$member_info['data'][0]['id'],						
						'statut'=>'valide',
						'maj'=>$donnees['timestamp'],
						'date_inscription'=>$donnees['timestamp'],
						'date_syncro'=>$donnees['timestamp'],
						'format'=>$member_info['data'][0]['email_type'],												
						);						
				sql_insertq('spip_auteurs_listes',$valeurs);
				$id_auteur='';	

				}
			}
		}
	return;
	}
	

/* Récuperer des infos des membres d'une liste MailChimp
 *  listMembers(string apikey, string id, string status, string since, int start, int limit)
 * http://apidocs.mailchimp.com/api/rtfm/listmembers.func.php
 */
 
function membres_liste_mc($api='',$id_liste_mailchimp,$status='subscribed',$since='',$start=0,$limit=15000){
	
	//pour ecrire_config 
	include_spip('inc/config');
	
	//L'api de mailchimps
	include_spip('inc/1.3/MCAPI.class');
	
	// initialisation d'un objet mailchimp	
	if(!$api){
		$apikey=lire_config('squirrel_chimp/apiKey');
		$api = new MCAPI($apikey);
		}
	
	$retval = $api->listMembers($id_liste_mailchimp,$status,$since,$start,$limit);
	
	spip_log($retval, 'sclp');	
	return $retval;
	
}

/* Les informations d'un abonné MailChimp
 * listMemberInfo(string apikey, string id, array email_address)
 * http://apidocs.mailchimp.com/api/1.3/listmemberinfo.func.php
 */
 
function membres_liste_info_mc($api='',$id_liste,$email){

	//pour ecrire_config 
	include_spip('inc/config');
	
	//L'api de mailchimps
	include_spip('inc/1.3/MCAPI.class');
	
	if(!$api){
		$apikey=lire_config('squirrel_chimp/apiKey');
		$api = new MCAPI($apikey);		
		}

	$retval = $api->listMemberInfo($id_liste,$email);
	
	if ($api->errorCode){

	echo "Unable to load listMemberInfo()!\n";

	echo "\tCode=".$api->errorCode."\n";

	echo "\tMsg=".$api->errorMessage."\n";

}

	return $retval;
	
}


/* Les informations d'un abonné MailChimp
 * listBatchUnsubscribe(string apikey, string id, array emails, boolean delete_member, boolean send_goodbye, boolean send_notify)
 * http://apidocs.mailchimp.com/api/rtfm/listbatchunsubscribe.func.php
 */
 
function desinscription_batch_mc($api='',$id_liste,$email,$delete_member=false,$send_goodby=false,$send_notify=false){
	//pour ecrire_config 
	include_spip('inc/config');
	
	//L'api de mailchimps
	include_spip('inc/1.3/MCAPI.class');
	
	if(!$api){
		$apikey=lire_config('squirrel_chimp/apiKey');
		$api = new MCAPI($apikey);		
		}

	$retval = $api->listBatchUnsubscribe($api,$id_liste,$email,$delete_member,$send_goodby,$send_notify);

//spip_log($retval, 'sclp');

	return $retval;
	
}
/* Les informations d'un abonné MailChimp
 * listBatchSubscribe(string apikey, string id, array batch, boolean double_optin, boolean update_existing, boolean replace_interests)
 * http://apidocs.mailchimp.com/api/rtfm/listbatchsubscribe.func.php
 */
 
function inscription_batch_mc($api,$id_liste,$batch,$optin,$update=true,$replace_interests=false){
	//pour ecrire_config 
	include_spip('inc/config');
	
	//L'api de mailchimps
	include_spip('inc/1.3/MCAPI.class');
	
	if(!$api){

		$apikey=lire_config('squirrel_chimp/apiKey');
		$api = new MCAPI($apikey);		
		}
	
	$retval = $api->listBatchSubscribe($id_liste,$batch,$optin,$update,$replace_interests);
	spip_log($retval, 'sclp');

	return $retval;
	
}

function inc_editer_auteur_traiter_listes($flux){		
		// necessaire pour utiliser les autorisations
		include_spip('inc/autoriser');


		// API mailchimp
		include_spip('inc/1.3/MCAPI.class');

		// necessaire pour utiliser lire_config
		include_spip('inc/config');
		
		//les fonctions
		
		include_spip('squirrel_chimp_lists_fonctions');

		//recuperation de la config
		$apiKey = lire_config("squirrel_chimp/apiKey");
		$lists = lire_config("squirrel_chimp/mailinglists");
		$ajouter = lire_config("squirrel_chimp/ml_act_ajout");
		$enlever = lire_config("squirrel_chimp/ml_act_enleve");
		$actualiser = lire_config("squirrel_chimp/ml_act_actualise");
		$optin = lire_config('squirrel_chimp/ml_opt_in')?false:true; //yes, send optin emails
		$id_auteur = $flux['data']['id_auteur'];
		$email = $flux['args']['args'][4]['email'];
		
		$sql=sql_select('id_liste','spip_auteurs_listes','id_auteur='.$id_auteur.' AND statut="valide"');
		
		$lists_sync=array();
		
		while($data=sql_fetch($sql)){
			if($lists[$data['id_liste']])$lists_sync[$lists[$data['id_liste']]]=$data['id_liste'];
			}
	
		//on verifie que les parametres du plugin mailchimp sont initialisées
		if ($apiKey){

            spip_log('actualisation_auteur','sclp');
            
			// initialisation d'un objet mailchimp
			$api = new MCAPI($apiKey);


			// les donnés de l'auteur
			
			$statut=sql_getfetsel('statut','spip_auteurs','id_auteur='.$id_auteur);
			
			//déterminer s'il s'agit d'un nouvel auteur
			if(_request('new')=='new')	$new=true;
			$message_ok = $flux['data']['message_ok'];

		
	
			// Actualisation de du profil si inscrit à une mailinglist et si activé actualisation des données si modification du profil
			if (!$new AND $actualiser AND $statut!='5poubelle'){
				spip_log('actualiser','sclp');
				


				// Inscription pour chaque liste
				foreach($lists_sync AS $listId=>$id_liste_spip){
					// compilation des informations à envoyer à MailChimp
					$donnees_auteur=donnees_sync($id_liste_spip,'',$id_auteur);
					$retour=inscription_liste_mc($flux,$api,$listId,$email,$donnees_auteur[1],$email_type,$optin,true);	
					}

			} 
			// Se le statut est mis à "poubelle", on le désinscrit
			elseif ($statut=='5poubelle' AND $enlever){
				foreach($lists_sync AS $listId=>$value){
					desinscription_liste_mc($flux,$api,$listId,$email);
					
				}
			}


		} //($apiKey and $listId)
		else {
			// n'effrayons pas l utilisateur classique
			spip_log(__LINE__);
			if (autoriser("configurer", "mailchimp")){
				spip_log(__LINE__);
				//erreur il faut configurer le plugin mailchimp
				$flux['data'] = array('message_erreur' => _T('squirrelchimp:config_erreur'));
				spip_log("Admin"._T('squirrelchimp:config_erreur'));
				return $flux;
			}
			else {
				spip_log(__LINE__);
				// que le spiplog si on est juste un user
				spip_log(_T('squirrelchimp:config_erreur'));
				return $flux;
			} // autoriser

			spip_log(__LINE__);
		} // if ( $apiKey and $listId )	{

		spip_log(__LINE__);
		return $flux;
	}
?>
