<?php
/**
 * Created by Herman Peeren, Yepr
 * September 2013
 * GPL
 *
 * The view's parent in Jooctrine ------------------ View2 is temporary test-view-parent -------------------------------
 */

namespace Jooctrine;

use \JViewLegacy;

// Protect from direct access
defined('_JEXEC') or die();

Abstract Class Outputviewdetail extends \JViewLegacy
{
	protected $entity; // the entity (and relations) that is going to be displayed in this view



	public function display($tpl = null)
	{
		$model = $this->getModel();
		$this->entity = $model->getEntity();

		parent::display($tpl);
	}

}