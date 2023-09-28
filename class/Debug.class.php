<?php

# VERSION: 20230821


global $argv;
if ( isset($argv[0]) && strpos($argv[0], "Debug.class.php") != false ) { Debug::test(); }

class Debug
{
	public		$level		= 0;
	public		$endline	= "\n";
	public		$prefix		= "";
	public		$log_file	= "/tmp/debug.log";

	function Debug($args=array())
	{
		return(__construct($args));
	}

	function __construct($args=array())
	{
		#$this->myCFG	= new myCFG($args);
		$this->myCFG	= new myCFG();
		$this->init($args?$args:$argv); # $argv par défaut si rien n'est précisé...
		return($this);
	}

	function __destruct()
	{
	}

	function init($args)
	{
		$this->endline($this->endline);
		$this->level($this->level);
		$this->log_file($this->log_file);
		$this->prefix($this->prefix);

		if (isset($args["endline"]))	{ $this->endline($args["endline"]); }
		if (isset($args["level"]))		{ $this->level($args["level"]); }
		if (isset($args["log_file"]))	{ $this->log_file($args["log_file"]); }
		if (isset($args["prefix"]))		{ $this->prefix($args["prefix"]); }

		return($this);
	}

	function verbose($lvl=0, $string="", ...$args)
	{
		if ($lvl <= $this->level)
		{
			$string	= sprintf("%s%s", $this->prefix, $string);
			$buf	= sprintf($string, ...$args);
			printf("%s%s", $buf, $this->endline);
		}
	}

	function log($string = "", ...$args)
	{
		$file = $this->log_file();

		if ( isset($file) )
		{
			# $this->prefix peut contenir des %s,  %.*, ...  donc on l'ajoute à $msg
			# afin que le second sprintf puisse traiter les %...
			# <!> Donc penser à ajouter les options contenues dans this->prefix dans &verbose("texte", opt_prefix, opt_other...)
			$msg = sprintf("%s%s", $this->prefix(), $string);
			

			if ( count($args) >= 1 )
			{
				$msg	= sprintf($msg, ...$args);
			}

			return(printf("%s%s", $msg, $this->endline));
			if (is_resource($handle = fopen($file, "a")))
			{
				$date = date("Y/m/d H:i:s");
				fwrite($handle, sprintf("[%s] %s%s", $date, $msg, $this->endline()) );
				fclose($handle);
			}
			else
			{
				$this->verbose(1, "!!: Error open write file '$file' $!\n");
			}
		}
	}

	####################
	# Variable Set/Get #
	####################

	function log_file($log_file=false)
	{
		return($this->model_myCFG_set("log_file", $log_file));
	}


	function endline($endline=false)
	{
		return( $this->endline = $this->model_myCFG_set("endline", $endline) );
	}

	function level()
	{
		if (func_num_args() >= 1)
		{
			$lvl	= func_get_arg(0);
			if (is_numeric($lvl))
			{
				$this->level	= $this->model_myCFG_set("level", $lvl);
			}
		}

		return($this->level);
	}

	function prefix($prefix=false)
	{
		return( $this->prefix = $this->model_myCFG_set("prefix", $prefix));
	}

	function model_myCFG_set($variable="", $value=false)
	{
		if ($value != false)
		{
			$this->myCFG->set($variable, $value);
		}

		return($this->myCFG->get($variable));
	}

	###########
	# Testing #
	###########
	function test()
	{
		require_once("config/autoload.php");
		$stderr	= array();
		printf("Mode test\n");
		$dbg	= new Debug([
								"prefix"	=> "<end>",
								"level"		=> 3,
								"log_file"	=> "/tmp/log_Debug.log",
								"endline"	=> "</end>\n",
							]);
		#var_dump("TEST()", $dbg->myCFG->list);
		if ( $dbg->myCFG->get('pefix') )
		{
			$stderr	.= sprintf("WARNING: &Debug::test(): La création de l'instance new Debug() est en erreur, elle créé une valeur non autorisée\n");
		}
		if ( $dbg->level() != 3)
		{
			$stderr[]	= sprintf("WARNING: &Debug::test(): &level() ne renvoie pas la valeur attendue\n");
		}
		if ( $dbg->log_file() != "/tmp/log_Debug.log" )
		{
			$stderr[]	= sprintf("WARNING: &Debug::test(): &log_file() ne renvoie pas la valeur attendue\n");
		}
		if ( $dbg->prefix() != "<end>")
		{
			$stderr[]	= sprintf("WARNING: &Debug::test(): &prefix() ne renvoie pas la valeur attendue\n");
		}
		if ( $dbg->endline() != "</end>\n")
		{
			$stderr[]	= sprintf("WARNING: &Debug::test(): endlibe() ne renvoie pas la valeur attendue\n");
		}
		printf("* Level debugging is : '%d'\n", $dbg->level(9));
		printf("* Prefix   is : '%s'\n", $dbg->prefix());
		printf("* Endline  is : '%s'\n", $dbg->endline());
		printf("* Log_file is : '%s'\n", $dbg->log_file());
		$dbg->verbose(0,	"Debug::test() Message envoyé par &verbose()");
		$dbg->log(			"Debug::log() Message envoyé par &verbose()");
		# BAD CHOICE $dbg->log(1, "Debug::test() Message envoyé par &log()");

		# Avec arguments
		$dbg->verbose(0,	"Debug::test->verbose('%s') Avec new prefix : Message envoyé par &verbose()", "jose");
		$dbg->log(			"Debug::test->log('%s')     Avec new prefix : Message envoyé par &verbose()", "jose");

		# Un nouveau prefix
		printf("New prefix is : '%s'\n", $dbg->prefix("<* nb=%03d> "));
		$nb	= 3;
		$dbg->verbose(0,	"Debug::test->verbose() Avec new prefix : Message envoyé par &verbose()", $nb++);
		$dbg->log(			"Debug::test->log()     Avec new prefix : Message envoyé par &verbose()", $nb++);

		# Encore plus d'options
		 printf("--------------------------------------\n");
		$dbg->verbose(0,	"Debug::test->verbose() Avec new prefix qui vaut '%s'", $nb++, $dbg->prefix());
		$dbg->log(			"Debug::test->log()     Avec new prefix qui vaut '%s'", $nb++, $dbg->prefix());

		#var_dump($stderr);
		$err[]	= count($stderr);
		$err[]	= $stderr;
		return( $err );
	}
}
