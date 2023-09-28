<!DOCTYPE html>
<?php
require_once("config/autoload.php");


# VERSION: 20230928

# vim info : set tabstop=4

/*
 * Reste à faire :

	- ajouter icones Corbeil

	- Changer les class couleur lorsqu'on se trouve dans la corbeil.
			Il ne faut que les couleurs des cadres soient les mêmes !

	- Faire le styles d'affichage screen/imp

	- Afficher la date de prise de vue.

	- https://validator.w3.org/
 */

class	Liste
{
	public	$data		= array();
	public	$active		= false;
	public	$url		= "";

	public function Liste($data)
	{
		return($this->__construct($data));
	}

	public function __construct($data)
	{
		$this->reset();
		$this->data = $data;
	}

	public function __destruct()
	{
	}

	public function __set($name, $val)
	{
		if ($name == "active")
		{
			$this->active	= $val;
		}
		elseif ($name == "url")
		{
			$this->url	= $val;
		}
		else
		{
			throw new Exception("Propriété '$name' n'existe pas");
			$this->list[$name]	= $val; # for futur...
		}
	}

	public function __get($name)
	{
		$ret	= "";

		if ($name == "active")
		{
			if ($this->active)
			{
				$ret	= $this->data;
			}
		}
		elseif ($name == "url")
		{
			if ($this->url)
			{
				$ret	= $this->url;
			}
		}
		else
		{
			throw new Exception("Propriété '$name' n'existe pas");
		}

		return($ret);
	}


	public function __toString()
	{
		return($this->data);
		return(strval($this->data));
	}

	public function __invoke()
	{
		throw new Exception("Invoke n'est pas géré");
		return($this->data);
		return(strval($this->data));
	}
	public function	reset()
	{
		$this->data		= "";
		$this->active	= false;
		$url			= "";
		return($this);
	}


	public		function test ()
	{
		$data	= new Data("variableu");
		printf("Data contient '%s'\n", $data);
		$data->reset();
		printf("Data contient '%s', après reset()\n", $data);
	}
}

	$err			= array();
	$informations	= array();
	$tag_meta		= array();
	$tag_script		= array();
	$tag_script_validation	= array();
	$menu			= array();
	$get_play		= "";	# Détermine si l'option mosaic.php?play= est présent dans l'url.
	$dir_medias		= $myCFG->get("medias");
	$addr_video		= $myCFG->get("addr_video");

	$GET			= filter_var_array($_GET);

	$pgt	= new Option("div", $myCFG->get("div_default"));
	$list	= new Option("div");
	$div	= new Option("div", $myCFG->get("div_default"));
	$mos	= new Option("div", $myCFG->get("div_default"));
	$canvas	= new Option("canvas", [ "id"=> "canvas"] );
	$vid	= new Option("video", $myCFG->get("video"));
	$src	= new Option("source", $myCFG->get("source"));
	$p		= new Option("p", $myCFG->get("p_default"));
	$a		= new Option("a", $myCFG->get("a_default"));
	$img	= new Option("img", $myCFG->get("img_default"));
	$br		= new Option("br");
	$span	= new Option("span", $myCFG->get("div_default"));
	$span->delvar("id");
	$br->monotag(false); # It seems that it is no longer useful to have a <br />!



	define("DISPLAY_VIDEO", 1);
	define("DISPLAY_TRASH", 2);	
	define("EMPTY_TRASH", 3);	

	$msg_display_video			= sprintf("%s %s", $myCFG->lang->_("display"), $myCFG->lang->_("video"));
	$msg_display_trash			= sprintf("%s %s", $myCFG->lang->_("display"), $myCFG->lang->_("trash") );
	$msg_empty_trash			= sprintf("%s %s", $myCFG->lang->_("empty"), $myCFG->lang->_("trash"));

	if (isset($argv[0]))
	{
		$this_script			= $myCFG->get("home_dir")."/".$argv[0];
	}
	else
	{
		$this_script			=  $_SERVER["SCRIPT_NAME"];
	}

	$menu[DISPLAY_TRASH]		= new Liste( $msg_display_trash );
	$menu[DISPLAY_TRASH]->url	= $this_script."?trash=display";

	$menu[EMPTY_TRASH]			= new Liste( $msg_empty_trash );
	$menu[EMPTY_TRASH]->url		= $this_script."?del=all";

	$menu[DISPLAY_VIDEO]		= new Liste( $msg_display_video );
	$menu[DISPLAY_VIDEO]->url	= $this_script;

	$meta_backPage	= sprintf(
						<<<END
	// Redirection par meta && windows.open()
	<meta http-equiv="refresh" content="3; url=%s">
END
, $_SERVER['HTTP_REFERER']?$_SERVER['HTTP_REFERER']:$this_script);

	$js_backPage	= sprintf(
						<<<END
	// Redirection par meta && windows.open()
	window.onload = function() {

		setTimeout(function() {
		window.open( "%s" , "_self");
		//history.back();
    	}, 500);

	};
END
, $_SERVER['HTTP_REFERER']?$_SERVER['HTTP_REFERER']:$this_script);

	#$meta_backPage	= "";
	#$js_backPage	= "";

	if (isset($GET["play"]))
	{
		$dir	= new FileDir(  $dir_medias );
		if ( count($ret	= $dir->get3array($GET["play"])) >= 1)
		{
			$get_play	= $ret[0];
		}
		$menu[DISPLAY_TRASH]->active			= true;
	}
	elseif (isset($GET["undo"]))
	{
		// Si option "undo",  la vidéo est ses fichiers sont RE-placés dans ./medias/
		clearstatcache();
		$src_dir	= $myCFG->get("trash_dir");
		$dst_dir	= $myCFG->get("medias");
		$bs		= basename($GET["undo"], $myCFG->get("medias_ext"));
		$source	= sprintf("%s.*", $bs);
		#$dst	= $myCFG->get("trash_dir");
		#$dir	= new FileDir($myCFG->get("medias"));
		$dir	= new FileDir($myCFG->get("trash_dir"));
		$ret	= $dir->get2array($source);

		if (count($ret) >= 1)
		{
			print("<!-- UNDO section\n");
			foreach ($ret as &$name)
			{
				if( !rename( $src_dir.$name, $dst_dir.$name) )
				{
					printf("mv %s, %s = %d<br/>\n", $src_dir.$name, $dst_dir, $return);
					array_push($err, sprintf("rename en echec de %s %s", $src_dir.$name, $dst_dir.$name) );
				}
				else
				{
					array_push($informations, sprintf("rename réussi de %s %s", $src_dir.$name, $dst_dir.$name) );
				}
			}
			print(" UNDO section end -->\n");
		}
		$tag_meta[]		= $meta_backPage;
		$tag_script[]	= $js_backPage;
	} # rename to trash dir
	elseif (isset($GET["trash"]))
	{
		$menu[EMPTY_TRASH]->active		= true;
		$menu[DISPLAY_VIDEO]->active	= true;

		if ( strcasecmp($GET["trash"], "display") == 0 )
		{
			$dir_medias	= $myCFG->get("trash_dir");
			$addr_video	= $myCFG->get("addr_video_trash");
			$dir	= new FileDir( $dir_medias );
			$tag_script[]	= sprintf("
		// setInterval(function() { location.reload(); }, 60000); // Rafraichi la page toutes les (60 seconds)*1000 !
");
		} # display trash dir
		else
		{
			// Si option "trash" la vidéo est ses fichiers sont placés dans ./medias/trash/
			clearstatcache();
			if ( !file_exists( $myCFG->get("trash_dir") ) ) { mkdir( $myCFG->get("trash_dir"), 0755, false);}
			$bs		= basename($GET["trash"], $myCFG->get("medias_ext"));
			$source	= sprintf("%s.*", $bs);
			$dst	= $myCFG->get("trash_dir");
			$dir	= new FileDir($myCFG->get("medias"));
			$ret	= $dir->get2array($source);
			if (count($ret) >= 1)
			{
				print("<!-- TRASH section\n");
				foreach ($ret as &$name)
				{
					if( !rename( $myCFG->get("medias").$name, $dst.$name) )
					{
						printf("mv %s, %s = %d<br/>\n", $myCFG->get("medias").$name, $dst, $return);
						array_push($err, sprintf("rename en echec de %s %s", $myCFG->get("medias").$name, $dst.$name) );
					}
					else
					{
						array_push($informations, sprintf("rename réussi de %s %s", $myCFG->get("medias").$name, $dst.$name) );
					}
				}
				print(" TRASH section end -->\n");
			}
			$tag_meta[]		= $meta_backPage;
			$tag_script[]	= $js_backPage;
		} # rename to trash dir
				$tag_script_validation[]	= sprintf(
<<<END
    <script>
			// On change le nom du style quand on est dans la gestion de la corbeil.
			var divElement = document.getElementById("surveillance");
			// Remplacez la classe existante par une nouvelle classe
			divElement.className = "surveillance_trash";
    </script>
END
				);
	}
	elseif (isset($GET["del"]))
	{
		$menu[DISPLAY_VIDEO]->active	= true;

		$dir_medias	= $myCFG->get("trash_dir");
		$addr_video	= $myCFG->get("addr_video_trash");
		$dir		= new FileDir($dir_medias);

		if (strcasecmp($GET["del"], "all") == 0 )
		{
			# Validation reçue, on efface tout !
			if (isset($GET["aswner"]) && strcasecmp($GET["aswner"], $myCFG->lang->_("yes")) == 0)
			{
				$ret			= $dir->get2array(".*");
				if (count($ret) >= 1)
				{
					print("<!-- DELETE ALL section\n");
					foreach ($ret as &$name)
					{
						if ( ! ($name == "." || $name == "..") )
						{
							printf("fichier '%s'\n", $name);
							$file			= new FileDir($dir_medias."/".$name);
							$file->verbose	= true;
							$file->real		= true;
							$return			= $file->Del();
							if( ! $return )
							{
								foreach ($file->stderr->get as $name)
								{
									printf("status: %s\n", $name);
								}
								foreach ($file->status() as $name)
								{
									printf("status: %s\n", $name);
								}
							}
						}
					}
					print(" DELETE ALL section end -->\n");
				}
				$meta_backPage	= sprintf("

	<meta http-equiv=\"refresh\" content=\"3; url=%s\"/>
", $menu[DISPLAY_VIDEO]->url);

				$js_backPage	= sprintf(
						<<<END
// Code del=all&aswner=yes
  window.onload = function() {
	setTimeout(function() {
	window.open( "%s" , "_self");
      //history.back();
    }, 500);
  };
END
, $menu[DISPLAY_VIDEO]->url);

				$tag_meta[]		= $meta_backPage;
				$tag_script[]	= $js_backPage;

			}
			else
			{
				# Affichage de la demande de validation pour tout effacer.

				# asnwer != yes  OR  undef
				$tag_script_validation[]	= sprintf(
<<<END
    <script>

        // Bouton : OK Confirmer effacement.
		document.getElementById("id_validation").addEventListener("click", function()
		{
            // Ouvrez une nouvelle fenêtre contextuelle avec l'URL souhaitée
			// console.log(" Validé: " + window.location.href + "&aswner=yes");
            window.open( window.location.href + "&aswner=yes", "_self");
		});

		window.onload = function()
		{
			const myPopup = document.getElementById('list');
			myPopup.style.display = 'none';

			// On chnage le nom du style quand on est dans la gestion de la corbeil.
			var divElement = document.getElementById("surveillance");
			// Remplacez la classe existante par une nouvelle classe
			divElement.className = "surveillance_trash";

           	// Boutton : Annuler, refuser effacement
			document.getElementById("id_cancel").addEventListener("click", function()
			{
				const	element	=  document.getElementById("id_cancel") ;
           		window.open( element.getAttribute("href"), "_self"); // Remplacez par l'URL de la page souhaitée
			});
		};
    </script>
END

);
			}
		}
		else
		{
			printf("<!-- DELETE option -->\n");
			$ret		= $dir->get2array($GET["del"]);
			if (count($ret) >= 1)
			{
				foreach ($ret as &$name)
				{
					printf("<!-- unlink(%s) \n", addslashes(strip_tags($name)) );
					$file			= sprintf("%s", addslashes(strip_tags($name)) );
					$file			= new FileDir($dir_medias."/".$file);
					$file->verbose	= true;
					$return			= $file->Del();

					if ( ! $return ) # si erreur, on affiche status....
					{
						foreach ($file->stderr->get as $name)
						{
							printf("status: %s\n", $name);
						}
						foreach ($file->status() as $name)
						{
							printf("status: %s\n", $name);
						}
					}
					printf("-->\n");
					$file->stderr->reset();
					unset($file);
				}
			}
			$tag_meta[]		= $meta_backPage;
			$tag_script[]	= $js_backPage;
		}
	}
	else
	{
		$dir	= new FileDir( $dir_medias );
		printf("<!-- Pas d'option dirmedia='%s ' -->\n", $dir_medias);
		$menu[DISPLAY_TRASH]->active			= true;
	}









	#########################
	###   Header + HTML   ###
	#########################






header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
?>
<html lang="en">
	<head>
	<title><?php print $myCFG->lang->_("Management of video sequences captured by a camera"); ?></title>
<?php
	if (count($tag_meta) >= 1) { foreach ($tag_meta as $line) { printf("%s\n", $line); } }
?>
	<meta charset="utf-8">
		<!--link rel="stylesheet" href="css/general.css" media="screen" id="general" /-->
		<link rel="stylesheet" href="css/mosaic.css?<?php print $myCFG->get("time_cache");?>" media="screen">
		<style media="all">
<?php
	$css	= new Option(".".$myCFG->get("css_thumb_name"), $myCFG->get("css"));
	$css->doctype("css");
		print $css->get_opt();
		print $css->close_opt();
?>
		</style>
		<script>
<?php
	if (count($tag_script) >= 1) { foreach ($tag_script as $line) { printf("%s\n", $line); } }
?>		</script>
	</head>

	<body>

<div id="motd" class="motd">
	<div class="informations">
	<?php
		if (count($err) >= 1 || count($informations) >= 1)
		{
			$P_err		= new Option("p", [ "id" => "informations"] );
			$ul_info	= new Option("ul", [ "id" => "informations"] );
			foreach ($err as &$name)
			{
				printf("	%s%s%s\n", $P_err->get_opt(), $name, $P_err->close_opt());
			}
			foreach ($informations as &$name)
			{
				printf("	%s%s%s\n", $ul_info->get_opt(), $name, $ul_info->close_opt());
			}
		}
?>
	</div><!-- class="informations" -->
</div><!-- id="motd" -->
<?php

	function video_info($info="")
	{
		global $p, $img, $a, $br, $span, $url_medias;
		global $myCFG, $GET, $dir_medias;


		$buf	= array();
		$duration	= "??.??";
		$uri		= "unknow";
				print $p->get_opt();
				if ($info["video_name"])
				{
					$file		= $info["video_path"].$info["video_name"];
					$file_json	= $info["video_path"].$info["video_name"].".json";
					if ( file_exists($file_json) )
					{
						$fh		= new FileHandle( [ "file"	=> $file_json, "mode"	=> "r" ] );
						if ($fh)
						{
							$json	= $fh->read(-1);
							if (strlen($json) >= 0)
							{
								#$buf	= json_decode( $json, true )[0]; # OK1
								$buf	= json_decode( $json, true ); # OK2

								foreach ($buf as $key2 => $value2) # OK2
								{
									foreach ($value2 as $key1 => $value1)
									{
										if ($key1 == $info["video_name"])
										{
											$info	= array_merge($info, $value1);
										}
									}
								}
							}
							else
							{
								printf($myCFG->lang->_("err_read_json"), $info["video_name"].".json");
							}
							unset($fh);
						}
						$duration	= $info["duration"]?$info["duration"]:"??.??";


						if ($duration) { printf($myCFG->lang->_("duration"), $duration); print $br->get_opt();}
					}

					$uri	= $info["video_name"]?$info["video_name"]:"";
					#$img->chvar("src", $myCFG->get("url_medias").$info["video_name"].".jpeg");
					$a->chvar("href", "?play=".$uri);
					print $a->get_opt();
						#print $img->get_opt();
						print $span->get_opt();
						printf("%s", $info["video_name"]);
						print $span->close_opt();
					print $a->close_opt();

					print $br;
					if (isset($GET['trash']) && strcasecmp($GET['trash'], "display") == 0)
					{
						# it's Display
						$a->chvar("href", "?del=".$uri);
						$a->chvar("title", $myCFG->lang->_("Permanently delete the document"));
						print $a->get_opt();
						printf ($myCFG->lang->_("to_delete"));
						print $a->close_opt();
					print $br;

						# Undo
						$a->chvar("href", "?undo=".$uri);
						$a->chvar("title", $myCFG->lang->_("Restore Document"));
						print $a->get_opt();
						printf ($myCFG->lang->_("undo"));
						print $a->close_opt();
					print $br;
					}
					else
					{
						# it's not Display
						$a->chvar("href", "?trash=".$info["video_name"]);
						$a->chvar("title", $myCFG->lang->_("Place this document in the trash"));
						print $a->get_opt();
						print ($myCFG->lang->_("trash"));
							#print $span->get_opt();
							#printf("%s", $info["video_name"]);
							#print $span->close_opt();
						print $a->close_opt();
					print $br;
					}
				}
				print $p->close_opt();
	}




	# <div de l'ensemble id="surveillance">
	$ret	= array();
	$ret	= $dir->get2arrayTime($myCFG->get("dir_regex"));

	$pgt->chvar("class", $myCFG->get("css_projet_name"));
	$pgt->chvar("id", "surveillance");
	$pgt->mark($myCFG->get("css_projet_name"));
	print $pgt->get_opt();





				############
				### MENU ###
				############




	$css_menu		= $myCFG->get("css_menu");
	$class_menu		= $myCFG->get($css_menu);
	$div_menu		= new Option("div", $class_menu);
	$a_menu			= new Option("a", $class_menu);

	$div_menu->mark($css_menu);
	$a_menu->delvar("id");
	print $br;
	print $div_menu;

	# Affichage du menu par ordre croissant.
	if (count($menu) >= 1)
	{
		ksort($menu);
		foreach ($menu as $array)
		{
			if ($array->active)
			{
						$a_menu->chvar("href", $array->url);
						print $a_menu->get_opt();
						print $array;
						print $a_menu->close_opt();
			}
		}
	}

	if ( isset($GET['del']) && strcasecmp($GET['del'], "all") == 0)
	{
		print $br;
		$css_menu_btn	= $myCFG->get("css_menu_btn");
		$class_menu_btn	= $myCFG->get($css_menu_btn);
		$div_menu_btn	= new Option("div", $class_menu_btn);
		$div_menu_btn->mark($css_menu_btn);
		print $div_menu_btn;
		print("\n");

		# reste à vérifier ca, syntaxe.

		print $myCFG->lang->_("WARNING_raz_trash");
		print $br;
		printf("<button id=\"id_validation\" type=\"button\" href=\"%s\" target=\"_self\">%s</button>\n",
				$menu[DISPLAY_VIDEO]->url,
				$myCFG->lang->_("yes_empty_the_trash") );
		printf("<button id=\"id_cancel\" type=\"button\" href=\"%s\" target=\"_self\">%s</button>\n",
				$menu[DISPLAY_TRASH]->url,
				$myCFG->lang->_("cancel"));
		print("\n");
		print $div_menu_btn->close_opt();
		print("\n");

	}
	print $div_menu->close_opt();
	print("\n");
	$div_menu->delvar("class");
	$div_menu->delvar("id");
	$div_menu->unmark($class_menu);
	$a_menu->delvar("class");

	if (count($tag_script_validation) >= 1) { foreach ($tag_script_validation as $line) { printf("%s\n", $line); } }

	# div id=list afin de pouvoir faire disparaire son contenu dès que les boutons sont affichés
	$list->chvar("id", "list");
	$list->chvar("style", "");
	$list->mark("list");
	print "\n".$list."\n";






	#############################
	### DISPLAY Mosaic videos ###
	#############################

	if ( count($ret) >= 1 )
	{
		$videoinfo	=array();

		if ( $get_play != "" )
		{
			$title	= $get_play;
		}
		else
		{
			$title	= $ret[0]; # le premier éléments est celui par défault à affiche en premier, dans <div=play>
			unset($ret[0]); # On vire le premier élément qui devient la vidéo à lire par défaut.
		}

		# <div du css "play" >
		$div->chvar("id", $myCFG->get("css_play_name"));
		$div->chvar("class", $myCFG->get("css_play_name"));
		$div->mark($myCFG->get("css_play_name"));
		print $div->get_opt();
			# <video par defaut>
			$vid->chvar("width", $myCFG->get("vid_sz_width"));
			print $vid;
				$src->chvar("class", $myCFG->get("css_source"));
				$src->chvar("src", $addr_video.$title);
				print $src;
			print $vid->close_opt();
			print("\n");

				# Information textuelle sur la vidéo
				$videoinfo["video_name"]	= $title;
				$videoinfo["video_path"]	= $dir_medias;

				##########################
				### Affichage du model ###
				##########################

				video_info($videoinfo);


					#printf("VID: %s\n", $title);
		# </ div du css "play" >
				#
		$div->mark($myCFG->get("css_play_name"));
		print $div->close_opt(); # Premier DIV: id=mosaic
		$div->unmark();

		$videoinfo	=array();
		#$mos->chvar("width", "640");
		#$mos->chvar("height", "100%");
		print $mos->get_opt()."\n";
		#$div->chvar("id", $myCFG->get("css_thumb_name"));
		$div->delvar("id");
		$div->chvar("class", $myCFG->get("css_thumb_name"));
		$vid->chvar("width", $myCFG->get("vid_sz_thumb"));
		$vid->delvar("autoplay");
		foreach ($ret as $key => $value)
		{
			$videoinfo	= array();

			if ($title != $value)
			{
				print $div->get_opt();
				#print $p->get_opt();
					#$img->chvar("src", $myCFG->get("url_medias").$value.".jpeg");
					$img->chvar("src", $addr_video.$value.".jpeg");
					$img->chvar("alt", $addr_video.$value.".jpeg");
					$a->chvar("href", "?play=".$value);
					print $a->get_opt();
						print $img->get_opt();
					print $a->close_opt();

					$videoinfo["video_name"]	= $value;
					$videoinfo["video_path"]	= $dir_medias;
				##########################
				### Affichage du model ###
				##########################
					video_info($videoinfo);

				#print $p->close_opt();
			print $div->close_opt();
			printf("\n");
			} # if $title != $value
		}
		unset($title);
		unset($videoinfo);
		$mos->mark("mosaic");
		print $mos->close_opt();
		$mos->unmark();
	}
	else
	{
		printf("Aucunes occurences trouvées\n");
	}
	print $list->close_opt();
	print $pgt->close_opt();
	# </ div de l'ensemble >
?>
<br>
	<script>
        // Détecter la langue du navigateur
        var lang = navigator.language || navigator.userLanguage;
        lang = lang.substring(0, 2); // Récupérer les deux premiers caractères (par exemple, "fr" ou "en")

		// Détecter la langue du navigateur
		var lang = navigator.language || navigator.userLanguage;
		lang = lang.substring(0, 2); // Récupérer les deux premiers caractères (par exemple, "fr" ou "en")

		// Récupérer toutes les sections avec l'attribut "lang"
		var sections = document.querySelectorAll('section[lang]');

		// Parcourir toutes les sections et afficher celle qui correspond à la langue détectée
		for (var i = 0; i < sections.length; i++)
		{
			if (sections[i].getAttribute('lang') === lang)
			{
        		sections[i].style.display = 'block';
    		} else {
        		sections[i].style.display = 'none';
    		}
		}


		// Gestion d'affichage/mascage des description des menus H1, H2 et H3
        function toggleStyle(id) {
            var elements = document.querySelectorAll('[id="' + id + '"]');
            elements.forEach(function (element) {
                if (element.style.display === 'none' || element.style.display === '') {
                    element.style.display = 'block';
                } else {
                    element.style.display = 'none';
                }
            });
        }
	</script>
</body>
</html>
