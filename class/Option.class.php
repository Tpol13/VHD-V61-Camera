<?php
/*
<video controls width="250"  controls autoplay>
    <source src="mosaic.php?prev=<?php print $visioneuse;?>" type="video/mp4">
	</video>


	VERSION: 20230909

 *  */
require_once(__DIR__."/class.php");


class Option
{
	public	$doctype	= "html"; #prévu pour utiliser autre chose que HTML, par ex: bash, ...
	public	$model		= array();
	private	$monotag	= false;
	private	$mark;					# Contient le nom du tag html à ajouter en remarque lorsque this->mark != (undef || "")
	public	$opt;					# contient la liste des options.

	function Option($name="unknow", $opt=array())
	{
		return(__construct($name, $opt));
	}

	function	__construct($name="unknow", $opt=NULL)
	{
		$this->name	= $name;
		$this->opt	= $opt;
		$this->doctype();	/* Initialise le type de document à afficher avec &get_* */
		#$this->monotag(false); # perte de temps de le lancer dans le constructeur, car défini par defaut...
		#printf("Options: construct()\n");
	}

	function	__destruct()
	{
		#printf("Options: destruct()\n");
	}

	function	__toString()
	{
		return($this->get_opt());
	}




	public function doctype($doctype="html")
	{
			# Vérif des possibilité de doctype à créer
		$this->doctype	= $doctype;
	}

	# Permet de savoir s'il faut finir un tag html comme : <name_tag />
	# # lors de l'appelle de &get_opt. Si oui cela rend aussi innopérant &close_opt()
	public function monotag($monotag="")
	{
		if ( $monotag	!= "") { $this->monotag	= $monotag; }
		return( $this->monotag );
	}

	# Permet d'ajouter une remarque après la fermeture d'un TAG HTML
	public function mark($mark="")
	{
		if ( $mark	!= "") { $this->mark	= $mark; }
		return( $this->mark );
	}

	public function unmark()
	{
		unset($this->mark);
	}

	# Change la valeur d'une option, si celle si n'existe pas, elle est créée.
	public function chvar($name="", $newval="")
	{
		$ret	= NULL;
		if ( $name != "" )
		{
			$this->opt[$name]	= $newval;
		}
		return(	$this->opt );
	}

	public function delvar($name="")
	{
		$ret	= NULL;
		if ( $name != "" )
		{
				if ( isset( $this->opt[$name] ) )
				{
					unset($this->opt[$name]);
				}
		}
		return(	$this->opt );
	}

	function get_opt()
	{
		$this->get_start();
			switch($this->doctype)
			{
				case "html":
					if ( !$this->monotag )
					{
						$this->tmp	.= ">";
					}
					else
					{
						$this->tmp	.= " />\n";
					}
					break;

				case "css":
					$this->tmp	.= sprintf("\n", $this->name);
					break;

				default:
					#$this->tmp	= sprintf("</%s>\n", $this->name);
					#printf("non trouvé '%s'\n", $this->doctype);
					#exit;
					break;
			}
		return($this->tmp);
	}

	function close_opt()
	{
		$this->tmp	= "";
		return($this->get_stop());
	}

	# Ferme le tag, si pas monotag, donc sous forme <> </>
	private function get_stop()
	{
		 if ( isset($this->name) && !$this->monotag )
		 {
			switch($this->doctype)
			{
				case "html":
					$this->tmp	= sprintf("</%s>", $this->name);
					if ( isset($this->mark) )
					{
						$this->tmp	.= sprintf("<!-- %s = %s -->", $this->name, $this->mark);
					}
					$this->tmp	.= "\n";
					break;

				case "css":
					$this->tmp	= sprintf("}\n", $this->name);
					break;

				default:
					#$this->tmp	= sprintf("</%s>\n", $this->name);
					#printf("non trouvé '%s'\n", $this->doctype);
					#exit;
					break;
			}
		 }
		 return($this->tmp);
	}

	private function get_start()
	{
		$tmp	= "";

		$name	= &$this->name;
		$opt	= &$this->opt;

		if ( isset($name))
		{
			switch($this->doctype)
			{
				case "html":
					$tmp	= sprintf("<%s", $name);
					#if ( isset($opt) )
					if ( isset($this->opt) )
					{
						foreach ($opt as $nom => $value)
						{
							# https://www.php.net/manual/fr/function.gettype.php
							$type	= gettype($value);
							$tmp	.= " ";
							switch($type)
							{
								case "boolean":
									$tmp	.= "$nom";
									break;
								default:
									$tmp	.= $nom.'="'.$value.'"';
									break;
							}
							#$tmp	.= " ";
							#printf('%s="%s"\n', $nom, $value);
						}
					}
					break;

				case "css":
					$tmp	= sprintf("%s {\n", $name);
					if ( isset($this->opt) )
					{
						foreach ($opt as $nom => $value)
						{
							$tmp	.= sprintf("	%s:	%s;\n", $nom, $value);
						}
					}
						break;

				default:
					#$this->tmp	= sprintf("</%s>\n", $this->name);
						break;
			} # switch
			$this->tmp	= $tmp;
			#printf("TMP: %s\n", $tmp);
		}
		return($tmp);
	}
}
?>
