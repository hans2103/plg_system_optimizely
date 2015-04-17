<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  System.Optimizely
 *
 * @author      Hans Kuijpers <info@hkweb.nl>
 * @copyright   (c) 2015 Hans Kuijpers.
 * @license     GPLv3 http://www.gnu.org/licenses/gpl.html
 */

defined('_JEXEC') or die;

JLoader::import('joomla.form.formfield');

/**
 * Fake field to display a header in settings
 *
 * @package     Joomla.Plugin
 * @subpackage  System.Mootable
 * @since       1.1.0
 */
class JFormFieldHeader extends JFormField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  1.1.0
	 */
	var	$type = 'header';

	/**
	 * Method to get the field input markup for a generic list.
	 * Use the multiple attribute to enable multiselect.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   1.1.0
	 */
	function getInput()
	{
		return '';
	}

	/**
	 * Method to get the field label markup.
	 *
	 * @return  string  The field label markup.
	 *
	 * @since   1.1.0
	 */
	function getLabel()
	{
		return '<h4 class="settings-header" style="clear: both;">' . JText::_((string) $this->element['label']) . ':</h4>';
	}
}
