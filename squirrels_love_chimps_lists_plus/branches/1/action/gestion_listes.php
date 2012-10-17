<?php

if (!defined("_ECRIRE_INC_VERSION")) return;

function action_gestion_listes_dist(){
	
	
	$securiser_action = charger_fonction('securiser_action', 'inc');
	$arg = $securiser_action();
	
	// necessaire pour utiliser lire_config
	include_spip('inc/config');
	
	$mailinglists=lire_config('squirrel_chimp/mailinglists');
	
	list($option,$id_liste,$args)= explode("-",$arg);
	
	$changement_statut=array('prive','poubelle');
	$date=date('Y-m-d G:i:s');
	
	
	if (in_array($option,$changement_statut)){	
		 sql_updateq('spip_listes',array('statut'=>$option,'maj'=>$date),'id_liste='.$id_liste);
		
		if($option=='poubelle'){
			
			$mailinglists[$id_liste]='';
					
			ecrire_config("squirrel_chimp/mailinglists",$mailinglists);
			}
		}
	else{
		switch($option){		
			case 'creer_liste_depuis_mc':
			
				list($titre,$lang)= explode("|",$args);				
				
				$valeurs=array(
					'titre'=>$titre,
					'id_liste_mailchimp'=>$id_liste,				
					'lang'=>$lang,	
					'date_creation'=>$date,	
					'maj'=>$date,	
					'statut'=>'prive',					
					);
					
					
				$id=sql_insertq('spip_listes',$valeurs);
				
				$mailinglists[$id]=$id_liste;
				
				ecrire_config("squirrel_chimp/mailinglists",$mailinglists);
			
				break;
			case 'eliminer':
				
				//On éfface la liste
				sql_delete('spip_listes','id_liste='.$id_liste);
				
				// on efface les abonnements à la liste
				sql_delete('spip_auteurs_listes','id_liste='.$id_liste);

				$mailinglists[$id_liste]='';
					
				ecrire_config("squirrel_chimp/mailinglists",$mailinglists);
			
				break;
			case 'syncroniser':
				include_spip('sclp_fonctions');
				
				$listes_accordes=lire_config('squirrel_chimp/mailinglists');
				
				$syncroniser=charger_fonction('syncroniser_listes','inc');
				$resultat = $syncroniser('',$id_liste,$listes_accordes[$id_liste],$status,$start,$limit,true);
				break;
			}
		
		}
	

}

?>
