<?php

if (!defined("_ECRIRE_INC_VERSION")) return;

// Syncroniser des listes
function inc_syncroniser_listes_dist($api='',$id_liste_spip,$id_liste_mailchimp,$status='',$start='',$limit='',$forcer=false){

	include_spip('squirrel_chimp_lists_fonctions');
	include_spip('sclp_fonctions');
	
	// Définit le fuseau horaire par défaut à utiliser, GMT est le fuseau utilisé par mailchimp.
	date_default_timezone_set('GMT');
	
	//pour ecrire_config 
	include_spip('inc/config');
	
	//L'api de mailchimps
	include_spip('inc/1.3/MCAPI.class');

	
	// initialisation d'un objet mailchimp	
	if(!$api){
		$apikey=lire_config('squirrel_chimp/apiKey');
		$api = new MCAPI($apikey);
		}
	
	// la date de la dernière syncro générale
	$since=sql_getfetsel('date_syncro','spip_listes_syncro','objet="listes" AND type_syncro="liste" AND id_objet='.$id_liste_spip,'','date_syncro DESC');

	
	// les données spip

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
	if($identifiant_joints)$where=' AND '.$identifiant_joints;
		

	$champs=implode(',',$champs).',spip_auteurs_listes.maj,spip_auteurs_listes.date_syncro,spip_auteurs_listes.statut,spip_auteurs.id_auteur,spip_auteurs.id_mailchimp';	
	
	// syncroniser tout, n'importe la date de la dernière mise à jour
	if(!$forcer)$since='0000-00-00 00:00:00';
		
	
	$liste_spip=sql_select($champs,'spip_auteurs_listes,'.$from,'spip_auteurs_listes.id_liste='.$id_liste_spip.' AND spip_auteurs_listes.id_auteur=spip_auteurs.id_auteur AND spip_auteurs_listes.date_syncro >'.sql_quote($since).$where);
	
	// Composer le tableau distinguant entre abonnées et désabonnées
	$listes_spip=array();
	while($data=sql_fetch($liste_spip)){
		if($data['email'])$listes_spip[$data['statut']][$data['email']]=$data;
		}
	
	//Les listes mc distinguant entre abonnées et désabonnées
	if(!$status){
		$statuts=array('subscribed','unsubscribed');
		$listes_mc=array();
		foreach($statuts AS $status){
			$listes_mc[$status]=membres_liste_mc($api,$id_liste_mailchimp,$status,'',$limit);
			}	
		}
	else {
		$liste_mc=membres_liste_mc($api,$id_liste_mailchimp,$status,$since,$limit);
		}
		spip_log($listes_mc, 'sclp');	
		spip_log($listes_spip, 'sclp');	
	//Etablir les candiats à la syncro.

	//D'abord les désinscriptions
	$liste_desabonnement=array();
	$liste_abonnement=array();
	$liste_traites_mc=array();
	$timestamp_desabo=array();
	
	foreach($listes_mc['unsubscribed']['data'] as $info_m_mc){
		$syncro_spip='';
		if($syncro_spip=$listes_spip['valide'][$info_m_mc['email']]['maj']){		
			if($info_m_mc['timestamp'] > $syncro_spip){
				$id_auteur=$listes_spip['valide'][$info_m_mc['email']]['id_auteur'];
				$liste_desabonnement['vers_spip'][$id_auteur]=$info_m_mc['timestamp'];
			}
			// on le réinscrir sur mc
			elseif($syncro_spip > $info_m_mc['timestamp']){
				$liste_abonnement['vers_mc'][$info_m_mc_2['email']]=$listes_spip['a_valider'][$info_m_mc_2['email']];
				}
			}
		}	

			
	//Ensuite les inscriptions et actualisations	

	foreach($listes_mc['subscribed']['data'] as $info_m_mc_2){
		$syncro_spip_2='';

		//L'inscris mc est désactivé en spip
				
		if($syncro_spip_2=$listes_spip['a_valider'][$info_m_mc_2['email']]['maj']){
			$id_auteur=$listes_spip['a_valider'][$info_m_mc_2['email']]['id_auteur'];

				if($info_m_mc_2['timestamp'] > $syncro_spip_2){
					 $liste_abonnement['vers_spip'][$id_auteur]=array(
																	'timestamp'=>$info_m_mc_2['timestamp'],
																	'email'=>$info_m_mc_2['email']);
				 }
				elseif($syncro_spip_2 > $info_m_mc_2['timestamp']){
					$liste_desabonnement['vers_mc'][$info_m_mc_2['email']]=$info_m_mc_2['timestamp'] ;
					}
				}
			//spip_log($info_m_mc_2['timestamp'], 'sclp');						
		//L'inscrit mc est active en spip mais date d'actualisation plus anciennes que celle de mc				
		elseif($syncro_spip_2=$listes_spip['valide'][$info_m_mc_2['email']]['maj']){

			$id_auteur=$listes_spip['valide'][$info_m_mc_2['email']]['id_auteur'];
				if($info_m_mc_2['timestamp'] > $syncro_spip_2){					 
					 $liste_abonnement['vers_spip'][$id_auteur]=array(
																'timestamp'=>$info_m_mc_2['timestamp'],																
																'email'=>$info_m_mc_2['email']);
					 }
				elseif($syncro_spip_2 > $info_m_mc_2['timestamp']){
				 		$liste_abonnement['vers_mc'][$info_m_mc_2['email']]=$listes_spip['valide'][$info_m_mc_2['email']];
				 		}
				}
			//Présent sur mailchhimp 	
		else{

			//mais pas abonné à la liste spip ou pas de modifications depuis la dernière syncro	
			$id_auteur=$listes_spip['valide'][$info_m_mc_2['email']]['id_auteur'];
			$liste_abonnement['vers_spip'][$info_m_mc_2['email']]=array(
															'timestamp'=>$info_m_mc_2['timestamp'],
															'email'=>$info_m_mc_2['email'])	;
			}	
		$liste_traites_mc[$info_m_mc_2['email']]=$info_m_mc_2['timestamp'];		
		}
		
	
	// On récupère les abonées spip pas encore traitées	
	$a_traiter_abo=array_diff_key($listes_spip['valide'],$liste_traites_mc);

	foreach ($a_traiter_abo AS $email=>$data){
		$liste_abonnement['vers_mc'][$email]=$listes_spip['valide'][$email];
		}
		

	
	// On sycronise
	
	// les variables
	$optin = lire_config('squirrel_chimp/ml_opt_in')?false:true; //yes, send optin emails
	$up_exist = true; // yes, update currently subscribed users
	$replace_int = false; // no, add interest, don't replace
	
	spip_log('abonnements id_liste:'.$id_liste_spip, 'sclp');
	spip_log($liste_abonnement, 'sclp');
	
	spip_log('desabonnements id_liste:'.$id_liste_spip, 'sclp');	
	spip_log($liste_desabonnement, 'sclp');
	$resultat=array();
	// Désabonnement
	

	if($liste_desabonnement){
		if($liste_desabonnement['vers_mc']){		
			$resultat['desabonnement']['vers_mc']=desinscription_batch_mc($api,$id_liste_mailchimp,$liste_desabonnement['vers_mc']);
			}
		elseif($liste_desabonnement['vers_spip']){
			$resultat['desabonnement']['vers_spip']=desinscription_batch_spip($id_liste_spip,$liste_desabonnement['vers_spip'],$timestamp_desabo);
			}
		}
		
	
	// Inscriptions	
	if($liste_abonnement){
		if($liste_abonnement['vers_mc']){
			$batch=donnees_sync_simple($id_liste_spip,$liste_abonnement['vers_mc']);	
				
			$resultat['abonnement']['vers_mc']=inscription_batch_mc($api,$id_liste_mailchimp,$batch,$optin,$up_exist,$replace_ints);
			
			}
		elseif($liste_abonnement['vers_spip']){
			//spip_log($liste_abonnement['vers_spip'], 'sclp');
			$resultat['abonnement']['vers_spip']=inscription_batch_spip($id_liste_spip,$liste_abonnement['vers_spip']);
			}
		}
		
		
	$actualiser_dates=array_merge($liste_abonnement['vers_mc'],$liste_desabonnement['vers_mc']);	
	$date=date('y-m-d G:i:s');
	$vals=array('maj'=>$date,'date_syncro'=>$date);
	foreach	($actualiser_dates as $email=>$data){
		if($data['id_auteur'])sql_updateq('spip_auteurs_listes',$vals,'id_auteur='.$id_auteur.' AND id_liste='.$id_liste_spip);
		else{
			$id_auteur=sql_getfetsel('email','spip_auteurs','email='.sql_quote($email));
			sql_updateq('spip_auteurs_listes',$vals,'id_auteur='.$id_auteur.' AND id_liste='.$id_liste_spip);
			}
		}
	sql_updateq('spip_listes',$vals,'id_liste='.$id_liste_spip);		
	sql_getfetsel('date_syncro','spip_listes_syncro','objet="listes" AND type_syncro="generale" AND id_objet='.$id_liste_spip,'','date_syncro DESC');
	$v=array('objet'=>'listes','type_syncro'=>'liste','id_objet'=>$id_liste_spip,'date_syncro'=>$date);
	sql_insertq('spip_listes_syncro',$v);
		
	spip_log('résultats pour la liste : '.$id_liste_spip.' depuis la dernière actualisation : '.$since.' date syncro:'.$date, 'sclp');	
	spip_log($resultat, 'sclp');		

	//$nombre_liste_spip=sql_count($liste_spip);
	//$nombre_liste_mc=count($liste_mc);
	
	/*if($nombre_liste_spip>=$nombre_liste_mc)$sync=syncro_spip_mc($liste_spip,$liste_mc,$derniere_syncro);
	else $sync=syncro_mc_spip($liste_spip,$liste_mc,$derniere_syncro);*/
	//spip_log('a traiter'.serialize($a_traiter), 'sclp');	
	//spip_log($listes_spip['valide'], 'sclp');
	//spip_log($listes_spip['a_valider'], 'sclp');

	
	//spip_log($listes_mc, 'sclp');

	//spip_log($listes_spip, 'sclp');
	//spip_log($desabonner_mc, 'sclp');
	
	//spip_log($liste_traites_mc, 'sclp');	

	
	//spip_log($data, 'sclp');
	//spip_log("Admin $messageErreur",'squirrel_chimp');
	return;
	
}
?>
