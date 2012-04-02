<?php
if (!defined("_ECRIRE_INC_VERSION")) return;

//Tables principales

function sclp_declarer_tables_principales($tables_principales){
	
	// Création ou actualisation de spip_listes
	$spip_listes = array(
		"id_liste"					=> "bigint(21) NOT NULL",
		"id_liste_mailchimp"		=> "varchar(50) NOT NULL",
		"titre"						=> "text NOT NULL",
		"descriptif"				=> "text NOT NULL",
		"texte"						=> "longblob NOT NULL",
		"date_creation"				=> "datetime DEFAULT '0000-00-00 00:00:00' NOT NULL",
		"date_syncro"				=> "datetime DEFAULT '0000-00-00 00:00:00' NOT NULL",
		"maj"						=> "datetime DEFAULT '0000-00-00 00:00:00' NOT NULL",
		"titre_message"				=> "varchar(255) NOT NULL",
		"periode"					=> "bigint(21) NOT NULL",
		"lang"						=> "varchar(10) NOT NULL",
		"statut"					=> "varchar(10) NOT NULL default 'prive'",	  	
		);
	
	$spip_listes_key = array(
		"PRIMARY KEY"				=> "id_liste",
		"KEY id_liste_mailchimp"	=> "id_liste_mailchimp",
		);

	$spip_listes_join = array(
		"id_liste"					=> "id_liste",
		"id_liste_mailchimp"		=> "id_liste_mailchimp",
		);	
	
	$tables_principales['spip_listes'] = array(
		'field' 					=> &$spip_listes,
		'key'						=> &$spip_listes_key,
		'join' 						=> &$spip_listes_join
		);
			
	// Création ou actualisation de spip_listes_syncro
	
    $listes_syncro = array(
		"id_syncro"					=> "bigint(21) NOT NULL",
		"date_syncro"				=> "datetime DEFAULT '0000-00-00 00:00:00' NOT NULL",	
		"objet"						=> "varchar(50) NOT NULL",	
		"type_syncro"				=> "varchar(50) NOT NULL",	
		"id_objet"					=> "bigint(21) NOT NULL",
                        );
        
        $listes_syncro_key = array(
                        "PRIMARY KEY"   => "id_syncro",
                        );
                        
		$listes_syncro_join = array(
		"spip_syncro"					=> "spip_syncro",
		);	                       
        
        $tables_principales['spip_listes_syncro'] =
                array(
                'field' => &$listes_syncro, 
                'key' => &$listes_syncro_key,
                'join' 	=> &$listes_syncro_join);
		

	// Extension de la table auteurs
	/*$tables_principales['spip_auteurs']['field']=array(
		"id_mailchimp"				=> "bigint(21) NOT NULL default '0'",
		"date_syncro"				=> "datetime DEFAULT '0000-00-00 00:00:00' NOT NULL",
		"format"					=> "enum('html','texte') NOT NULL default 'html'"		
		);*/

        $tables_principales['spip_auteurs']['field']['id_mailchimp'] = "varchar(50) NOT NULL";
        $tables_principales['spip_auteurs']['field']['date_syncro'] = "datetime DEFAULT '0000-00-00 00:00:00' NOT NULL";
        $tables_principales['spip_auteurs']['field']['format'] = "enum('html','texte') NOT NULL default 'html'";
        

        return $tables_principales;
}       
        
        


// Tables auxiliaires

function sclp_declarer_tables_auxiliaires($tables_auxiliaires){
	
	
	// Création ou actualisation de spip_auteurs_listes
	$spip_auteurs_listes = array(
		"id_auteur"					=> "bigint(21) NOT NULL default '0'",
		"id_mailchimp" 				=> "varchar(50) NOT NULL",
		"id_liste" 					=> "bigint(21) NOT NULL default '0'",
		"date_inscription"			=> "datetime NOT NULL default '0000-00-00 00:00:00'",
		"maj"						=> "datetime NOT NULL default '0000-00-00 00:00:00'",
		"date_syncro"				=> "datetime NOT NULL default '0000-00-00 00:00:00'",
		"statut"					=> "enum('a_valider','valide') NOT NULL",
		"format"					=> "enum('html','texte') NOT NULL default 'html'"	  	
		);
	
	$spip_auteurs_listes_key = array(
		"KEY id_auteur"				=> "id_auteur",
		"KEY id_liste"				=> "id_liste",
		"KEY id_mailchimp"			=> "id_mailchimp",
		);


		
	$tables_auxiliaires['spip_auteurs_listes'] = array(
		'field' => &$spip_auteurs_listes,
		'key' => &$spip_auteurs_listes_key,
	);	
	return $tables_auxiliaires;
}
