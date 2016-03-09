<?php

/**
 * Clase de los miembros que integran la genealogía
 */
class Member
{
	/**
	 * Instancia de "Genealogy" asociada.
	 *
	 * @var        Genealogy
	 */
	protected $_genealogy = NULL;
	/**
	 * Código único del miembro actual.
	 *
	 * @var        string
	 */
	protected $_code = NULL;
	/**
	 * Nombre del miembro actual.
	 *
	 * @var        string
	 */
	protected $_name = "";

	/**
	 * Constructor.
	 *
	 * @param      Genealogy  $genealogy  Instancia de la genealogia asociada.
	 * @param      string     $code       Código único del miembro actual.
	 * @param      string     $name       Nombre del miembro actual.
	 */
	public function __construct(Genealogy $genealogy,$code,$name=NULL)
	{
		$this->_genealogy = $genealogy;
		$this->_code = $code;
		$this->setName($name);
	}
	/**
	 * Devuelve la instancia de la genealogía asociada.
	 *
	 * @return     Genealogy
	 */
	public function getGenealogy()
	{
		return $this->_genealogy;
	}
	/**
	 * Devuelve el código del miembro actual.
	 *
	 * @return     string
	 */
	public function getCode()
	{
		return $this->_code;
	}
	/**
	 * Devuelve el nombre del miembro actual.
	 *
	 * @return     String
	 */
	public function getName()
	{
		return $this->_name;
	}
	/**
	 * Establece el nombre del miembro actual.
	 *
	 * @param      String  $name   Nombre del miembro
	 *
	 * @return     Member
	 */
	public function setName($name)
	{
		//Normaliza el nombre
		$name = $this->getGenealogy()->normalizeName($name);
		//En caso que esté vacio el nombre coloca automáticamente "John Doe".
		$name = empty($name)?"John Doe":$name;
		//Guarda el nombre en la instancia.
		$this->_name = $name;
		//Devuelve la instancia actual.
		return $this;
	}
	/**
	 * Devuelve las instancias de los miembros relacionados para una herencia dada.
	 *
	 * @param      string   $type       Tipo de herencia que se busca.
	 * @param      boolean  $recursive  Determina si la búsqueda de herencias será recursiva o no (Por defecto falsoo)
	 *
	 * @return     array
	 */
	public function getInheritance($type,$recursive=FALSE)
	{
		$inheritance    = $this->getGenealogy()->getInheritance($type,$this->getCode(),$recursive);
		$instances = [];

		if(empty($inheritance))
		{
			return [];
		}

		if($recursive !== TRUE)
		{
			foreach($inheritance as $code)
			{
				$instances[$code] = $this->getGenealogy()->get($code);
			}
		}
		else
		{
			foreach($inheritance as $k => $line)
			{
				$instances[$k] = [];

				foreach($line as $code)
				{
					$instances[$k][$code] = $this->getGenealogy()->get($code);
				}
			}	
		}
		return $instances;
	}
	/**
	 * Devuelve las instancias de los miembros hijo relacionados.
	 *
	 * @param      string  $recursive  Determina si  la búsqueda será recursiva o no (por defecto falso).
	 *
	 * @return     array
	 */
	public function getChilds($recursive=FALSE)
	{
		return $this->getInheritance(Genealogy::_CHILD,$recursive);
	}
	/**
	 * Devuelve las instancias de los miembros padre relacionados.
	 *
	 * @param      string  $recursive  Determina si  la búsqueda será recursiva o no (por defecto falso).
	 *
	 * @return     array
	 */
	public function getParents($recursive=FALSE)
	{
		return $this->getInheritance(Genealogy::_PARENT,$recursive);
	}

	/**
	 * Añade una herencia al miembro actual.
	 *
	 * @param      string  $type   Tipo de la herencia. Esta puede ser "parent" o "child".
	 * @param      <type>  $code   Código del miembro por vincular.
	 *
	 * @return     Member
	 */
	public function addInheritance($type,$code)
	{
		$this->getGenealogy()->setInheritance($type,$this->getCode(),$code);
		return $this;
	}
	/**
	 * Añade un padre al miembro actual.
	 *
	 * @param      string  $code   Código del miembro padre por vincular.
	 *
	 * @return     Member
	 */
	public function addParent($code)
	{
		return $this->addInheritance(Genealogy::_PARENT,$code);
	}
	/**
	 * Añade un hijo al miembro actual.
	 *
	 * @param      string  $code   Código del miembro hijo por vincular.
	 *
	 * @return     Member
	 */
	public function addChild($code)
	{
		return $this->addInheritance(Genealogy::_CHILD,$code);
	}
	/**
	 * Agrega una herencia especifica con los miembros suministrados.
	 *
	 * @param      string  $type   Tipo de la herencia. Esta puede ser "parent" o "child".
	 * @param      <type>  $codes  Matriz con los miembros de la herencia por vincular.
	 *
	 * @return     Member
	 */
	public function addInheritances($type,$codes)
	{
		if(is_array($codes) && !empty($codes))
		{
			foreach($codes as $code)
			{
				$this->addInheritance($type,$code);
			}
		}
		return $this;
	}
	/**
	 * Vincula varios miembros padre al actual.
	 *
	 * @param      string  $codes  Matriz con los miembros padre por vincular.
	 *
	 * @return     Member
	 */
	public function addParents($parentCodes)
	{
		return $this->addInheritances(Genealogy::_PARENT,$parentCodes);
	}
	/**
	 * Vincula varios miembros hijo al actual.
	 *
	 * @param      string  $codes  Matriz con los miembros hijo por vincular.
	 *
	 * @return     Member
	 */
	public function addChilds($childCodes)
	{
		return $this->addInheritances(Genealogy::_CHILD,$parentCodes);
	}
	/**
	 * Imprime el arbol familial del miembro actual.
	 */
	public function printTree()
	{
		//Creamos una matriz con los nombres humanos para las relaciones
		//Dejamos como Key el tipo de relación para luego automatizar el dump de la info
		//con bucles
		$inheritance = [
			Genealogy::_PARENT => [
				0 => "Padres",
				1 => "Abuelos",
				2 => "Bisabuelos",
				3 => "Tatarabuelos"
			],
			Genealogy::_CHILD => [
				0 => "Hijos",
				1 => "Nietos",
				2 => "Bisnietos",
				3 => "Tataranietos"
			]
		];

		//Recorremos las relaciones
		foreach($inheritance as $type  => $lineNames)
		{
			//Recorremos las relaciones del miembro actual
			foreach($this->getInheritance($type,true) as $line => $family)
			{
				if(empty($family))
					continue;
				//Imprimimos el nombre humano de la linea temporal
				echo "<h3>" . $lineNames[$line] . ":  </h3><k>";
				//Recorremos los miembros de la familia
				foreach($family as $member)
				{
					//Imprimimos el miembro de la familia
					echo "[" . $member->getName() . "] ";
				}
				echo "</k>";
			}
			echo "<br/><br/>";
		}
	}
}
