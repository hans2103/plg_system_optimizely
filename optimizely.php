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
			return true;
		}

		// Required objects
		$app        = JFactory::getApplication();
		$doc        = JFactory::getDocument();
		$pageParams = $app->getParams();
		$projectid  = $this->params->get('projectid', 0);

		// Check if we have to disable Mootools for this item
		$mode = $pageParams->get('optimizely', $this->params->get('defaultMode', 0));

		if ($mode && boolval($projectid))
		{
			// Get the generated content
			$body = JResponse::getBody();

			// Load Optmizely code
			$pattern     = '<head>';
			$replacement = '<head>
  <script src="//cdn.optimizely.com/js/'.$projectid.'.js"></script>';
			$body        = str_replace($pattern, $replacement, $body);

			JResponse::setBody($body);
		}

		return true;
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