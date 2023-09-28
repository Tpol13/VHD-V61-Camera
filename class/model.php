<?php

/*
 *
 *  */

require_once(__DIR__."/option.class.php");

class FH
{
	function FH($opt=array())
	{
		return(__construct($opt));
	}

	function	__construct($opt=array())
	{
		if (count($opt))
		{
			if ( !isset($opt["method"]))
			{
				$opt["method"]	= "unix";
			}
			$this->opt	= new Option($opt);
		}
	}

	function	__destruct()
	{
	}

	function	test()
	{
	}

}
		$file	= "/tmp/ls.txt";
		$fh	= new FH( [ "file"	=> $file,
	   					"mode"	=> "w",
				]);
printf("DIR: %s\n", __DIR__);
?>
