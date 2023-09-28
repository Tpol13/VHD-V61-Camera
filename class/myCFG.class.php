<?php

# Classe provisoire, en vu d'intégrer Config3.pm de Perl
# une fois que j'aurai le temps de la convertir en PHP !

# VERSION: 2023 08 20

# $obj = new Arg( [ key1 => val1, .... ] );
#
# $val1	= $obj->get("key1");
# $val2	= $obj->put("key2", "val2");

class myCFG Extends Argv
{
	#private	$arg;

	function myCFG($options=array())
	{
		return(__construct($options));
	}

	function __construct($options=array())
	{
		#if (is_subclass_of(__CLASS__, 'Debug')) { parent::__construct($options); $this->dbg = &$this; } else { $this->dbg = new Debug($options); }
		if (is_subclass_of(__CLASS__, 'Argv')) { parent::__construct(["list"=>$options]); $this->argv = &$this; } else { $this->argv = new Argv(["list"=>$options]); }
		$this->method("http");
		if (count($options) )
		{
			$this->analyse( $options );
		}
	}

	function __destruct()
	{
	}

}

