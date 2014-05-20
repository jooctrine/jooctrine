<?php
/**
 * Created by Herman Peeren, Yepr
 * November 2013
 * GPL
 *
 * Doctrine's EntityRepository extended with binding capabilities
 * (preserving transparency of persistence)
 * possible improvement: adding a binder to the EntityRepository for SRP
 *
 * inspired by: http://www.paulderaaij.nl/2012/07/29/joomla-and-doctrine-experimenting-with-automated-binding/
 * (he puts the binding logic in the Entity itself, by extending an Entity from BaseModel)
 *
 */

namespace Jooctrine;

use Doctrine\ORM\EntityRepository as DoctrineEntityRepository;

class EntityRepository extends DoctrineEntityRepository
{

	// TODO: Still has to be finished, this doesn't work at all yet!!! Just to get the idea...

	/**
	 * Method to bind an associative array or object to an entity, recursively if necessary.
	 * This method only binds properties for which setters are available.
	 * TODO: optionally take an array of properties to ignore when binding.
	 *
	 * @param   \JInput         $input
	 * @param   array|string    $passedEntities
	 *
	 * @return  boolean true on success.
	 *
	 * @link    http://docs.joomla.org/JTable/bind
	 */
	public function bind(\JInput $input, $passedEntities = array(), $propertyName = null)
	{
		$classMetadata = $this->getClassMetadata();

		// We use the short class name as an identifier key in the value set.
		// If no identifier is given, use the class name of the current entity as default
		if( $propertyName == null) {
			$fqnParts = explode('\\', $classMetadata->getName());
			$parentClass = $fqnParts[count($fqnParts) - 1];
		} else {
			$parentClass = $propertyName;
		}

		// Hold a reference of the entities we have processed already
		$passedEntities[] = $classMetadata->getName();
		foreach( $classMetadata->getFieldNames() as $property ) {
			if ($this->hasSetterForProperty($property) && !$classMetadata->hasAssociation($property)) {
				$inputData = $input->getArray(array($parentClass,'jform'));

				// Check if the property is present in the given value set
				if(array_key_exists($property, $inputData) === false) {
					continue;
				}

				$value = $inputData[$property];

				if( $classMetadata->getTypeOfField($property) == 'datetime') {
					$this->setPropertyValue($property, new \DateTime($value));
				} else {
					$this->setPropertyValue($property, $value);
				}
			}
		}

		// Walk through all associations to set the correct value if found in the value set
		foreach( $classMetadata->getAssociationMappings() as $association ) {
			$getterMethodName = 'get' . ucfirst($association['fieldName']);
			$entity = $this->$getterMethodName();
			$inputData = $input->getArray(array($parentClass,'jform'));

			// See if we have data for the association in the given value set
			if(array_key_exists($association['fieldName'], $inputData) === false) {
				continue;
			}

			$value = $inputData[$association['fieldName']];

			if( !is_null($entity) && !in_array(get_class($entity), $passedEntities)) {

				// If the association is a collection process it by each element
				if ( $classMetadata->isCollectionValuedAssociation($association['fieldName']) ) {;
					$repository =  $this->getEntityManager()->getRepository($entity->getTypeClass()->getName());// --------------
					$associatedEntity = $repository->find( $value );
					if( $entity instanceof \Doctrine\Common\Collections\ArrayCollection ) {
						$entity->add($associatedEntity);
					} else {
						if( isset( $association['mappedBy']) ) {
							$associatedEntity->setPropertyValue($association['mappedBy'], $this);
						}

						$collection = new \Doctrine\Common\Collections\ArrayCollection();
						$collection->add($associatedEntity);
						$this->setPropertyValue($association['fieldName'], $collection);
					}

				} else {
					// We only have one entity, so call the populate for the associated
					// entity and do the same thing again
					$entity->bind($input, $passedEntities, $association['fieldName']);//---------------------------------
				}
			}
		}
	}

	/**
	 *
	 * Determine the setter name of the function
	 *
	 * @param $property
	 * @return string
	 */
	private function getPropertySetterName($property)
	{
		return 'set' . ucfirst($property);
	}

	private function hasSetterForProperty($property)
	{
		$setterName = $this->getPropertySetterName($property);
		return is_callable(array($this, $setterName));
	}

	private function setPropertyValue($property, $value)
	{
		$value = $this->findValueRelationForProperty($property, $value);
		call_user_func(array($this, $this->getPropertySetterName($property)), $value);
	}

	/**
	 *
	 * It is likely that a property is a reference to an other entity. We can't just store the id to the property, but need a instance to
	 * be set on the property.
	 *
	 * This function checks the Doctrine metadata information for a relation and if so it uses that information to bind the right
	 * instance on the property.
	 *
	 * @param $property
	 * @param $value
	 * @return mixed
	 * @throws \InvalidArgumentException
	 */
	private function findValueRelationForProperty($property, $value)
	{
		$em = $this->getEntityManager();
		$metadataFactory = $em->getMetadataFactory();
		$entityMetadataInfo = $metadataFactory->getMetadataFor(get_class($this));

		try {
			$associationMapping = $entityMetadataInfo->getAssociationMapping($property);
		} catch (\Exception $e) {
			$associationMapping = null;
		}

		if ($associationMapping == null) {
			return $value;
		}

		$repository = $em->getRepository($associationMapping['targetEntity']);

		if ($repository == null) {
			throw new \InvalidArgumentException('Not able to bind reference');
		}

		$items = $repository->findById($value);
		return $items[0];
	}

}