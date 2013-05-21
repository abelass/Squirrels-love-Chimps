<?php
if (!defined("_ECRIRE_INC_VERSION")) return;

// Insertion de la feuille de style dans l'espace privé
function squirrel_chimp_header_prive($flux){

	$flux .= '<link rel="stylesheet" href="'.find_in_path('css/squirrel_chimp.css').'" type="text/css" media="all" />';
	
	return $flux;
}

//Insertion dans la colonne droite de l'espace privé
function squirrel_chimp_affiche_droite($flux){
	
		$exec=$flux['args']['exec'];
	
	// Insertion d'un menu racourcis pour modèles

	// Définition sur quelle page c'est affiché	
	$affichage_page=array('article_edit','articles_edit','rubriques_edit','rubrique_edit');
	
	// Des plugins peuvent y inclure d'autres pages
	$pipeline =pipeline('squirrel_chimp_pages_raccourcis_models',array());

	if(in_array($exec,$affichage_page) OR in_array($exec,$pipeline)){
		
		//On y in afficher toute noisette "prive/squelettes/extra/NOM_OBJET_DECLARE_modeles.html"
		
		$objets=charger_fonction('squirrel_chimp_definitions','inc');
		$objets=$objets();
		$chemin='prive/squelettes/extra/';
		
		// Cherche les noisettes à inclure et compile le contenu
		foreach($objets AS $objet=>$value){
			if(find_in_path($chemin.$objet.'_modeles.html'))$contenu .=recuperer_fond($chemin.$objet.'_modeles',$flux);
			}
		// Insert le cotenu
		if ($contenu) $flux['data'] .= cadre_depliable(find_in_path('images/logo_slc_24.png'),_T('squirrelchimp/spip:titre_cadre_raccourcis'),false,$contenu,$ids='','r squirrel_chimp');
	
	}
		
	return $flux;
}


?>
