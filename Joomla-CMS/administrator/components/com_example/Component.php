<?php
/**
 * Created by Herman Peeren, Yepr
 * August 2013
 * GPL
 *
 * A controller in Jooctrine
 */

namespace Example;

use Jooctrine\Component as JooctrineComponent;

// Protect from unauthorized access
defined('_JEXEC') or die();

Class Component extends JooctrineComponent
{
	/**
	 *  Start the component! So: do task from input and render view
	 */
	public function execute($input)
	{
		$model = $this->model;

		// Get the view from the input plus instantiate
		$viewname = $input->get('view','test');
		$viewname_fqn = $this->viewnamespace . $viewname . '\\Html';

		$view = new $viewname_fqn();
		$view->setModel($model, true);

		// set some model-state

		// Render the view
		$view->display($tpl);
	}
}