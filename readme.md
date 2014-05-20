Using Doctrine2 ORM in Joomla!
===

WORK IN PROGRESS

example of how I use Doctrine ORM in Joomla CMS
including Doctrine 2.4 ORM (and dependencies) in libraries/vendor-library

Will upload an example component (with Albums, Tracks and Artists)

I put an EntityManager in a Model.
A basic model and a EntityManagerFactory are in libraries/Jooctrine.
In the entry-file of a component I dispatch the component (componentdispatcher-system-plugin).
In the Componentdispatcher-plugin I instantiate the Component and Model. I get the EntityManager,
inject it into the Model and inject the Model into the component. Then I execute the component. The component has one
/model-directory in which an /entities-directory, a /repositories-directory and a model called domain.php.

The view-templates work the same as in Joomla; probably including overrides (not tested). Working on a FormMapper to match
input form-data with entity-methods in the model.

