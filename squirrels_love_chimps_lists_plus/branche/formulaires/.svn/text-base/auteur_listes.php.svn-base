<?php

// Securité
if (!defined("_ECRIRE_INC_VERSION")) return;



 function formulaires_auteur_listes_charger_dist($id_auteur){

	# API mailchimp
	include_spip('inc/1.3/MCAPI.class');	 
	$apikey=lire_config('squirrel_chimp/apiKey');
	$api = new MCAPI($apikey);
	
	$ajouter = lire_config("squirrel_chimp/ml_act_ajout");
	$enlever = lire_config("squirrel_chimp/ml_act_enleve");
	$actualiser = lire_config("squirrel_chimp/ml_act_actualise");
	$optin = lire_config('squirrel_chimp/ml_opt_in')?false:true; //yes, send optin emails
	
	 // On synchronise les données avec mailchimp
	 $listes_accordes=lire_config('squirrel_chimp/mailinglists');
	 


	 $auteur=sql_fetsel('email,format','spip_auteurs','id_auteur='.$id_auteur);
	 $email= $auteur['email'];	
	 $format= $auteur['format'];	
	  
	 $listes=sql_select('*','spip_auteurs_listes','id_auteur='.$id_auteur);

	$editable=donnees_sync('','',$id_auteur)?true:false;		
	 
	 $info_liste_spip=array();
	 
	 while($data=sql_fetch($listes)){
		 $info_liste_spip[$data['id_liste']]=$data; 
		 }
	 
	  $info_liste_mc=array();
	 
	 $status_equivalence=array(
		'valide'=>array('subscribed'),
		'a_valider'=>array('unsubscribed','cleaned'),
		);
		
	 $status=array();
	
	if($editable){
	 foreach($listes_accordes AS $id_liste_spip=>$id_liste_mc){

		$timestamp=''; 
		 /*Faire d'abord un seul appel à Mc pour afficher plus raidement le formulaire
		  * On en retire les informations sur les statut des listes
		  */
		
		 if($id_liste_mc)$member_info=membres_liste_info_mc($api,$id_liste_mc,$email);
			 $status[$id_liste_mc]=$member_info['data'][0]['status'];
			 $timestamp=$member_info['data'][0]['timestamp'];
			 if(is_array($member_info['data'][0]['lists']))$lists_members=array_merge($status,$member_info['data'][0]['lists']);
			 else $lists_members=$status;
			
 
		 // Si le statut mc ne correpond pas au statut spip on syncronise
		$statut_spip=$info_liste_spip[$id_liste_spip]['statut']?$info_liste_spip[$id_liste_spip]['statut']:'a_valider';

		if($lists_members[$id_liste_mc]){
			
			 if	 (!in_array($lists_members[$id_liste_mc],$status_equivalence[$statut_spip])){

				if(!$timestamp){
					if($email)$member_info=membres_liste_info_mc($api,$id_liste_mc,$email);
					$timestamp=$member_info['data'][0]['timestamp'];
					}
			 //Info mailchimp plus récente que celle de spip
	
					 if($timestamp=$member_info['data'][0]['timestamp']>$info_liste_spip[$id_liste_spip]['maj']){
		 
						 if($status_mc=$member_info['data'][0]['status']=='subscribed'){

								$test=sql_updateq('spip_auteurs_listes',array('statut'=>'valide','maj'=>$timestamp),'id_auteur='.$id_auteur.' AND id_liste='.$id_liste_spip);
								if (!$teste) sql_insertq('spip_auteurs_listes',array('statut'=>'valide','maj'=>$timestamp,'id_auteur'=>$id_auteur,'id_liste'=>$id_liste_spip));
							 }
						else{
							 sql_updateq('spip_auteurs_listes',array('statut'=>'a_valider','maj'=>$timestamp),'id_auteur='.$id_auteur.' AND id_liste='.$id_liste_spip);				
							}
						 
						 }
					elseif($timestamp=$member_info['data'][0]['timestamp']<$info_liste_spip[$id_liste_spip]['maj']){

							// compilation des informations à envoyer à MailChimp
							$auteur=donnees_sync_simple($id_liste_spip,$info_liste_spip[$id_liste_spip]);
							
							spip_log($donnees_auteur,'sclp');
			
							$auteur=$auteur[1];
			
							$vals=inscription_liste_mc($valeurs,$api,$id_liste_mc,$email,$auteur,$format,$optin,true);
										
						}
					elseif($member_info['data'][0]['error']=='The email address passed does not exist on this list'){

						if($info_liste_spip[$id_liste_spip]['statut']=='valide'){

							// compilation des informations à envoyer à MailChimp
						
							$auteur=donnees_sync_simple($id_liste_spip,$info_liste_spip[$id_liste_spip]);
										
							$auteur=$auteur[1];
			
							$vals=inscription_liste_mc($valeurs,$api,$id_liste_mc,$email,$auteur,$format,$optin,true);	
							
							 }
							
						}				
					
				 }
				 spip_log($vals, 'sclp');	
		 }
			 // l'abonnée spip n'existe pas sur mc on désincrit
			 elseif(!$lists_members[$id_liste_mc]){

			sql_delete('spip_auteurs_listes','id_auteur='.$id_auteur.' AND id_liste='.$id_liste_spip);				
				}	
				 
		

		 }	 
	 
 }
	 
	 $liste=sql_select('id_liste','spip_auteurs_listes','statut="valide" AND id_auteur='.$id_auteur);
	 

	$format=sql_getfetsel('format','spip_auteurs','id_auteur='.$id_auteur);
	
	$listes_abos=array(); 
	while($data=sql_fetch($liste)){
		 $listes_abos[]=$data['id_liste'];
		 }
		 
	$listes_abos_prev=$listes_abos;	 
	
	if(_request('listes_abos')) $listes_abos=_request('listes_abos');	
	 
	$valeurs = array(
		'listes_abos'=> $listes_abos,
		'format'=>$format,	
		'listes_abos_prev'=>'',	
		'editable'=>$editable,		
		);
	$valeurs['_hidden']	.='<input type="hidden" name="listes_abos_prev" value="'.implode(',',$listes_abos_prev).'"/>';
				
	return $valeurs;	
}



function formulaires_auteur_listes_verifier_dist($id_auteur){
	$erreurs = array();


	// verifier que les champs obligatoires sont bien la :

   /*foreach(array('titre','lang','qsdfd') as $champ) {
        	if (!_request($champ)) {
            		$erreurs [$champ] = _T('spip:info_obligatoire');
        		}
    	}*/
    	
	return $erreurs;
}
 

 

function formulaires_auteur_listes_traiter_dist($id_auteur){

	# API mailchimp
	include_spip('inc/1.3/MCAPI.class');
	
	
	#pour ecrire_config 
	include_spip('inc/config');
	

		
	$apikey=lire_config('squirrel_chimp/apiKey');
	$api = new MCAPI($apikey);
	$listes_accordes=lire_config('squirrel_chimp/mailinglists');

	$date=date('Y-m-d G:i:s');
	//les fonctions
		
	include_spip('squirrel_chimp_lists_fonctions');
	include_spip('sclp_fonctions');

	//recuperation de la config

	$lists_sync = $listes_accordes;
	$ajouter = lire_config("squirrel_chimp/ml_act_ajout");
	$enlever = lire_config("squirrel_chimp/ml_act_enleve");
	$actualiser = lire_config("squirrel_chimp/ml_act_actualise");
	$optin = lire_config('squirrel_chimp/ml_opt_in')?false:true; //yes, send optin emails

	//on verifie que les parametres du plugin mailchimp sont initialisées


	// necessaire pour utiliser lire_config	
	include_spip('inc/config');
	

	$listes_abos= _request('listes_abos')?_request('listes_abos'):array();
	$format= _request('format');
	$listes_abos_prev= explode(',',_request('listes_abos_prev'));


	if(is_array($listes_abos_prev)){
		
		foreach($listes_abos_prev AS $id_liste){
			if(!in_array($id_liste,$listes_abos)){
				if ($id_liste){
					
					$id_mailchimp=sql_getfetsel('id_mailchimp','spip_auteurs_listes','id_auteur='.$id_auteur.' AND id_liste='.$id_liste);
				
					sql_updateq('spip_auteurs_listes',array('statut'=>'a_valider'),'id_auteur='.$id_auteur.' AND id_liste='.$id_liste);

					if($id_mailchimp AND $listes_accordes[$id_liste]){
						if($listes_accordes[$id_liste])$vals=desinscription_liste_mc('',$api,$listes_accordes[$id_liste],$id_mailchimp);
						$message_ok.='<li>'._T('sclp:liste').' - '.$id_liste.': '. $vals['data']['message_ok'].'</li>';
						}
					}
				}
			}	
		}


	if(is_array($listes_abos)){
		foreach($listes_abos AS $id_liste){
			
			if(!in_array($id_liste,$listes_abos_prev)){
				$l=sql_getfetsel('id_liste','spip_auteurs_listes','id_auteur='.$id_auteur.' AND id_liste='.$id_liste);
								
				// compilation des informations à envoyer à MailChimp
				$auteur=donnees_sync($id_liste,'',$id_auteur);
				


				//$auteur=$auteur[1];

				$email=$auteur[1]['EMAIL'];
				$liste=$listes_accordes[$id_liste];

				$statut=$auteur['statut'];
				if($email){
				if($liste)$vals=inscription_liste_mc($valeurs,$api,$liste,$email,$auteur,$format,$optin,true);
				
				
				if($vals['data']['message_erreur']){
					$message_erreur.='<li>'._T('sclp:liste').' - '.$id_liste.': '. $vals['data']['message_erreur'].'</li>';
					}
				else{
					if($email)$infos=membres_liste_info_mc($api,$liste,$email);
					$infos=$infos['data'][0];
	
					$id_mailchimp=$infos['id'];
					$date_syncro=$infos['info_changed'];
					if(!intval($l)){
						sql_insertq('spip_auteurs_listes',array('statut'=>'valide','id_auteur'=>$id_auteur,'id_liste'=>$id_liste,'id_mailchimp'=>$id_mailchimp,'date_inscription'=>$date,'maj'=>$date_syncro));
	
						}
					else {
						sql_updateq('spip_auteurs_listes',array('statut'=>'valide','id_mailchimp'=>$id_mailchimp,'date_inscription'=>$date,'maj'=>$date_syncro),'id_auteur='.$id_auteur.' AND id_liste='.$id_liste);
						}
					$message_ok.='<li>'._T('sclp:liste').' - '.$id_liste.': '. $vals['data']['message_ok'].'</li>';		
					}
				$liste='';
				spip_log($vals, 'sclp');	
				}
				else $message_ok.='<li>'._T('sclp:actualisation_spip_ok_probleme_actualisation_mailchimp').'</li>';	
			}	
		}

		}
		
		if($message_ok) $valeurs['message_ok'] = '<ul>'.$message_ok.'</ul>';
		if($message_erreur) $valeurs['message_erreur'] = '<ul>'.$message_erreur.'</ul>';		
	return $valeurs;
}

?>
