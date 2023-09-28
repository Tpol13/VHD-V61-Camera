<!DOCTYPE html>
<?php

/*
 * Outils pour référencer les vidéos produits par Motion suite à une détection de mouvement.
 * Ce programme 
 		* récupère des informations
			* calcule les nombres de frames,
			* dates et heures (horodatage)
			* Première photos de la vidéo afin de l'utiliser comme image lors de mosaic.php.
		* Déplace les vidéos dans le bon dossier


* Reste à faire :

	-- voir avec : ffmpeg -i 15-20220917164201.mkv -vframes 1 -f image2 /tmp/image.jpg

	Ajouter un test SI *.jpeg existe, ne pas créer. passer à la suivante.

 */

# VERSION 20230820


require_once("config/autoload.php");



if (0)
{
$lang	= new Lang([ "list" =>
						array_merge([
							"lag=fr_FR",
							"domain=".$myCFG->get("PROJECT_NAME"),
									],
							$argv,
						),
					"myCFG"	=> $myCFG,
					"method"	=> "unix",
					"level"		=> 99,
					]);
$dbg	= new Debug([ "level" => "1" ]);
}
else
{
$lang	= &$myCFG->lang;
$dbg	= &$myCFG->dbg;
}

#  Translat
# https://www.codeandweb.com/babeledit/tutorials/translation-with-gettext-and-php


# les 2 lignes sont pour ffmpegPHP
require_once 'libs/ffmpeg-php-master/vendor/autoload.php';
use Char0n\FFMpegPHP\Movie;


	# Détermine si execution depuis un Shell ou serveur http
	# Shell :
	# 	./$0 file=video.ext
	# 	./$0 directory=path/   (or dir=path/)
	#
	# Http :
	# 	_GET[file] contient la video à traiter
	# 	_GET[directory] est INTERDIT, et donc ignoré.
	#
	# Si aucune option n'est détecetée, le dossier $medias et scanné et traité.

	$ret		= array();
	#$myCFG->argv		= new Argv();
	$myCFG->argv		= $myCFG->Argv;

	if ( isset( $_SERVER["argv"]) OR isset( $_SERVER["SHELL"] )  OR count($_GET) )
	{

		if ( count( $_GET) )
		{
			$myCFG->argv->method("http");
			$myCFG->argv->analyse( $_GET );
			$myCFG->dbg->init( $myCFG->argv->list);
			#$argv	= new Argv([	
				#					"method"	=> "http",
				#					"list"	=> $_GET
				#			]);
		}
		else
		{
			$dbg->verbose(1, "Option shell");
			$myCFG->argv->method("unix");
			$myCFG->argv->analyse( $argv );
			#$argv	= new Argv([	"method"	=> "unix",
			#						"list"	=> $argv,
			#					]);
		}
		$file		= $myCFG->argv->get("file");
		$directory	= $myCFG->argv->get("directory");

		# En l'absence de dir= ou directory= $médias est le chemin par défaut du scan.
		if ( !$directory )
		{
			$directory	= $myCFG->argv->get("dir");
			if ( !$directory )
			{
				$directory	= $myCFG->get("medias");
			}
			else
			{
				#printf($lang->_(""));
			}
		}

		if ( $file ) #eq != false )
		{
			$dbg->verbose(1,  $lang->_('single_req_file'), $file );
			$ret[]	= $file;
		}
		else
		{
			$dbg->verbose(1,  $lang->_('method_active'), $file );
			$dir	= new FileDir($directory);
			$ret	= $dir->get2arrayTime( $myCFG->get("dir_regex") );
			printf("dir '%s'\n", $directory);
		}
	}
	else
	{
		#print_r($_SERVER);
		die("non prévu\n");
	}




	video2image( $ret );


	function video2image( $list )
	{
		global $arg, $dbg, $lang, $myCFG;

		if ( count($list) >= 1 )
		{
			foreach ($list as $key => $value)
			{
				$info_video	= array();
				$video_file	= $value;
				$fh			= new FileHandle(
											[
												"mode"  => "w"
											]);

				if ( !file_exists($video_file))
					$video_file	= $myCFG->get("medias").$value;

				# FFMPEG
				$dbg->verbose(1, $lang->_('process_video'), $video_file );

				$movie = new Movie( $video_file );
				$info_video["duration"]		= $movie->getDuration();
				$dbg->verbose(1,  $lang->_('duration'), $info_video["duration"] );

				#$videoFrames = $movie->getFrameCount();
				$info_video["Frames"]		= $movie->getFrameCount();
				$dbg->verbose(1,  $lang->_('she_has_number_img'), $info_video["Frames"] );

				$videoImage = $movie->getFrame(1); #$videoFrames/2);
				$info_video["Largeur"]		= $videoImage->getWidth();
				$info_video["Hauteur"]		= $videoImage->getHeight();
				$dbg->verbose(1,  $lang->_('size_video'), $info_video["Largeur"], $info_video["Hauteur"] );

				$img = $videoImage->toGDImage();

				$info_video["thumb"]	= $video_file.'.jpeg';
				if ( !file_exists( $info_video["thumb"]) OR $myCFG->argv->get("force") )
				{
					$dbg->verbose(1,  $lang->_('convert2jpeg_name'), $info_video["thumb"] );
					imagejpeg($img, $info_video["thumb"]);
				}
				else
				{
					$dbg->verbose(1,  $lang->_('no_creation') );
				}

				$list_videos[]	= [ basename("$video_file")	=> $info_video ];

				# JSON stockage au format JSON
				$file_json		= $video_file.'.json';
				$fh->open($file_json);
				$json			= json_encode($list_videos);
				if ( $fh->write($json) != strlen($json))
				{
					$dbg->verbose(1,  $lang->_('err_write_json'), $file_json );
				}
				unset($fh);
				unset($list_videos); # si on vire ce unset, toutes les vidéos scutées seront dans le .json courant...
				#var_dump($list_videos);

				# XML version
				$xmlstr	= <<< XML
		<sequence file="">
			<thumb></thumb>
			<date></date>
			<time></time>
			<duration></duration>
			<frames></frames>
			<width></width>
			<weight></weight>
		</sequence>
XML;
				$xml	= new SimpleXMLElement($xmlstr);

				$xml["file"]		= basename("$video_file");
				$xml[0]->thumb		= basename($info_video["thumb"]);
				$xml[0]->duration	= $info_video["duration"];
				$xml[0]->frames		= $info_video["Frames"];
				$xml[0]->width		= $info_video["Largeur"];
				$xml[0]->weight		= $info_video["Hauteur"];

				$timestamp			= filemtime($video_file);
				$date				= date('Ymd', $timestamp);
				$heure				= date('H:i:s', $timestamp);
				$xml[0]->time		= $myCFG->argv->get("heure")?$myCFG->argv->get("heure"):date('H:i:s', $timestamp);
				$xml[0]->date		= $myCFG->argv->get("date")?$myCFG->argv->get("date"):date('Ymd', $timestamp);

				$xml_out	= new FileHandle( [ "file"      => $video_file.".xml", "mode"   => "w" ] );
				if ($xml_out->isOpen())
				{
					$return		= $xml_out->var2file($xml->asXML() );
					if ( $return)
					{
						$return		= $xml_out->close();
						$dbg->verbose(99,  $lang->_('return_of_xmlClose'), $return );
					}
					else
					{
						$dbg->verbose(1,  $lang->_('err_write_file'), $video_file );
						$dbg->verbose(1,  $lang->_('err_msg_return'), $xml_out->err );
					}
				}
				else
				{
					$dbg->verbose(1,  $lang->_('err_opening_file'), $xml_out->name_file );
					$dbg->verbose(1,  $lang->_('err_msg_return'), $xml_out->err );
				}
			}
		}
		else
		{
			$dbg->verbose(1,  $lang->_('no_occurences_found') );
		}
	}


?>
<?php
?>
