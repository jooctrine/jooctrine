<?php
/**
 * Created by Herman Peeren, Yepr
 * September 2013
 * GPL
 *
 * The view's parent in Jooctrine ------------------ View2 is temporary test-view-parent -------------------------------
 */

namespace Jooctrine;

use \JViewLegacy,
    \JFactory,
    \JToolBarHelper,
	\Doctrine\Common\Collections\ArrayCollection;

// Protect from direct access
defined('_JEXEC') or die();

Abstract Class View extends \JViewLegacy
{
	protected $entitylist; // the list of entities of which we want data TODO: labeling, filtering, ordering and validation
	protected $data; // the data of the entities in the entitylist
	protected $metaData; // the metadata of the entities in the entitylist

	public function __construct()
	{
		$this->entitylist = new ArrayCollection();
	}

	public function display($tpl = null)
	{
		// Options button.
		if (\JFactory::getUser()->authorise('core.admin', 'com_inta'))
		{
			\JToolBarHelper::preferences('com_inta');
		}

		$model = $this->getModel();
		if ($this->entitylist->count()>0)
		{
			//$this->metaData = $model->getMetaData($this->entitylist);
			$this->data = $model->getData($this->entitylist);
		}

		parent::display();
	}

}