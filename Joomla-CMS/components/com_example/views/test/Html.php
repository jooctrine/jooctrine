<?php
namespace Example\Site\views\test;

use Jooctrine\View;

Class Html extends View
{
	public function display($persons=null)
	{

		echo "TEST: this is the site test-view<br />--";
	}

}