<?php

# VERSION: 20230907

# Reste :
#
# a intégrer Debug()
# pour obtenir Real / verbose / config...

class Process
{
	public		$var			= ""; # la commande

	public		$cwd			= "/tmp/";	# Dossier de travail
	protected	$descriptors	= array(
						0	=> array("pipe", "r"),	//STDIN
						1	=> array("pipe", "w"),	//STDOUT
						2	=> array("pipe", "w"),	//STDERR
	);
	public		$env_vars	= null;
	public		$options	= null;
	public		$verbose		= 0;
	public		$real			= 1;
	public		$stdout			= "";
	public		$stderr			= "";

	public function Process($var)
	{
		return($this->__construct($var));
	}

	public function __construct($var)
	{
		$this->var = $var;
		$this->init();
	}

	public function __destruct()
	{
	}

	public function	init()
	{
		$this->cwd	= getenv("TEMP")?getenv("TEMP"):"/tmp/";
		return($this);
	}

	public function	reset()
	{
		$this->var		= "";
		$this->cwd		= "/tmp/";
		$this->env_vars	= null;
		$this->options	= null;
		return($this);
	}

	public function __set($name, $val)
	{
		throw new Exception("Propriété '$name' n'existe pas");
		if ($name == "add")
		{
			$this->var[]	= $val;
		}
		else
		{
			throw new Exception("Propriété '$name' n'existe pas");
		}
	}

	public function __get($name)
	{
		throw new Exception("Propriété '$name' n'existe pas");
		printf("Get: '%s' \n", $name);
		if ($name == "get")
		{
			$ret	= $this->var;
		}
		else
		{
			throw new Exception("Propriété '$name' n'existe pas");
		}

		return($ret);
	}


	public function __toString()
	{
		return($this->var);
		return(strval($this->var));
	}

	public function __invoke()
	{
		return($this->var);
		return(strval($this->var));
	}

####################
### EXEC COMMAND ###
####################

	# éxécute $this->var et récupère les sorties STDOU et STDERR
	#
	#
	# Renvoie un array:
	#		[command]	=> $this->var // la commande !
	#		[pid]		=> N° du pid qui a été utilisé lors de l'execution de la commande.
	#		[running]	=> Si le processus continu d'être exécuté
	#		[signaled]	=> non géré
	#		[stopped]	=> non géré
	#		[exitcode]	=> 0-256
	#		[termsig]	=> 0
	#		[stopsig]	=> 0
	#		[stdout]	=> string
	#		[stderr]	=> string
	public function	exec()
	{
			$this->stdout	= "";
			$this->stderr	= "";
			$ret			= array();

			if ($this->verbose )
			{
				printf("%s\n", $this->var);
			}

			if ($this->real)
			{
				$process		= proc_open($this->var, $this->descriptors, $pipes, $this->cwd, $this->env_vars, $this->options);
				$this->stdout	= stream_get_contents($pipes[1]);
				$this->stderr	= stream_get_contents($pipes[2]);

				fclose($pipes[0]);
				fclose($pipes[1]);
				fclose($pipes[2]);

				$ret		= proc_get_status($process);
				if ($process !== false)
				{
					proc_close( $process );
				}
			}
			else
			{
				# Real = 0
				$ret['command']		= $this->var;
				$ret['pid']			= "-1";
				$ret['running']		= "";
				$ret['signaled']	= "";
				$ret['stopped']		= "";
				$ret['exitcode']	= 0;
				$ret['termsig']		= "";
				$ret['stopsig']		= "";
			}

			return( array_merge($ret, ["stdout" => $this->stdout, "stderr" => $this->stderr]) );
	}

}
