<?php

# VERSION 20230905

# Non utilisée, uniquement cas d'école.
# Et en cas de besoin si je dois convertir un C++ en Php...

global $argv;
if ( isset($argv[0]) && strpos($argv[0], "Liste.class.php") != false ) { include_once("config/autoload.php"); Liste::test(); }

class NodeListe
{
	public $data;	 // Données du nœud
	public $next;	 // Pointeur vers le nœud suivant
	public $prev;	 // Pointeur vers le nœud précédent

	public function __construct($data)
	{
		$this->data = $data;
		$this->next = null;
		$this->prev = null;
	}
}

class Liste
{
	public $head;	 // Pointeur vers le premier nœud
	public $tail;	 // Pointeur vers le dernier nœud

	public function __construct()
	{
		$this->head = null;
		$this->tail = null;
	}

	public function add($data)
	{
		$newNode = new NodeListe($data);

		if ($this->head === null)
		{
			// La liste est vide, le nouveau nœud devient la tête et la queue.
			$this->head = $newNode;
			$this->tail = $newNode;
		}
		else
		{
			// La liste n'est pas vide, ajoutez le nouveau nœud à la fin.
			$newNode->prev = $this->tail;
			$this->tail->next = $newNode;
			$this->tail = $newNode;
		}
	}

	public function sortAlphabetically()
	{
		$current = $this->head;

		while ($current->next !== null)
		{
			$next = $current->next;
			while ($next !== null)
			{
				if (strcasecmp($current->data, $next->data) > 0)
				{
					// Échangez les données des nœuds
					$temp = $current->data;
					$current->data = $next->data;
					$next->data = $temp;
				}
				$next = $next->next;
			}
			$current = $current->next;
		}
	}

	public function sortCase()
	{
		if ($this->head === null || $this->head->next === null) {
			// La liste est vide ou a un seul élément, rien à trier.
			return;
		}

		$sorted = null; // Nouvelle liste triée
		$current = $this->head;

		while ($current !== null) {
			$next = $current->next;

			if ($sorted === null) {
				// Première étape : la liste triée est vide, faites de $current la tête.
				$current->prev = null;
				$current->next = null;
				$sorted = $current;
			} else {
				// Insérer $current dans la liste triée tout en maintenant les liens.
				$prevSorted = null;
				$sortedCurrent = $sorted;

				while ($sortedCurrent !== null && strcasecmp($current->data, $sortedCurrent->data) > 0) {
					$prevSorted = $sortedCurrent;
					$sortedCurrent = $sortedCurrent->next;
				}

				if ($prevSorted === null) {
					// Insérer $current au début de la liste triée.
					$current->prev = null;
					$current->next = $sorted;
					$sorted->prev = $current;
					$sorted = $current;
				} else {
					// Insérer $current entre $prevSorted et $sortedCurrent.
					$prevSorted->next = $current;
					$current->prev = $prevSorted;
					$current->next = $sortedCurrent;

					if ($sortedCurrent !== null) {
						$sortedCurrent->prev = $current;
					}
				}
			}

			$current = $next;
		}

		// Mettre à jour la tête de la liste principale pour pointer vers la nouvelle liste triée.
		$this->head = $sorted;

		// Trouver la nouvelle queue de la liste triée (cela pourrait être optimisé si besoin).
		$tail = $sorted;
		while ($tail->next !== null) {
			$tail = $tail->next;
		}

		// Mettre à jour la queue de la liste principale.
		$this->tail = $tail;
	}
	public function printListe()
	{
		$current = $this->head;
		while ($current !== null)
		{
			echo $current->data . " ";
			$current = $current->next;
		}
		echo PHP_EOL;
	}

	public function printListe_tail()
	{
		$current = $this->tail;
		while ($current !== null)
		{
			echo $current->data . " ";
			$current = $current->prev;
		}
		echo PHP_EOL;
	}


	public	function	test()
	{
		// Exemple d'utilisation :
		$list = new Liste();
		$list->add("C");
		$list->add("A");
		$list->add("D");
		$list->add("B");

		$list->printListe(); // Affiche "A B C"
		$list->sortCase();
		$list->printListe(); // Affiche "A B C"
		$list->printListe_tail(); // Affiche "A B C"
	}
}

