Using Doctrine2 ORM in Joomla!
===

WORK IN PROGRESS
Warning: I'm changing some things at the moment, so things might not work properly!
Especially changing all namespaces to upercase (and using PSR-4 instead PSR-0). Next week at JAB it should be alright...

example of how I use Doctrine ORM in Joomla CMS. This repo
includes Doctrine 2.4 ORM (and dependencies) in libraries/vendor-library

Will upload an example component (with Albums, Tracks and Artists) a.s.a.p. Am working on a step-by-step tutorial.

I put an EntityManager in a Model.
A basic model and a EntityManagerFactory are in libraries/Jooctrine.
In the entry-file of a component I dispatch the component (componentdispatcher-system-plugin).
In the Componentdispatcher-plugin I instantiate the Component and Model. I get the EntityManager,
inject it into the Model and inject the Model into the component. Then I execute the component. The component has one
/model-directory in which an /entities-directory, a /repositories-directory and a model called domain.php.

The view-templates work the same as in Joomla; probably including overrides (not tested). Working on a FormMapper to match
input form-data with entity-methods in the model.

------

I've changed some things in the beginning of this year because we now have PSR-4 available. My Joomla-components are namespaced. I was first sticking to the Joomla-CMS-convention to make all directory-names small case and hence also started the namespace with a small case. I'm now in the transition to stick to the more common way to start namespaces with a capital and hence the directory-names too, so you can use the common Composer PSR-4 autoloader. But I sometimes have some issues with the case sensitivity on Linux. BTW only last week heard a very good reason to start namespaces with a capital instead of undercase: then you never have issues when using namespcaces in a string, as a backslash only has special meaning with some small case letters after it. It is not a PSR to start a namespace with a capital and I always thought it was mere convention to do that, but there is some rationale to it too.

Here are some links about using Doctrine with the Joomla Framework:
* in JIssues a branche was started to experiment with it. See http://issues.joomla.org/tracker/jtracker/342 and https://github.com/joomla/jissues/tree/doctrine/src
* and in this thread https://groups.google.com/forum/#!topic/joomla-dev-framework/QXQlAS_zhdA piotr_cz mentiones his DoctrineServiceProvider https://github.com/joomContrib/joomContrib/blob/master/src/Providers/DoctrineServiceProvider.php

In Symfony the EntityManager is mainly used  in controllers (or services, that are a kind of subroutines of those controllers). That philosophy was also used in the experiments of Paul de Raaij when using Doctrine in Joomla CMS in 2011: http://www.paulderaaij.nl/2011/03/05/using-doctrine-in-joomla/ and 2012 using binding : http://www.paulderaaij.nl/2012/07/29/joomla-and-doctrine-experimenting-with-automated-binding/ . Although very dirty, for instance using an EntityManager within an entity to get the metadatainfo for binding, those blogposts were very inspiring for me then. As far as the binding part another inspiration is the Symfony CRUD-generator. But take care with sticking to CRUD too much, as you don't use the real power of ORM then; see http://verraes.net/2013/04/decoupling-symfony2-forms-from-entities/ .  I only use the EntityManager within the Model, to keep it as close as possible to the in Joomla-CMS used philosophy of thin controllers / fat models and being able to use views as I did before in Joomla-CMS.

I use Doctrine in Joomla because it simplifies complex models. Especially when all kinds of relations like one-to-many are in play, things can be so much simpler when you don't stick to the relational db paradigm and Active Record. And although a CMS is generally seen as a rather uninteresting modeling problem that can simply be solved by some CRUD-behaviour, there are many things that would be much simpler when a solid model would be the foundation. I want to beware to not just use an ORM because it is such a fancy toy or hyped as a tool, but to use it because it brings value, that would otherwise be harder to get. The benefits must be more than the costs.

