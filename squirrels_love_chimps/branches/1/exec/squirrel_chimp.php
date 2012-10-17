<?php

if (!defined("_ECRIRE_INC_VERSION")) return;

include_spip('inc/presentation');
include_spip('public/assembler');

function exec_squirrel_chimp_dist(){
	$contexte = array();
	$contexte = calculer_contexte();
	
	// si pas autorise : message d'erreur
	if (!autoriser('defaut')) {
		include_spip('inc/minipres');
		echo minipres();
		exit;
	}
	
	// pipeline d'initialisation
	pipeline('exec_init', array('args'=>array('exec'=>'squirrel_chimp'),'data'=>''));
	// entetes
	$commencer_page = charger_fonction('commencer_page', 'inc');


	// titre, partie, sous_partie (pour le menu)
	echo $commencer_page(_T('squirrelchimp:titre_plugin'), "editer", "editer");
	
	
	// colonne gauche
	echo debut_gauche('', true);
	
	echo debut_boite_info(true);
		echo recuperer_fond('prive/squelettes/navigation/sc_boite_info',$contexte);
	echo fin_boite_info(true);
	
	echo debut_cadre_relief('',true,'', _T('spip:titre_cadre_raccourcis'));	
		echo '<div class="shortcut">';
			echo recuperer_fond('prive/squelettes/navigation/sc_shortcuts',$contexte);
		echo '</div>';		
	echo fin_cadre_relief(true);
						
	echo pipeline('affiche_gauche', array('args'=>array('exec'=>'boutique'),'data'=>''));	
	

	// colonne droite
	echo creer_colonne_droite('', true);
	echo pipeline('affiche_droite', array('args'=>array('exec'=>'boutique'),'data'=>''));
	
	// centre
	echo debut_droite('', true);
	// contenu
	// ...


	//Insertion du contenu des différentes page : defaut ou appelé par la variable afficher
	$afficher = _request('afficher')?'_'._request('afficher'):'';
	

	echo recuperer_fond('prive/squelettes/contenu/squirrel_chimp'.$afficher,$contexte,array("ajax"=>true));

	// ...
	// fin contenu
	echo pipeline('affiche_milieu', array('args'=>array('exec'=>'boutique'),'data'=>''));
	
	echo fin_gauche(), fin_page();

}
?>
