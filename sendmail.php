<?php
require_once("config/autoload.php");


# sample launch :
# sudo -u www-data -g www-data php ./sendmail.php -- MovieStart file=/var/www/html/surveillance/medias//01-20220927144300.mkv date=20220927 heure=14:43:00 frame=01 event=xx

	$ret		= array();
	$arg		= new Argv();

	if ( isset( $_SERVER["argv"]) OR isset( $_SERVER["SHELL"] )  OR count($_GET) )
	{
		printf("Option shell\n");

		if ( count( $_GET) )
		{
			# Si c'est lancé depuis le serveur
			$arg->method("http");
			$arg->analyse( $_GET );
				#					"method"	=> "http",
				#					"list"	=> $_GET
				#			]);
		}
		else
		{
			# Lancé depuis un Shell
			$arg->method("unix");
			$arg->analyse( $argv );
			#$arg	= new Argv([	"method"	=> "unix",
			#						"list"	=> $argv,
			#					]);
		}
		$file	= $arg->get("file");

		if ( $file ) #eq != false )
		{
			printf("fichier: %s'\n", $file);
			$ret[]	= $file;
		}
		else
		{
			$dir	= new FileDir($medias);
			$ret	= $dir->get2arrayTime("*\.mkv");
			$file	= " file not found!!! see your log server !!!!";
		}
	}
	else
	{
		#print_r($_SERVER);
		die("mode d'exécution non prévu, abandon du lancement\n");
		$dir	= new FileDir($medias);
		$ret	= $dir->get2arrayTime("*\.mkv");
	}

	if ($myCFG->get("mail_send"))
	{
		$to			= $myCFG->get("mail_to");
		$subject	= sprintf("ALARM déclenchée le %s à %s", $arg->get("date"), $arg->get("heure") );
		$message	= sprintf($myCFG->lang->_("mail_text_alarm"), $myCFG->get("url_mosaic")."?play=".basename($arg->get("file")) );
		$from		= $myCFG->get("mail_from");

		$headers	= "From: $from";
		$mail		= mail($to,$subject,$message,$headers);
		#echo "Mail Sent.";
	}


