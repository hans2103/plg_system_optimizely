<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  System.Optimizely
 *
 * @author      Hans Kuijpers <info@hkweb.nl>
 * @copyright   (c) 2015 Hans Kuijpers. All Rights Reserved.
 * @license     GPLv3 http://www.gnu.org/licenses/gpl.html
 */
defined('_JEXEC') or die;

// Import the parent class
jimport( 'joomla.plugin.plugin' );

class plgSystemOptimizely extends JPlugin
{
	// Plugin info constants
	const TYPE = 'system';
	const NAME = 'optimizely';

	/**
	 * Plugin details from DB
	 *
	 * @var  object
	 */
	private $plugin;

	/**
	 * Plugin parameters
	 *
	 * @var  JRegistry
	 */
	public $params;

	/**
	 * Path to the plugin folder
	 *
	 * @var  string
	 */
	private $pathPlugin = null;

	/**
	 * Base Url to the plugin folder
	 *
	 * @var  string
	 */
	private $urlPlugin;

	/**
	 * Is this plugin enabled in frontend?
	 *
	 * @var  boolean
	 */
	private $frontendEnabled 	= true;

	/**
	 * Is this plugin enabled on backend?
	 *
	 * @var  boolean
	 */
	private $backendEnabled 	= false;

	/**
	 * Constructor
	 *
	 * @param   mixed  &$subject  Subject
	 */
	function __construct( &$subject )
	{
		parent::__construct($subject);

		// Load plugin parameters
		$this->plugin = JPluginHelper::getPlugin(self::TYPE, self::NAME);
		$this->params = new JRegistry($this->plugin->params);

		// Init folder structure
		$this->initFolders();

		// Load plugin language
		$this->loadLanguage('plg_' . self::TYPE . '_' . self::NAME, JPATH_ADMINISTRATOR);
	}



	/**
	 * This event is triggered after pushing the document buffers into the template placeholders,
	 * retrieving data from the document and pushing it into the into the JResponse buffer.
	 * http://docs.joomla.org/Plugin/Events/System
	 *
	 * @return boolean
	 */
	function onAfterRender()
	{
		// Validate view
		if (!$this->validateUrl())
		{
			return;
		}

		// Required objects
		$pageParams = JFactory::getApplication()->getParams();
		$projectid  = $this->params->get('projectid', 0);

		// Check if we have to disable Mootools for this item
		$mode = $pageParams->get('optimizely', $this->params->get('defaultMode', 0));

		if (!$mode || !boolval($projectid))
		{
			return;
		}

		// Get the generated content
		$body = JResponse::getBody();

		// Add Optimizely Script to <head>
		$body = $this->addScriptToHead($body, $projectid);

		// Add class to <body>
		$body = $this->addClassToBody($body, 'possible-ab-test');

		// return the new body
		JResponse::setBody($body);
	}

	/**
	 * Add optimizely script directly after head tag
	 *
	 * @param   string  $html  html that should contain the <head> tag
	 * @param   string  $projectid  the optimizely project id
	 *
	 * @return string
	 */
	private function addScriptToHead($html, $projectid)
	{
		return preg_replace(
			'#<head.*?>#',
			'\0' . "\n  " . '<script src="//cdn.optimizely.com/js/' . $projectid . '.js"></script>',
			$html, 1
		);
	}

	/**
	 * Add class to body tag
	 *
	 * @param   string  $html  html that should contain the <body> tag
	 * @param   string  $class  the class to indacate a running A/B test (useful for Google Tag Manager)
	 *
	 * @return string
	 */
	private function addClassToBody($html, $class)
	{
		if(!preg_match('#<body([^>]*)>#s', $html, $match))
		{
			return $html;
		}

		if(strpos($match['1'], 'class="') !== false)
		{
			$body_tag = str_replace('class="', 'class="' . $class . ' ', $match['0']);

			return str_replace($match['0'], $body_tag, $html);
		}

		return str_replace('<body', '<body class="' . $class . '"', $html);
	}


	/**
	 * Change forms before they are shown to the user
	 *
	 * @param   JForm  $form  JForm object
	 * @param   array  $data  Data array
	 *
	 * @return boolean
	 */
	public function onContentPrepareForm($form, $data)
	{
		// Check we have a form
		if (!($form instanceof JForm))
		{
			$this->_subject->setError('JERROR_NOT_A_FORM');

			return false;
		}

		// Extra parameters for menu edit
		if ($form->getName() == 'com_menus.item')
		{
			$form->loadFile($this->pathPlugin . '/forms/menuitem.xml');
		}

		return true;
	}

	/**
	 * initialize folder structure
	 *
	 * @return none
	 */
	private function initFolders()
	{
		// Path
		$this->pathPlugin = JPATH_PLUGINS . '/' . self::TYPE . '/' . self::NAME;

		// Url
		$this->urlPlugin = JURI::root(true) . "/plugins/" . self::TYPE . "/" . self::NAME;
	}

	/**
	 * validate if the plugin is enabled for current application (frontend / backend)
	 *
	 * @return boolean
	 */
	private function validateApplication()
	{
		$app = JFactory::getApplication();

		if ( ($app->isSite() && $this->frontendEnabled) || ($app->isAdmin() && $this->backendEnabled) )
		{
			return true;
		}

		return false;
	}

	/**
	 * Custom method for extra validations
	 *
	 * @return true
	 */
	private function validateExtra()
	{
		return $this->validateApplication();
	}

	/**
	 * Is the plugin enabled for this url?
	 *
	 * @return boolean
	 */
	private function validateUrl()
	{
		if (method_exists($this, 'validateExtra'))
		{
			return $this->validateExtra();
		}

		return true;
	}
}
