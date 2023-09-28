<?php

# VERSION: 20230911

# Reste :
# 	intégrer Debug();

# en cours: &rmdir

global $argv;
if ( isset($argv[0]) && strpos($argv[0], "FileDir.class.php") != false ) { include_once("config/autoload.php"); FileDir::test(); }

class stdIO
{
	public	$std		= array();


	public function stdIO($table)
	{
		return($this->__construct($table));
	}

	public function __construct($table)
	{
		$this->std = $table;
		$this->reset();
	}

	public function __destruct()
	{
	}

	public function __set($name, $val)
	{
		if ($name == "add")
		{
			$this->std[]	= $val;
		}
		else
		{
			throw new Exception("Propriété '$name' n'existe pas");
		}
	}

	public function __get($name)
	{
		if ($name == "get")
		{
			$ret	= $this->std;
		}
		else
		{
			throw new Exception("Propriété '$name' n'existe pas");
		}

		return($ret);
	}


	public function __toString()
	{
			printf("IO toString\n");
		return($this->std);
		return(strval($this->std));
	}

	public function __invoke()
	{
			printf("IO invoke\n");
		return($this->std);
		return(strval($this->std));
	}
	public function	reset()
	{
		$this->std	= array();
		return($this);
	}
}




class FileDir
{
	public	$file;

	public		$exists		= false;
	public		$group		= "unknown";
	protected	$level		= 0;
	public		$real		= true;
	public		$stat		= array();
	public		$stderr;
	public		$verbose	= false;	# temporaire en attente de Debug()
	public		$user		= "unknown";
	public		$writable	= false;

	public function FileDir($file)
	{
		return($this->__construct($file));
	}

	public function __construct($file)
	{
		$this->stderr	= new stdIO(array());
		$this->stdout	= new stdIO(array());
		$this->file		= $file;

		$this->refresh();
	}

	public function __destruct()
	{
	}

	public function __toString()
	{
		return(strval($this->file));
	}

	public function __invoke()
	{
		return(strval($this->file));
	}

	# $this->stderr		est renseigné si erreur.
	public function	refresh()
	{
		$this->exists	= false;
		$this->stat		= array();
		$this->user		= "unknown";
		$this->group	= "unknown";
		$this->writable	= false;

		if (file_exists($this->file))
		{
			$this->exists	= true;

			$stat			= stat($this->file);
			if (count($stat) >= 0)
			{
				foreach ($stat as $key => $val)
				{
					if (!is_numeric($key))
					{
						$this->stat[$key]	= $val;
					}
				}	
				$this->user		= posix_getpwuid($this->stat["uid"])["name"] ?: "unknown";
				$this->group	= posix_getgrgid($this->stat["gid"])['name'] ?: "unknown";
			}
			$this->stat["type"]	= filetype($this->file);
			if ($this->stat["type"] == "dir")
			{
				if ( ($this->dir	= dir($this->file)) === false )
				{
					$this->stderr->add = sprintf("%s: L'ouverture du dossier '%s' est en erreur\n", get_called_class() . '->' . __FUNCTION__, $this->file);
				}
			}

			if (is_writable($this->file))
			{
				$this->writable	= true;
			}
		}
	}


### File

	# Renvoie "true" si l'opération d'effacement s'est bien déroulée
	# "false" si erreur.
	# $this->stderr contient l'erreur rencontrée sur la sortie standard (stderr)
	# $this->stdout contient les messages, de la "commande", envoyés sur la sortie standard (stdout)
	public	function Del()
	{
		$ret	= false;

		if ($this->exists)
		{
				if ($this->stat)
				{
					if ( $this->stat['type'] == "dir") # join for windows
					{
						$ret	= $this->rmdir();
					}
					else
					{
						$ret	= $this->delete();
					}
				}
				else
				{
					$this->stderr->add	= sprintf("%s: Erreur d'accès au flux '%s'", get_called_class() . '->' . __FUNCTION__, $this->file);
				}
		}
		else
		{
			$this->stderr->add	= sprintf("%s() '%s' n'existe pas", get_called_class() . '->' . __FUNCTION__, $this->file);
			#foreach (debug_backtrace() as $backtrace) { printf("* function called : '%s'\n",  $backtrace['function']); }
		}

		return($ret);
	}

	# Renvoie "true" si l'opération d'effacement s'est bien déroulée
	# "false" si erreur.
	# $this->stderr contient l'erreur rencontrée sur la sortie standard (stderr)
	# $this->stdout contient les messages, de la "commande", envoyés sur la sortie standard (stdout)
	public	function delete()
	{
		$file	= $this->file;

		$ret	= false;
		$stderr	= array();
		$stdout	= array();

		#$this->stderr->add	= sprintf("%s(\$file='%s') Start operation", get_called_class() . '->' . __FUNCTION__, $file);

		if ($this->exists)
		{
			if ( $this->writable)
			{
				if (PHP_OS === 'Windows')
				{
					$cwd	= getenv("TEMP")?getenv("TEMP"):getenv("TMP");
					$cmd	= sprintf("rd /s /q %s", escapeshellarg($file));
				}
				else
				{
					$cwd	= "/tmp/";
					$cmd	= sprintf("rm -f %s ", escapeshellarg($file));
				}

				$ret	= $this->exec($cmd);
				if ($ret === true)
				{
					$this->refresh(); # Mise à jour du status de $this->file, utile pour l'utilisateur.
					$this->stderr->add	= sprintf("%s() fichier '%s' a été effacé avec succès.", get_called_class() . '->' . __FUNCTION__, $file, $file);
				}
			}
			else
			{
				$this->stderr->add	= sprintf("%s(\$file='%s') Vous n'avez pas les droits pour effacer '%s'", get_called_class() . '->' . __FUNCTION__, $file, $file);
				$this->stderr->add	= sprintf("%s()    vos droits : '%s' username='%s, group='%s', ceux de '%s' : user='%s', group='%s'",
						get_called_class() . '->' . __FUNCTION__,
						get_current_user(),
						posix_getpwuid( posix_getuid( ) )['name'],
						posix_getgrgid( posix_getgid( ) )['name'],
						$file, $this->user, $this->group);

			}
		}
		else
		{
			$this->stderr->add	= sprintf("%s(\$file='%s') '\$file' n'existe pas", get_called_class() . '->' . __FUNCTION__, $file);
		}
		#$this->stderr->add	= sprintf("%s(\$file='%s') End operation", get_called_class() . '->' . __FUNCTION__, $file);

		return( $ret );
	}

	################
	### PROCESS ####
	################

	public function exec($cmd)
	{
		$ret	= false;
		$exec	= new Process($cmd);

		$exec->verbose	= $this->verbose;
		$exec->real		= $this->real;

		$ret			= $exec->exec();

		$stdout			= $ret['stdout'];
		$stderr			= $ret['stderr'];
		$ret			= $ret["exitcode"];
		if ($ret !== 0)
		{
			$this->stderr->add	= sprintf("%s() une erreur est survenue lors de la tentative de suppression de \$file='%s'", get_called_class() . '->' . __FUNCTION__, $file, $file);
			$this->stderr->add	= sprintf("%s() code erreur retourné : '%d'", get_called_class() . '->' . __FUNCTION__, $ret );
			foreach (explode("\n", $stderr) as $line)
			{
				$this->stderr->add	= sprintf("%s() err retournée(s) : %s", get_called_class() . '->' . __FUNCTION__, $line );
			}
			foreach (explode("\n", $stdout) as $line)
			{
				$this->stderr->add	= sprintf("%s() STDOUT commande : %s", get_called_class() . '->' . __FUNCTION__, $line );
			}
			$ret	= false;
		}
		else
		{
			$this->refresh();
			$ret	= true;
			#$this->stderr->add	= sprintf("%s() dossier '%s' a été effacé avec succès.", get_called_class() . '->' . __FUNCTION__, $file, $file);
		}
		return($ret);
	}

	#################
	### Direcotry ###
	#################

	# Renvoie "true" si l'opération d'effacement s'est bien déroulée
	# "false" si erreur.
	# $this->stderr contient l'erreur rencontrée sur la sortie standard (stderr)
	public	function rmdir()
	{
		$file	= $this->file;
		$ret	= false;

		if ($this->exists)
		{
			if (PHP_OS === 'Windows')
			{
				#exec(sprintf("rd /s /q %s", escapeshellarg($this->file)));
				$cmd	= sprintf("rd /s /q %s", escapeshellarg($this->file));
			}
			else
			{
				#exec(sprintf("rm -rf %s", escapeshellarg($this->file)));
				$cmd	= sprintf("rm -rf %s", escapeshellarg($this->file));
			}

				$ret	= $this->exec($cmd);
				if ($ret === true)
				{
					$this->refresh();
					$this->stderr->add	= sprintf("%s() dossier '%s' a été effacé avec succès.", get_called_class() . '->' . __FUNCTION__, $file, $file);
				}
		}
		else
		{
			$this->stderr->add	= sprintf("%s() Rien n'a été effacé, car dossier '%s' non existant.", get_called_class() . '->' . __FUNCTION__, $file);
		}

		return($ret);
	}

	# Renvoie la list des occurences trouvées sour former d'un array()
	# le tableau contient :
	# 0 si le $regex ne correspond à rien.
	# sinon le nombre d'occurences trouvées.
	public	function get2array($regex="", $io="%s")
	{
		$this->list		= array();
		$list			= array();
		$list_raw		= array();	# preserve a list as dir() returned it
		$list_sort		= array();
		$list_sort_raw	= array();
		$sortMtime		= array();
		$i				= 0;

		while( $tmp = $this->dir->read())
		{
			if ( preg_match("/$regex/", $tmp) )
			{
				$path			= $this->file."/".$tmp;
				$list_raw[$i]	= $tmp;

				$stat			= stat($path);
				$stat['nom']	= $tmp;
				$stat['type']	= filetype($path);
				if ($stat['type'] == "link")
				{
					# lstat permet d'avoir le mtime du lien et non du fichier pointé
					$stat			= lstat($path);
					$sortMtime[$i]	= $stat['mtime'];
				}
				else
				{
					#print_r($stat);
					$sortMtime[$i]	= $stat['mtime'];
				}
				$i++;
			}
		}

		# tri par mtime
		$list_sort_raw	= $list_raw;
		if (count($list_sort_raw) >= 0)
		{
			array_multisort($sortMtime, SORT_DESC, $list_sort_raw);
		}

		$i	= 0;
		# Format lists return
		foreach ($list_raw as $tmp)
		{
			$list[$i]		= sprintf($io, $tmp);
			$i++;
		}
		$i	= 0;
		foreach ($list_sort_raw as $tmp)
		{
			$list_sort[$i]	= sprintf($io, $tmp);
			$i++;
		}
		$this->list				= &$list;
		$this->list_raw			= $list_raw;
		$this->list_sort		= &$list_sort;
		$this->list_sort_raw	= &$list_sort_raw;

		$i=0;
		#foreach ($list_raw as $key => $val) { printf("%2d: '%10s' '%s' %s\n", $key, $val, $sortMtime[$i], date('H:i:s Ymd',$sortMtime[$i])); $i++; }
		$this->rewinddir();
		return($this->list);
	}

	# Renvoie le contenu de $path trié par ordre de dernière modification.
	function get2arrayTime($regex="", $io="%s")
	{
		$this->get2array($regex, $io);
		return($this->list_sort);
	}

	# Renvoie le contenu de $path trié par ordre Alphabétique
	function get2arrayAlpha($regex="", $io="%s")
	{
		$this->get2array($regex, $io);

		if (count($this->list) >= 0)
		{
			array_multisort($this->list, SORT_ASC, $this->list);
		}
		return($this->list);
	}

	# Fonctionne comme get2array() mais sans utiliser le regex.
	# Ceci pour ne pas risquer d'avoir du code malvaillant dnas $file...
	public	function get3array($file="", $io="%s")
	{
		$this->list	= array();

		while( $tmp = $this->dir->read())
		{
			if ( $file ==  $tmp )
			{
				array_push($this->list, sprintf($io, $tmp) );
			}
		}
		$this->rewinddir();
		return($this->list);
	}

	public function rewinddir()
	{
		$this->dir->rewind();
	}

	public function	status()
	{
		$status		= array();
		$status[]	= sprintf("Name file '%s'", $this->file);
		$status[]	= sprintf("* exists '%s'", $this->exists?"Yes":"No");
		$status[]	= sprintf("* writable '%s'", $this->writable?"Yes":"No");
		$status[]	= sprintf("* Stats on device :");

		foreach($this->stat as $key => $val)
		{
			$status[]	= sprintf("* %10s = %s", $key, $val);
		}
		$status[]	= sprintf("* File username '%s'", $this->user);
		$status[]	= sprintf("* Group username'%s'", $this->group);
		$status[]	= sprintf("* Username of this process '%s'", posix_getpwuid( posix_getuid( ) )['name']);
		$status[]	= sprintf("* Group name of this process '%s'", posix_getgrgid( posix_getgid( ) )['name']);
		return($status);
	}
}

class Dir
{
	protected	$dir_path	= "";
	protected	$dir; // Object dir()
	public		$list	= array();
	public		$stderr	= array();

	function Dir($dir_path="/tmp/")
	{
		if (!is_dir($dir_path))
		{
			$dir_path	= dirname($dir_path);
		}
		$this->dir_path	= $dir_path;
		$this->dir		= dir( $dir_path );
		return ($this);
	}

	function __destruct()
	{
		$this->dir->close();
	}

	# Si aucunes option ne retourne que la valeur actuel. Undef si rien.
	function dir_path($path=false)
	{
		if ($path)
		{
			$this->dir_path	= $path;
		}
		return($this->dir_path);
	}

	# Renvoie la list des occurences trouvées sour former d'un array()
	# le tableau contient :
	# 0 si le $regex ne correspond à rien.
	# sinon le nombre d'occurences trouvées.
	public	function get2array($regex="", $io="%s")
	{
		$this->list	= array();

		while( $tmp = $this->dir->read())
		{
			if ( preg_match("/$regex/", $tmp) )
			{
				array_push($this->list, sprintf($io, $tmp) );
			}
		}
		$this->rewinddir();
		return($this->list);
	}

	# Renvoit le contenu de $path trié par ordre de dernière modification.
	# regex est sous forme de match et non pur $regex.
	function get2arrayTime($regex="", $io="%s")
	{
		$cwddir	= getcwd();
		chdir($this->dir_path);
		#printf("cwfdir '%s', chdir'%s', regex '%s'\n", $cwddir, $this->dir_path, $regex);
		array_multisort(array_map('filemtime', ($this->list = glob($regex))), SORT_DESC, $this->list);
		chdir($cwddir);
		return($this->list);
	}

	# Fonctionne comme get2array() mais sans utiliser le regex.
	# Ceci pour ne pas risquer d'avoir du code malvaillant dnas $file...
	public	function get3array($file="", $io="%s")
	{
		$this->list	= array();

		while( $tmp = $this->dir->read())
		{
			if ( $file ==  $tmp )
			{
				array_push($this->list, sprintf($io, $tmp) );
			}
		}
		$this->rewinddir();
		return($this->list);
	}

	# relit depuis la première ligne
	# pas de vérification sur l'existance du $this->dir()
	public function rewinddir()
	{
		$this->dir->rewind();
	}


	public	function display($regex="/^.*$/")
	{
		$tmp	= "";

		if ( isset( $this->dir_path) )
		{
			if ( $this->get2array($regex, "Display(): %s\n") <= 0)
			{
				$this->stderr->add	= sprintf("Aucunes occurences (%s) trouvées dans le dossier '%s'\n", $regex, $this->dir_path);
			}
			else
			{
				$this->stderr->add	= sprintf("Dossiers trouvés :\n");
			}

			foreach($this->list as $value)
			{
					$this->stderr->add	= sprintf("%s\n", $value);
			}
		}
		else
		{
			printf("La variable dir_path='%s' n'est pas définie\n", $this->dir_path);
		}
	}

	public	function test()
	{
		global $argv;

		$arg	= new Argv( ["list" => $argv, "method" => "unix"] );
		$medias	= $arg->get("name");

		if ( isset( $this ) )
		{
			$file	= $this;
		}
		else
		{
			$file	= new FileDir($medias);
		}

		printf("--------   partie StringFile   --------------\n");
		$file	= new FileDir($file);
		foreach ($file->status() as $name)
		{
			printf("status: %s\n", $name);
		}
		if ($file->exists)
		{
				if ( !($ret	=  $file->Del()))
				{
					printf("Fichier '%s' effacé, code retour '%d'\n", $file, $ret);
				}
				else
				{
					printf("Résultat des erreurs rencontrées :\n");
					print_r($file->stderr->get);
				}
		}
		$file->stderr->reset();

		#var_dump( $dir->list );

		$regex	= ".*";
		$dir	= new FileDir("/");
		foreach ($dir->status() as $name)
		{
			printf("status: %s\n", $name);
		}
		printf("Sort by modification date\n");
		$list	= $dir->get2arrayTime($regex);
		print_r($list);
		printf("Sort by reading order\n");
		$list	= $dir->get2array($regex);
		print_r($list);

		printf("Sort Alphabetically\n");
		$list	= $dir->get2arrayAlpha($regex);
		print_r($list);

		$dir	= new FileDir("/var/www/html/surveillance/medias/");
		$list	= $dir->get2arrayTime("*.mkv");
		print_r($list);
		if ( isset( $this ) ){ return ($this); }
	}
}


?>
