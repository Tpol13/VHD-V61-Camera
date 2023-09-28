<?php


# VERSION: 20230905

# Non utilisée, car en test pour Debug, fusionner prefix/verbose/endline de manière transparente.
# Et si je dois convertir du C++ en PHP...

global $argv;
if ( isset($argv[0]) && strpos($argv[0], "Data.class.php") != false ) { include_once("config/autoload.php"); Data::test(); }


class Data
{
	public	$data		= array();


	public function Data($data)
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
		if ($name == "set")
		{
			$this->data	= $val;
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
			$ret	= $this->data;
		}
		else
		{
			throw new Exception("Propriété '$name' n'existe pas");
		}

		return($ret);
	}


	public function __toString()
	{
		#printf("IO toString\n");
		return($this->data);
		return(strval($this->data));
	}

	public function __invoke()
	{
		#printf("IO invoke\n");
		throw new Exception("Invoke n'est pas géré");
		return($this->data);
		return(strval($this->data));
	}
	public function	reset()
	{
		$this->data	= "";
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

