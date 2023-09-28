<?php

/*
 *
 * Version: 20230802 11:53
 *
 * A FAIRE:
 * 	- Ajouter vérification sur existance des options comme $file, $mode
 * 	  prévoir des valeurs par défaut...
 *
 *  */

#require_once(__DIR__."/class.php");
#require_once(__ARGV__);

# Reprends une partie du fonctionnement de FileHandle.pm de Perl.
#
# Sont seulement inclus : &open, &read, &mode, &write et &close()
#
# <!> Tous les arguements de ./$0 sont pris en charge dans cette Class !!!!
#
class FileHandle
{
	public		$arg;
	public		$err;
	private		$fh;		# File Handle du fichier ouvert
	private		$in;		# dernière données qui viennent d'être lues.
	public		$name_file;	# Nom du fichier trouvé en arguement de fonction &new(...) & open(...) ou "$argv"
	private		$out;		# dernière données qui viennent d'être écrites.


	function FileHandle($opt=array())
	{
		return(__construct($opt));
	}

	function	__construct($opt=array())
	{
		if (count($opt))
		{
			$this->arg	= new Argv( [ "list"	=> $opt	]);
			#$this->arg->analyse($opt);

			$this->name_file	= $this->arg->get("file");
			$mode				= $this->arg->get("mode");

			if (isset($this->name_file))
			{
				$this->fh	= $this->open($this->name_file);
			}
			else
			{
				$this->warn	= sprintf("Le nom de fichier n'est pas présent dans la création de l'instance Argv()");
			}
		}
	}

	function	__destruct()
	{
		$this->close();
	}

	function	open($file=false)
	{
		$this->fh	= "null";
		$this->err	= "null";

		if ( ! $file )
		{
			$file	= $this->name_file;
		}
		
		if ( $file )
		{
			$this->name_file	= $file;

			$mode	= $this->arg->get("mode");
			if ( ($this->fh	= fopen($file, $mode?$mode:"r")) == false )
			{
				$this->err	= sprintf("Erreur d'ouverture du fichier '%s' en '%s'", $file, $mode?$mode:"r");
			}
			else
			{
				#printf("fh 	= fopen(%s, %s\n", $file, $mode?$mode:"r");
			}
		}
		return($this->fh);
	}

	/*
	 * List un flux depuis $this->fh
	 * $in	= read( $opt )
	 *
	 * 	$in	= données lues
	 * 	$opt
	 * 		s'il vaut -1, lit tout le fichier d'un trait (file_get_contents())
	 * 		si >= 0, lit $size octect(s) ou jusqu'à la du fichier. (fread())
	 * 		si "non précisé", lit la prochaine ligne ou jusqu'à la fin du fichier. (fgets($fh, $size))
	 *
	 * RETURN
	 * 	si $opt >= 0, renvoit le nombre d'octects lu
	 * 	Dans les autres cas, renvoit les données lues..
	 */
	function	read($size)
	{
		unset($this->err);
		if ( isset( $this->fh ))
		{
			if (isset($size))
			{
				if ($size == -1 )
				{
					#printf("Lecture file_get_contents() de '%s'\n", $this->arg->get("file"));
					$this->in	= file_get_contents($this->arg->get("file"));
				}
				else
				{
					#printf("Lecture fread() de '%s'\n", $this->arg->get("file"));
					$this->in	= fread($this->arg->get("file"), $size);
				}
			}
			else
			{
				#printf("Lecture gets() de '%s'\n", $this->arg->get("file"));
				$this->in	= fgets($this->fh);
			}
		}
		else
		{
			$this->err	= sprintf("read() ne trouve pas de \$fh avec lequel lire des données.\nVérifier si le fichier '%s' existe et qu'il est accessible en lecture", $this->arg->get("file"));
		}
		return( $this->in );
	}

	/*
	 *  write() retourne le nombre d'octets écrits ou false si une erreur survient.
	 *  eq à fwrite () mais avec gestion d'erreur dans $this->err
	 */
	function	write($buf=false)
	{
		$this->err	= false;;
		$nbytes		= false;

		if ( isset( $this->fh ) && $this->fh != false)
		{
			if ($buf)
			{
				$this->out	= $buf;
				$nbytes		= fwrite($this->fh, $buf);
				if ( gettype($nbytes) != "boolean")
				{
					if ( $nbytes != strlen($buf))
					{
						$this->err	= sprintf("Write() n'a pu écrire la totalité des données contenues dans \$buffer");
					}
				}
			}
			#else
			#{
			#	$errstr et $errno
			#	$this-err	= sprintf("Le contenu devant être écrit est vide, rien n'est donc écrit dans le \$fh\n");
			#}
		}
		else
		{
			$this->err		= sprintf("aucun flux ouvert, \$fh = NULL");
		}
		
		return($nbytes);
	}

	function	close()
	{
		$ret	= false;
		unset($this->err);

		if ( isset($this->fh) && $this->fh != false)
		{
			$ret	= fclose($this->fh);
			if ($ret)
			{
				unset($this->fh);
			}
			else
			{
				$this->err	= sprintf("Impossible de ferme le descripteur de fichier '%s'", $this->fh);
				$this->warn	= &$this->err;
			}
		}
		
		return( $ret );
	}

############
### MISC ###
############
	function	isOpen()
	{
		$ret	= true;

		if ( !isset($this->fh) || $this->fh == false )
		{
			$this->err	= sprintf("Flux sur fichier '%s' non ouvert", $this->name_file);
			if ( !isset($this->name_file) )
			{
				$this->err	.= sprintf(", certainement parce que le Nom du fichier n'est pas défini");
			}
			$ret	= false;
		}

		return($ret);
	}
	function var2file($variable="")
	{
		$ret	= $this->write($variable);
		return( $ret );
	}

	function	test()
	{
		$file	= "/var/www/html/surveillance/medias/01-20220927144300.mkv.json";
		$fh2	= new FileHandle( [ "file"	=> $file,
	   					"mode"	=> "r",
				]);
		#$bytes	= $fh2->write(sprintf("test d'écriture\n"));
		#$fh2->open();
		$json	= $fh2->read(-1);
		$buf	= json_decode($json, true);
		print_r($buf);
		#$fh2->close();	# bad choice
		unset($fh2);	# Best choice
		#printf("Ecriture de '%d' dans le fichier nommé '%s'\n", $bytes, $fh2->arg->get("file") );
	}

}
#FileHandle::test();
?>
