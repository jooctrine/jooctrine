<?php
/**
 * Created by Herman Peeren, Yepr
 * August 2013
 * GPL
 *
 * A controller in Joocrine
 */

namespace Jooctrine;

// Protect from unauthorized access
defined('_JEXEC') or die();

Abstract Class Component
{
	public $model;
	protected $isAdmin = true;
	protected $componentname;
	protected $componentnamespace_admin;
	protected $componentnamespace_site;
	protected $viewnamespace;

	/**
	 * constuctor
	 */
	public function __construct($config = null)
	{
		$this->isAdmin = $config['isAdmin'];
		$this->componentname = $config['componentname'];

		//set namespace for site or admin

		$this->componentnamespace_admin = $config['componentnamespace_admin'];
		$this->componentnamespace_site  = $config['componentnamespace_site'];
		$this->viewnamespace            = $config['viewnamespace'];
	}

	/**
    *  Start the component! So: do task on input and render view
    */
	public function execute($input)
	{
		// Get the task from the input

		// Bind the input to the entities if needed (+ persist)

		// Get the view from the input

		// Render the view
	}

	/**
	 * Set the model (N.B.: one model at a Jooctrine-component)
	 */
	public function setModel($model)
	{
		$this->model = $model;
	}
	function checkACL()
	{
		$app 			= JFactory::getApplication();
		if($app->isAdmin())
		{
			// TODO: ACL bladibla....
		}

		return true;
	}
}