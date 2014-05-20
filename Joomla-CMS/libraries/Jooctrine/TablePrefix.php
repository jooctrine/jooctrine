<?php
/**
 * Created by Herman Peeren, Yepr
 * August 2013; version 3 March 2014
 * GPL
 *
 * Change the #__ table prefix, as normally used in Joomla!, to the currently used prefix.
 */

namespace Jooctrine;
use \Doctrine\ORM\Event\LoadClassMetadataEventArgs;

class TablePrefix
{
	protected $prefix = '';

	public function __construct($prefix)
	{
		$this->prefix = (string) $prefix;
	}

	public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs)
	{
		$classMetadata = $eventArgs->getClassMetadata();
		$classMetadata->setTableName(str_replace('#__', $this->prefix, $classMetadata->getTableName()));
		foreach ($classMetadata->getAssociationMappings() as $fieldName => $mapping) {
			if ($mapping['type'] == \Doctrine\ORM\Mapping\ClassMetadataInfo::MANY_TO_MANY) {
				$mappedTableName = $classMetadata->associationMappings[$fieldName]['joinTable']['name'];
				$classMetadata->associationMappings[$fieldName]['joinTable']['name'] = str_replace('#__', $this->prefix, $mappedTableName);
			}
		}
	}

}