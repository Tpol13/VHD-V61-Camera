<?php

#  Translat 
# https://www.codeandweb.com/babeledit/tutorials/translation-with-gettext-and-php 
#
# gettext ne fonctionne pas, donc je passe à kkch de plus simple et 
# de moin gourmand en CPU/RAM !



# VERSION = 20230821


#
# $Lang	= new Lang( $array );
#
# $array	= [
# 				"list"	=> [
#
#					"charset"	=> type d'encodage : UTF-8,  ISO-5589-15 ....
#					"directory"	=> Dossier contennant les locales
#					"domain"	=> Nom de l'application (.mo)
#			 				],
#
#				"mycfg"		=> new myCFG(), contient une instance avec la configuration par défaut que doit adopter Lang()
#				"lang"		=> xx_XX   $primaryLang_subLang ex: "fr_CA" or "fr", "en_GB" or "en", ...
# ];
#
# <!>	"lang" peut prendre deux types de format "xx_XX" ou "xx". Si "xx_XX" est choisie le charset détecté ou par défaut est ajouté.
#		Il est donc concidéré que l'architecture locale est au format gettext()
#
#


class Lang extends Debug
{
	#private	$arg;
	public		$LC_MESSAGES	= "LC_MESSAGES";
	public		$ext_default	= "php";
	public		$file;
	public		$primaryLang;
	public		$subLang;
	public		$charset		= "UTF-8";
	public		$lang			= "en_EN";
	protected	$language;
	public		$myCFG;

	function Lang($options=array())
	{
		return(__construct($options));
	}

	function __construct($options=array())
	{
		$this->default();

		if (is_subclass_of(__CLASS__, 'Debug')) { parent::__construct($options["list"]?$options["list"]:$options); $this->dbg = &$this; } else { $this->dbg = new Debug($options["list"]?$options["list"]:$options); }

		if (count($options) )
		{

			# Gestion des arguements
			if ( !isset($options["myCFG"]) ||  (isset($options["myCFG"]) && gettype($options["myCFG"]) != "object"))
			{
				$this->myCFG	= new MyCFG();
			}
			else
			{
				$this->myCFG	= $options["myCFG"];
			}

			$options["method"]	= "http";
			$this->arg	= new Argv( $options );

			if ($charset	= $this->arg->get("charset"))
			{
				$this->charset	= $charset;
			}

			# Gestion langue
			$this->detectLanguage();

			if ($this->domain	= $this->arg->get("domain"))
			{
				# Déterminer nom du fichier $this->file
				$this->determined();
			}
			else
			{
				$this->dbg->verbose(1, $this->_("domain_no_define"));
			}
		}
		return($this);
	}

	function __destruct()
	{
	}

	function get($var) { return($this->arg->get($var)); }
	function set($var) { return($this->arg->set($var)); }

	function _($lang="")
	{
		$txt	= $this->language[$lang];
		if (isset($txt) && (gettype($txt) == "boolean") )
		{
			$txt	= &$lang;
		}
		return($txt );
	}

	function determined()
	{
		$form_gettext		= 0;
		$lang_diff_sublang	= 0;	
		$test				= array();
		$LANG_SURVEILLANCE	= $this->default();

		if ( isset($this->subLang) )
		{
			$form_gettext	= 1;
			if ( strcasecmp($this->subLang, $this->lang) != 0 )
			{
				$lang_diff_sublang	= 1;	
			}
		}

		# langue ) tester en cas d'échec de la primière formule
		$test[]	= sprintf("%s/%s/%s/%s.%s",		$this->myCFG->get("locales_dir"), $this->lang, $this->LC_MESSAGES, $this->domain, $this->ext_default);
		$test[]	= sprintf("%s/%s_%s.%s/%s/%s.%s",	$this->myCFG->get("locales_dir"), $this->primaryLang, strtoupper($this->subLang), $this->charset, $this->LC_MESSAGES, $this->domain, $this->ext_default);
		$test[]	= sprintf("%s/%s_%s.%s/%s/%s.%s",	$this->myCFG->get("locales_dir"), $this->primaryLang, strtoupper($this->primaryLang), $this->charset, $this->LC_MESSAGES, $this->domain, $this->ext_default);
		$test[]	= sprintf("%s/%s_%s/%s/%s.%s",	$this->myCFG->get("locales_dir"), $this->primaryLang, $this->subLang, $this->LC_MESSAGES, $this->domain, $this->ext_default);
		$test[]	= sprintf("%s/%s_%s/%s/%s.%s",	$this->myCFG->get("locales_dir"), $this->primaryLang, strtoupper($this->primaryLang), $this->LC_MESSAGES, $this->domain, $this->ext_default);
		$test[]	= sprintf("%s/%s/%s/%s.%s",		$this->myCFG->get("locales_dir"), $this->primaryLang, $this->LC_MESSAGES, $this->domain, $this->ext_default);
		foreach ($test as $file)
		{
			if (file_exists($file))
			{
				if (is_file($file))
				{
					if ( !($ret	= include( $file )) )
					{
						$this->dbg->verbose(99, $this->_("lang_not_read_file"), $file );
					}
					else
					{
						$this->language	= array_merge($this->language, $LANG_SURVEILLANCE);
						$this->dbg->verbose(99, $this->_('lang_file_charge'), $file );
						$this->file	= $file;
						break;
					}
				}
				else
				{
					$this->dbg->verbose(99,$LANG_SURVEILLANCE["lang_not_type_file"], $file );
				}
			}
			else
			{
				$this->dbg->verbose(99, $this->_("file_not_found"), $file);
			}
		}
	}

	function default()
	{
		$LANG_SURVEILLANCE	= array();
		$LANG_SURVEILLANCE["lang_not_read_file"]	= "Impossible de lire le fichier de lang nommé '%s";
		$LANG_SURVEILLANCE["lang_not_type_file"]	= "&Lang::Suck_lang('%s') n'est pas un fichier";
		$LANG_SURVEILLANCE["lang_name_file"]		= "Nom du fichier local : ";
		$LANG_SURVEILLANCE["lang_name_sub"]			= "Nom de la sous langue : ";
		$LANG_SURVEILLANCE["lang_type_code"]		= "Type d'encodage : ";
		$LANG_SURVEILLANCE["worth"]					= "vaut";
		$LANG_SURVEILLANCE['option_shell']			= 'Option shell';
		$LANG_SURVEILLANCE["single_req_file"]		= "Mono fichier demandé: '%s'";
		$LANG_SURVEILLANCE["method_active"]			= "Méthode dossier activée";
		$LANG_SURVEILLANCE["process_video"]			= "Traitement de la vidéo '%s'";
		$LANG_SURVEILLANCE["duration"]				= "Durée: %s";
		$LANG_SURVEILLANCE["she_has_number_img"]	= "Elle possède '%d' images";
		$LANG_SURVEILLANCE["size_video"]			= "Taille de la vidéo: '%dx%d'";
		$LANG_SURVEILLANCE["convert2jpeg_name"]		= "Convertion en jpeg au nom de '%s'";
		$LANG_SURVEILLANCE["no_creation"]			= "pas de création d'imagette jpeg";
		$LANG_SURVEILLANCE["err_write_json"]		= "Erreur d'écriture du fichier JSON '%s'";
		$LANG_SURVEILLANCE["err_read_json"]			= "Erreur de lecture du fichier JSON '%s'";
		$LANG_SURVEILLANCE["return_of_xmlClose"]	= "Retour de xml_out->close(...) eq '%d'";
		$LANG_SURVEILLANCE["err_write_file"]		= "Erreur d'écriture du fichier %s";
		$LANG_SURVEILLANCE["err_msg_return"]		= "Message d'erreur retourné : '%s'";
		$LANG_SURVEILLANCE["err_opening_file"]		= "Erreur d'ouverture du fichier '%s'";
		$LANG_SURVEILLANCE["no_occurences_found"]	= "Aucunes occurences trouvées";
		$LANG_SURVEILLANCE["domain_no_define"]		= "Domaine non défini, impossible de charger le fichier des langues";
		$LANG_SURVEILLANCE["lang_file_charge"]		= "Fichier de langue chargé : '%s'";
		$LANG_SURVEILLANCE["file_not_found"]		= "Fichier '%s' manquant";
		$LANG_SURVEILLANCE["lang_opt_instant_detected"]	= "Langue optionnelle détecté dans l'instance de Lang() vaut '%s'";
		$LANG_SURVEILLANCE["lang_opt_instant_missing"]	= "Langue optionnelle absente, le système contient '%s'";
		$LANG_SURVEILLANCE["duration"]				= "Durée: (%s)";
		$LANG_SURVEILLANCE["trash"]					= "Corbeil";
		$LANG_SURVEILLANCE["delete_file"]			= "Suppression du fichier '%s'";
		$LANG_SURVEILLANCE["to_delete"]				= "à supprimer";
		$LANG_SURVEILLANCE["display"]				= "Afficher";
		$LANG_SURVEILLANCE["empty"]					= "Vider";
		$LANG_SURVEILLANCE["video"]					= "vidéo";
		$LANG_SURVEILLANCE["return"]				= "retour";
		$LANG_SURVEILLANCE["yes"]					= "yes";
		$LANG_SURVEILLANCE["cancel"]				= "annuler";
		$LANG_SURVEILLANCE["yes_empty_the_trash"]	= "Oui, vider la corbeil!";
		$LANG_SURVEILLANCE["undo"]					= "restaurer";
		$LANG_SURVEILLANCE['WARNING_raz_trash']		= "Si vous validez, tous les docuements se trouvant dans la corbeil vont être définitivement effacés avec aucun moyen de les récupérer";
		# --------------------- NEW
		#$LANG_SURVEILLANCE["Permanently delete the document"]	= "Supprimer définitivement le document";
		$LANG_SURVEILLANCE["Permanently delete the document"]	= "Effacer définitivement le document";
		$LANG_SURVEILLANCE["Restore Document"]					= "Restaurer le document";
		$LANG_SURVEILLANCE["Place this document in the trash"]	= "Placer le document dans la corbeil";
		$LANG_SURVEILLANCE["Management of video sequences captured by a camera"]	= "Gestion des séquences vidéos capturées par une camera";
		$LANG_SURVEILLANCE['mail_text_alarm']		= "
	Hello!

		Tu reçois ce message car l'alarm a été déclenchée
	le lien de la séquence vidéo est : %s.
	Croisons les doigts pour que ce soit une fausse alerte :)
";
		$LANG_SURVEILLANCE[""]	= "";

		$this->language	= $LANG_SURVEILLANCE;

		return($LANG_SURVEILLANCE);
	}


	// Fonction pour détecter la langue en se basant sur différentes sources
	function detectLanguage()
	{
		$lang	= "";

			# Si en arguement dans l'instance new Lang(...)
			if ($lang	= $this->get("lang"))
			{
				$this->dbg->verbose(1, $this->_("lang_opt_instant_detected"), $this->lang);
			}
			# Sinon détection automatique URL, ENV et locale
			elseif (isset($_SERVER['HTTP_ACCEPT_LANGUAGE']))
			{
				// Détecter la langue préférée du navigateur
				    $preferredLanguages = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
				    $languages		= explode(',', $preferredLanguages);
				    $languages[0]	= str_replace("-", "_", $languages[0]);
				
				    // Extraire la langue principale de la première entrée
				    $mainLanguage	= explode(';', $languages[0])[0];
				
				    // Comparer avec les langues supportées
				    $supportedLanguages = array( 'fr_FR'); // Autres langues supportées
				    if (in_array($mainLanguage, $supportedLanguages)) {
				        // La langue préférée du navigateur est supportée
				        $lang = $mainLanguage;
				    } else {
				        // Utiliser une langue par défaut
				        $lang = 'en_EN'; // Langue par défaut
				    }
			}
			else
			{
				// Vérifier les variables d'environnement
				$shellLang = getenv('LANG');
				$powershellLang = getenv('LANG');
				
				// Vérifier les variables de PHP
				$phpLang = setlocale(LC_ALL, 0);

				// Vérifier les paramètres d'URL
				$urlLang = isset($_GET['lang']) ? $_GET['lang'] : ''; # double emploi avec $this->get("lang")

				// Choix de priorité pour la langue
				$lang	= $urlLang ?: $shellLang ?: $powershellLang ?: $phpLang ;
				$this->dbg->verbose(1, $this->_("lang_opt_instant_missing"), $lang);
			}

			if (isset($lang) || $lang != "") { $this->lang	= $lang; }


			// Utilisation d'une expression régulière pour extraire les parties de la langue
			if (preg_match('/^([a-z]{2})(_[A-Z]{2})?(\.[^.]+)?$/', $this->lang, $matches))
			{
				$this->primaryLang	= $matches[1];  // Langue principale (ex. "en")
				#$this->subLang		= isset($matches[2]) ? substr($matches[2], 1) : '';  // Sous-langue (ex. "GB")
				if (isset($matches[2]))
				{
					$this->subLang	= substr($matches[2], 1);
				}
				#$this->charset		= isset($matches[3]) ? substr($matches[3], 1) : '';  // Partie après le point (ex. "UTF-8")
				if (isset($matches[3]))
				{
					$this->charset		= substr($matches[3], 1);  // Partie après le point (ex. "UTF-8")
				}
				#echo "Langue principale : " . $this->primaryLang . "\n";
				#echo "Sous-langue : " . $this->subLang . "\n";
				#echo "Charset : " . $this->charset . "\n";
			}

	}


	function status()
	{
		$status	= array();
		$status[]	= $this->language["lang_name_file"] .$this->file;
		$status[]	= $this->language["lang_type_code"] .$this->charset;
		$status[]	= sprintf($this->language["lang_opt_instant_detected"], $this->lang);
		$status[]	= $this->language["lang_name_sub"] .$this->subLang;
		return($status);
	}

	function test()
	{
		$Lang	= new Lang();
		printf("Langue détectée: '%s'\n'", $Lang->lang);
	}
}

