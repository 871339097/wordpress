<?php
/**
 * Sofort Element
 * 
 * @author SOFORT GmbH (integration@sofort.com)
 *
 * @copyright 2010-2015 SOFORT GmbH
 *
 * @license Released under the GNU LESSER GENERAL PUBLIC LICENSE (Version 3)
 * @license http://www.gnu.org/licenses/lgpl.html
 *
 * @version SofortLib 2.1.2
 *
 * @link http://www.sofort.com/ official website
 */

/**
 *
 * Implementation of a simple element
 *
 */
abstract class SofortElement {
	
	/**
	 * Render the element
	 */
	public abstract function render();
}