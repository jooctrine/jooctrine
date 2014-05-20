<?php
/**
 * Created by Herman Peeren, Yepr
 * August - September 2013
 * GPL
 *
 * The model's parent in Jooctrine
 */

namespace Jooctrine;
use Doctrine\ORM\EntityManager;

// Protect from unauthorized access
defined('_JEXEC') or die();

Abstract Class Model
{
	/**
	 * The Entitymanager that handles the entities in this (domain)model
	 * @var EntityManager
	 */
	protected $em;
	protected $componentNamespace;
	protected $entityName;
	protected $entityId;

	/**
	 * Inject Doctrine's EntityManager into the model (maybe better in constructor: you cannot do without it)
	 */
	public function setEntityManager(EntityManager $em)
	{
		$this->em = $em;
	}

	/**
	 * method is needed by JViewLegacy...
	 */
	public function getName()
	{
		return 'test'; // this return-value is not used...
	}

	/**
	 * method to set one entityName as state of this Model
	 */
	public function setEntityName($entityName)
	{
		$this->entityName = $entityName;
	}

	/**
	 * method to set the id of an entity as state of this Model
	 */
	public function setEntityId($entityId)
	{
		$this->entityId = $entityId;
	}

	/**
	 * method to get an instance of the entitythat has been set by this model by $entityName and $entityId
	 */
	public function getEntity()
	{
		return $this->em->find($this->getComponentModelNamespace() . '\\entities\\' . $this->entityName, $this->entityId);
	}

	/**
	 * Method to get a list of a certain entity
	 *
	 */
	public function getList($entityname) {
		/*
		$language = JFactory::getLanguage()->getTag(); //TODO: languageptions
		$dql  = "SELECT e FROM entity:" . $entityname . " e";
		//$dql .= " WHERE p.language IN ('" . $language . "','*')"; // TODO: filteroptions
		//$dql .= " ORDER BY p.lastName ASC"; //TODO: orderoptions
		$query = $this->em->createQuery($dql);
		$list = $query->getResult();
		*/
		$list = $this->em->getRepository($entityname)->findAll();

		return $list;
	}

	/**
	 * Method to get data of a list of entities
	 *
	 */
	public function getData($entitylist) {
		/*
		$language = JFactory::getLanguage()->getTag(); //TODO: languageptions
		$dql  = "SELECT e FROM entity:" . $entityname . " e";
		//$dql .= " WHERE p.language IN ('" . $language . "','*')"; // TODO: filteroptions
		//$dql .= " ORDER BY p.lastName ASC"; //TODO: orderoptions
		$query = $this->em->createQuery($dql);
		$list = $query->getResult();
		*/

		$entityNamespace = '\\'.$this->getComponentModelNamespace().'\\entities\\';

		foreach ($entitylist as $entity)
		{

			if($entity->name=='Person')
			{
				$entity->data = $this->em->getRepository($entityNamespace.$entity->name)->getAllNames('firstname, lastname', 'lastname');
				//dit moet de standaard worden: minimale hoev. velden terug; wrschl. moet ik dit in de ViewForm opgeven
			}
			else
			{
				$entity->data = $this->em->getRepository($entityNamespace.$entity->name)->findAll();
			}

		}

		return $entitylist;
	}
	/**
	 * Method to retrieve the namespace of the component
	 *
	 */
	public function getComponentModelNamespace() {
		if (empty($this->componentNamespace))
		{
			$reflector = new \ReflectionClass($this); // "$this" is the actual (extended) model-class
			$this->componentNamespace = $reflector->getNamespaceName();
		}
		return $this->componentNamespace;
	}
	/**
	 * Method to get the metadata of a list of entities
	 *
	 */
	public function getMetaData($entitylist) {

		$namespace = $this->getComponentModelNamespace().'\\entities\\';

		foreach ($entitylist as $entityname)
		{
			$className=$entityname;
			$metaData[$entityname] = $this->em->getClassMetadata($namespace.$className);
		}

		return $metaData;
	}

}