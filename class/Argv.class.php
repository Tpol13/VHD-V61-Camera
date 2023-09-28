<?php
/*
 * Pour la DOC, voir la fonction ::test() ci-dessous.
 *
 * Version: 20230821
 *
 * A FAIRE:
 *
 *  étudier fonction ->nom variable()
 *  ->get("nom variable");
 *
 *  <!> Ce module n'est qu'une ébauche, en attente de convertir Config3.pm en PHP...
 *
 *  */

global $argv;
if ( isset($argv[0]) && strpos($argv[0], "Argv.class.php") != false ) { Argv::test(); }

#
# $arg	= new Argv()
# 	Si aucun arguement, l'analyse n'est pas lancée et aucunes valeurs, même de $argv, n'est mémorisée.
#
# $arg	= new Argv( [ "list" => [ "opt1=val1", "optN=valN" )
# 	<!> si "list" est présent, l'analyse est lancée
#
#


class Argv
{
	public		$argv	= array();
	public		$list	= array();
	private		$method	= "http";

	# $argv est celui de PHP ! https://www.php.net/manual/fr/reserved.variables.argv.php 
	function Argv($argv)
	{
		return(__construct($argv));
	}

	#
	# La présence de  $argv["list"] lance l'analyse.
	#
	function	__construct($options=array())
	{
		if ( isset($options["method"]))
			$this->method	= $options["method"];

		if ( isset($options["list"]))
			$this->analyse( $options["list"] );
	}

	function	__destruct()
	{
	}

	function	method($opt="")
	{
		$ret	= false;
		if ( $opt != "" )
		{
			$ret	= $this->method	= $opt;
		}
		else
		{
			$ret	= $this->method;
		}

		return( $ret );
	}

	# =item get ( $key )
	#
	# Récupère la valeur $key dans la liste des arguements
	#
	# RETURN "" si la valeur n'est pas trouvée. Sinon renvoie la valeur
	# de $key depuis la liste des options mémorisées.
	#
	# =back
	#
	function	get($key)
	{
		$ret	= "";
		if ( isset($this->list[$key]) )
		{
			$ret	= $this->list[$key];
		}

		return( $ret );
	}

	# =item set( $key, $val )
	#
	# Modifie ou créé une valeur dans la list des arguements stockés.
	#
	# 	$val peut contenir toute type de valeur : false, object, ...
	#
	# RETURN
	#
	#  * Si $key existe dans la liste, il replace sa valeur par $val et renvoie $val pour confirmer que c'est une modification.
	#  * Si $key n'existe pas, créé $key avec la valeur $val. Renvoit "" pour indiquer qu'il s'agit une nouvelle $key.
	#
	# =back
	#
	function	set($key, $value)
	{
		$ret	= "";
		if ( isset($this->list) )
		{
			$ret	= $this->list[$key]	= $value;
		}
		return( $ret );
	}

	# &analyse($argv) $argv de PHP
	# &analyse( array() )	array() est sous forme de : ["option1=value1", "optionN=valueN", ... ]
	function	analyse($options=array())
	{
		# -- MovieStart file=/var/www/html/surveillance/medias//01-20220927144300.mkv date=20220927-14:43:00 frame=01
		#$this->argv_bak	= $options;	# Sauvegarde ...
		$arg_list	= array();

		foreach ($options as $key => $value)
		{
		$var		= array();
			if ( isset($value) )
			{
				#$value	= $options[$n];

				#printf("Key='%s', value='%s'\n", $key, $value);
				if ( $this->method == "unix")
				{
					$var	= explode("=", $value);
				}
				elseif ( $this->method == "http")
				{
					$var[]	= $key;
					$var[]	= $value;
				}

				if ( $var != false)
				{
					if ( isset($var[1]) )
					{
						$arg_list[$var[0]]	= $var[1];
					}
					else
					{
						$arg_list[$var[0]]	= true;
					}
				}
			}
		}

		if (count($arg_list) >= 1)
		{
			$this->list	= $arg_list;
		}
	}

	# Lancer le prg ./$0 date="20220922" other="blabla" flagExist
	function	test()
	{
		global $argv;
		$Argv	= new Argv([ "method"	=> "unix",
							"list"	=> $argv
						]);
		# OR
		#$Argv	= new Argv();
		#$Argv->method("unix");
		#$Argv->analyse($argv);
		print $Argv->get("date");
		$Argv->set("date", "nouvelle date");
		print $Argv->get("date");
		print_r($Argv->list);
	}
}
?>
