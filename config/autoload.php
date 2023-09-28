<?php

global $argv, $myCFG;


$configuration	= array();
$cfg			= &$configuration;

#time_cache sert à contourner le pb de cache des navigateurs
# un CTRL+R or F5 ne permet pas forcément aux css d'être rechargés.
# avec time_cache, lors du developement du code,  dans l'url cela règle le pb...
$cfg["time_cache"]		 = time();


# Information de base
$cfg["server_ip"]			= "192.168.3.98";
$cfg["camera_ip"]			= "192.168.3.88";

$cfg["mail_to"]				= "login@domain.tdl";
$cfg["mail_from"]			= "login+alarm@domain.tdl";
$cfg["mail_send"]			= true; # true = sendmail ok, false, not send...

# FILES && DIRECTORYS
$cfg["PROJECT_NAME"]		= "surveillance";
$cfg["www_dir"]				= "/var/www/html";
$cfg["home_dir"]			= $cfg["www_dir"] . "/" . $cfg["PROJECT_NAME"];
$cfg["medias_ext"]			= "mkv";
#$cfg["medias_ext"]			= "mp4";
$cfg["url_medias"]			= "medias/";
$cfg["medias"]				= $cfg["home_dir"]."/".$cfg["url_medias"];
$cfg["trash_dir"]			= $cfg["medias"]."trash/";
$cfg["dir_regex"]			= ".*\.".$cfg["medias_ext"]."$";

# Locales
$cfg["locales_dir"]			= $cfg["home_dir"]."/"."locales";


# Motion from team: motion-project
# Pour affichage de la vidéo au format MJPEG
$cfg["motion_method"]		= true;
$cfg["motion_port_stream"]	= "8082";
$cfg["motion_cam1"]			= "http://".$cfg["server_ip"].":".$cfg["motion_port_stream"]."/"."0/stream/video.mjpeg";


# HLS / RTMP
$cfg["hls_method"]			= false; # true si vous utilisez un serveur HLS. par example avec nginx....
$cfg["hls_name_stream"]		= "/stream/hls/stream0.m3u8";
$cfg["hls_url_stream"]		= "http://".$cfg["server_ip"].$cfg["hls_name_stream"];

# Webcam
$cfg["camera1"]				= "http://".$cfg["camera_ip"]."/1";

# Mosaic URL/URI - médias && trash
$cfg["url"]					= "http://".$cfg["server_ip"]."/".$cfg["PROJECT_NAME"];
$cfg["url_mosaic"]			= "http://".$cfg["server_ip"]."/".$cfg["PROJECT_NAME"]."/mosaic.php";
$cfg["addr_video"]			= "http://".$cfg["server_ip"]."/".$cfg["PROJECT_NAME"]."/".$cfg["url_medias"];
$cfg["addr_video_trash"]	= $cfg["addr_video"]."trash/";

$cfg["motion_cam1_err"]		= $cfg["url"]."/"."gplv3-with-text-136x68.png";

# CSS styles
$cfg["vid_sz_width"]		= "640";
$cfg["vid_sz_height"]		= "360";
$cfg["vid_sz_thumb"]		= "320";
$cfg["css_projet_name"]		= $cfg["PROJECT_NAME"];
$cfg["css_play_name"]		= "play";
$cfg["css_play_width"]		= "100%";
$cfg["css_mosaic_name"]		= "mosaic";
$cfg["css_mosaic_width"]	= $cfg["vid_sz_width"]."px";
$cfg["css_mosaic_height"]	= $cfg["vid_sz_height"]."px";
$cfg["css_thumb_name"]		= "videomp4";
$cfg["css_thumb_width"]		= $cfg["vid_sz_thumb"]."px";
$cfg["css_menu"]			= "menu";
$cfg["css_menu_btn"]		= "menu_btn";
$cfg["css_source"]			= "source";

$cfg["video"]		= [
		'controls'	=> true,
		'width'		=> 250,
		'autoplay'	=> true,
		'loop'	=> true
	];
$cfg["source"]		= [
		'src'		=> "",
		'type'		=> "video/mp4",
	];
$cfg["div_default"]	= [
		'id'		=> $cfg["css_mosaic_name"],
		'class'		=> $cfg["css_mosaic_name"]
	];
$cfg["p_default"]	= [
		'class'		=> $cfg['css_thumb_name']
	];
$cfg["a_default"]	= [
		'class'		=> $cfg['css_thumb_name'],
		'href'		=> $cfg['css_thumb_name']
	];
$cfg["img_default"]	= [
		'class'		=> $cfg['css_thumb_name'],
		'src'		=> $cfg['css_thumb_name']
	];
$cfg["css"]			= [
			'width'	=> $cfg['vid_sz_thumb']."px"
	];
$cfg["menu"]		= [
		'id'		=> $cfg["css_menu"],
		'class'		=> $cfg["css_menu"]
	];
$cfg["menu_btn"]	= [
		'id'		=> $cfg["css_menu_btn"],
		'class'		=> $cfg["css_menu_btn"]
	];



# les 3 lignes sont pour ffmpegPHP
#require_once(__DIR__.'/../libs/ffmpeg-php-master/vendor/autoload.php');
require_once($configuration["home_dir"].'/libs/ffmpeg-php-master/vendor/autoload.php');
use Char0n\FFMpegPHP\Movie;
use Char0n\FFMpegPHP\Frame;
#use Char0n\FFMpegPHP\Adapters\FFMpegMovie;
#use Char0n\FFMpegPHP\Adapters\FFMpegFrame;

# Load the intl library
# pour new MessageFormatter('fr_FR', array(
#require_once 'vendor/autoload.php';

/*
 * Reste à faire :

 */

spl_autoload_register(function($className)
{
	global $configuration;

	// Vérifiez si la classe existe déjà.
	if (class_exists($className)) {
		return;
	}

	// Déterminez le chemin du fichier de classe.
	#$path = __DIR__ . '/../class/' . $className . '.class.php';
	$path = $configuration["home_dir"] . '/class/' . $className . '.class.php';

	// Chargez le fichier de classe.
	require_once( $path);
});


# Méthode temporaire.... en attente d'un convertion de Config3.pm en PHP...
$myCFG			= new myCFG($configuration);

# Gestion arguements passés par shell
$myCFG->Argv	= new Argv([ "list" => $argv, "method" => "unix"]);
$myCFG->Argv->set("domain", $myCFG->get("PROJECT_NAME"));

# Language
$myCFG->lang	= new Lang([ "list" => $myCFG->Argv->list,
							"myCFG"	=> &$myCFG,
							"method"	=> "unix",
							]);
$myCFG->dbg		= new Debug( $myCFG->Argv->list );



#require_once("models/javascript.js");


if (file_exists( $configuration["home_dir"] . "/config/personal.php") )
{
	require_once( $configuration["home_dir"] . "/config/personal.php" );
}

?>
