<?php
/**
 * Clase que maneja todo lo relativo a la genealogía.
 * Entre ello está la creación de miembros, definiciones de relaciones y mucho más.
 */
class Genealogy
{
	/**
	 * Miembros de la genealogía.
	 *
	 * @var        array
	 */
	protected $_members = [];
	/**
	 * Constantes para setear el tipo de relación.
	 *
	 * @var        string
	 */
	const _CHILD  = "child",
		  _PARENT = "parent";

	/**
	 * Asigna como padre a un miembro dado con respecto a otro.
	 * Si alguno de los dos (padre/hijo) suministrados no existe, los creará automáticamente.
	 *
	 * @param      string  $child    Código del hijo.
	 * @param      string  $parent   Código del padre.
	 *
	 * @return     Genealogy
	 */
	public function addChild($child,$parent)
	{
		return $this->setInheritance(self::_CHILD,$child,$parent);
	}
	/**
	 * Asigna como hijo a un miembro dado con respecto a otro.
	 * Si alguno de los dos (padre/hijo) suministrados no existe, los creará automáticamente.
	 *
	 * @param      string  $parentCode  Código del padre.
	 * @param      string  $childCode   Código del hijo.
	 *
	 * @return     Genealogy
	 */
	public function addParent($parent,$child)
	{
		return $this->setInheritance(self::_PARENT,$parent,$child);
	}

	/**
	 * Asigna herencia a un miembro dado con respecto a otro.
	 * Si alguno de los dos (padre/hijo) suministrados no existe, los creará automáticamente.
	 *
	 * @param      string  $type   Tipo de la herencia. Esta puede ser "parent" o "child".
	 * @param      string  $from   Código del elemento al que se asignará la herencia especificada.
	 * @param      string  $to     Código del elemento al que se vinculará con la herencia especificada.
	 *
	 * @return     Genealogy
	 */
	public function setInheritance($type,$from,$to)
	{
		//Normalizamos los códigos.
		$fromCode = $this->normalizeCode($from);
		$toCode   = $this->normalizeCode($to);
		//Verificamos que ninguno de los códigos esté vacío. De ser así alguno de los códigos suminstrados NO es 
		//una cadena de caracteres, o bien estaba vacío.
		if(!empty($fromCode) && !empty($toCode))
		{
			//Normalizamos el tipo de la herencia
			$type = ($type !== self::_PARENT)?self::_CHILD:$type;
			//Agregamos si no existen aún los miembros
			$this->add($from);
			$this->add($to);
			if(!isset($this->_inheritance[$fromCode]))
			{
				$this->_inheritance[$fromCode] = ["parents"=>[], "childs"=>[]];
			}
			if(!isset($this->_inheritance[$toCode]))
			{
				$this->_inheritance[$toCode] = ["parents"=>[], "childs"=>[]];
			}
			//Guardamos las herencias
			$this->_inheritance[$fromCode][$type][$toCode]= $toCode;
			$this->_inheritance[$toCode][($type === self::_CHILD)?self::_PARENT:self::_CHILD][$fromCode] = $fromCode;
		}
		return $this;
	}
	/**
	 * Devuelve los códigos de los miembros relacionados con un miembro especificado para una herencia dada.
	 *
	 * @param      string   $type       Tipo de herencia que se busca.
	 * @param      string   $code       Código del miembro.
	 * @param      boolean  $recursive  Determina si la búsqueda de herencias será recursiva o no (Por defecto falsoo)
	 *
	 * @return     array
	 */
	public function getInheritance($type,$code,$recursive=FALSE)
	{
		//Normalizamos el código
		$code = $this->normalizeCode($code);
		//Si el código está vacío o el miembro no tiene ninguna herencia asignada, devolvemos un array vacío.
		if(empty($code) || !isset($this->_inheritance[$code]) || !isset($this->_inheritance[$code][$type]))
			return [];
		//Normalizamos el tipo de la herencia
		$type = ($type !== self::_PARENT)?self::_CHILD:$type;

		//Si el recursivo está desactivado, devolvemos directamente los valores almacenados
		if($recursive === false || empty($this->_inheritance[$code][$type]))
		{
			return $this->_inheritance[$code][$type];
		}
		//Herencia total
		$inheritance = [
			0 => $this->_inheritance[$code][$type] //Hijos o padres (primera línea de herencia) 
		];
		//Puntero actual a recorrer
		$pointer = $this->_inheritance[$code][$type];
		//Creamos un bucle infinito
		for($n = 1 ; $n > 0 ; $n++)
		{
			//Si el puntero actual no tiene integrantes, detenemos el bucle.
			if(empty($pointer))
				break;
			//Herencia con el # de la linea generacional
			$inheritance[$n] = [];
			//Recorremos el puntero
			foreach($pointer as $code)
			{
				//Guardamos en una variable las herencias del miembro analizado.
				//Si este no tiene más herencia, lo saltamos.
				$tmp = $this->getInheritance($type,$code,FALSE);
				if(empty($tmp))
					continue;
				//Si tuvo herencia, guardamos los códigos dentro de la línea generacional
				$inheritance[$n] = array_merge($inheritance[$n],$tmp);
			}
			//Cambiamos el puntero a la línea generacional actual
			$pointer = $inheritance[$n];
		}
		//Devolvemos la herencia
		return $inheritance;
	}

	/**
	 * Devuelve los códigos de los hijos relacionados con un miembro dado.
	 *
	 * @param      string  $code       Código del miembro.
	 * @param      string  $recursive  Determina si  la búsqueda será recursiva o no (por defecto falso).
	 *
	 * @return     array
	 */
	public function getChilds($code,$recursive=FALSE)
	{
		return $this->getInheritance($code,$recursive);		
	}
	/**
	 * Devuelve las relaciones de padres de un miembro dado.
	 *
	 * @param      string  $code       Código del miembro.
	 * @param      string  $recursive  Determina si  la búsqueda será recursiva o no (por defecto falso).
	 *
	 * @return     array
	 */
	public function getParents($code,$recursive=FALSE)
	{
		return $this->getInheritance($code,$recursive);		
	}

	/**
	 * Devuelve un miembro a partir del código del mismo.
	 * Si el miembro no existe lo autocreará.
	 *
	 * @param      string  $code   Código.
	 *
	 * @return     Member|false    Devolverá la instancia del miembro en caso de tener un código válido, de lo contrario FALSE.
	 */
	public function get($code)
	{
		//Almacenamos el nombre original para evitar se guarde en minúsculas si el mismo no existía.
		$old = $code;
		//Normalizamos el código.
		$code = $this->normalizeCode($code);
		//Si el código no está vacío
		if(!empty($code))
		{
			//Si el miembro no existe lo creamos
			if(!isset($this->_members[$code]))
			{
				$this->add($old);
			}


			//Devolvemos la instancia del miembro
			return $this->_members[$code];
		}
		//Devolvemos false
		return false;
	}
	/**
	 * Normaliza el nombre de un miembro.
	 *
	 * @param      string  $name   Nombre del miembro.
	 *
	 * @return     String
	 */
	public static function normalizeName($name)
	{
		return is_string($name) ? trim($name) : "";
	}
	/**
	 * Normaliza el código de un miembro.
	 * A diferencia de Genealogy::normalizeName, en este método se convierte a minúsculas el código para hacerlo CI(case insensitive)
	 *
	 * @param      string  $code   Código a normalizar.
	 *
	 * @return     string
	 */
	public function normalizeCode($code)
	{
		return strtolower($this->normalizeName($code));
	}
	/**
	 * Agrega un miembro a la genialogía.
	 *
	 * @param      string  $name   Nombre del miembro por agregar.
	 * @param      string  $code   Código para llamar al miembro (opcional).
	 */
	public function add($name,$code=NULL)
	{
		//Normalizamos
		$name = $this->normalizeName($name);
		$code = $this->normalizeCode($code);

		//Si el nombre no está vacío
		if(!empty($name))
		{


			//Convertimos en minúsculas el código directamente sin usar el método normalizeCode, para evitar 
			//rutinas redundantes (volver a llamar normalizeName).
			$code = empty($code)?strtolower($name):$code;
			//Si el código existe
			if(isset($this->_members[$code]))
			{
				//Cambiamos sólo el nombre
				$this->_members[$code]->setName($name);
			}
			//Si el código no existe
			else
			{
				//Generamos el nuevo miembro y lo almacenamos
				$this->_members[$code] = new Member($this,$code,$name);
			}
		}
		//Devolvemos la instancia de Genealogy
		return $this;
	}
}
