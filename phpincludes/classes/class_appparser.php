<?php
namespace Contentomat;

use Contentomat\Session;

/**
 * This Parser can be extended by own (project-specific) macros etc.
 */
class AppParser extends Parser {

	/**
	 * @var string 	The templates path, formerly set in controller, would now reside here!
	 */
	public $templatesPath;

	protected $globals;


	/**
	 * Some convenience methods to be included in Parser maybe..
	 */

	/**
	 * set: Short for setParserVar / setMultipleParserVars
	 * @param mixed 	The variable to be set
	 * @return void
	 */
	public function set($var) {
		if (is_array($var)) {
			return $this->setMultipleParserVars($var);
		}
		else {
			return $this->setParserVar(compact($var));
		}
	}

	public function parseDefault() {

			echo '<pre>'; var_dump("check"); echo '</pre>'; die();
		try {
			if (!preg_match('/^action(\w+)$/', debug_backtrace()[1]['function'], $match)) {
				throw new \Exception('Unknown action. Cannot determine default template.');
			}
			if (empty($match[1])) {
				throw new \Exception('Unknown action. Cannot determine default template.');
			}
			$actionName = strtolower($match[1]);
			echo '<pre>'; var_dump($actionName); echo '</pre>'; die();
			$templatePath = rtrim($this->templatesPath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $actionName . '.tpl';
			if (!file_exists($templatePath) || !is_readable($templatePath)) {
				throw new \Exception(sprintf('No default template found for action "%s". Be sure to create the template file %s', $actionName, $templatePath));
			}
			return $this->parseTemplate($templatePath);
		}
		catch (\Exception $e) {
			die ($e->getMessage());
		}
	}


	/**
	 * Returns the amount of items in an array
	 * 
	 * @param string $value Content
	 * @param array $params void
	 *
	 * @return string 	String representation of the number of items in this array
	 */
	public function macro_COUNT ($value, $params) {

		if (!isset($this->vars[$value])) {
			$replaceData = '0';
		}
		else if (!is_array($this->vars[$value])) {
			$replaceData = '0';
		}
		else {
			$replaceData = sprintf('%s', count((array)$this->vars[$value]));
		}

		return $replaceData;
	}

	/**
	 * Generates a munged Email-Link, so that the email address is unreadable for robots
	 * but readable for humans and decodes on click back to the original email
	 * 
	 * e.g. MUNGEDEMAILLINK(info@example.com)
	 * 
	 * will output
	 * 
	 * <a href="javascript:void(0)" onclick="stufftodecodethemungedemail">info<span>@</span>example<span>.</span>com</a>
	 * 
	 * Needs Javascript enabled
	 * 
	 * @param string $value email address to munge
	 * @param array $params 'mungeTag' => the Tag name to use for munging, default: span
	 *
	 * @return string 	Complete link tag with munged email and decoder onclick 
	 */
	public function macro_MUNGEDEMAILLINK ($email, $params) {

		$mungeTag = !empty($params['mungeTag']) ? $params['mungeTag'] : 'span';

		$mungedEmail = preg_replace('/\./', "<{$mungeTag}>.</{$mungeTag}>", $email);
		$mungedEmail = preg_replace('/(@)/', "<{$mungeTag}>@</{$mungeTag}>", $mungedEmail);
		$onclick = sprintf('(function(e, obj) { obj.setAttribute(\'href\', \'mailto:\' + obj.innerHTML.replace(/(\<\/?%s\>)/g, \'\')); return true; })(event, this);', $mungeTag);

		return sprintf('<a href="javascript:void(0);" onclick="%s">%s</a>', $onclick, $mungedEmail);
	}

	/**
	 * Returns whether a content group has active content elements in it or not
	 * 
	 * @param string $varName	Optional first parameter
	 * @param unknown $params	Optional additional prameters
	 * @return string			Value for canonical tag
	 */
	public function macro_GROUPISEMPTY($params) {
		$colNr = (int)$params;
		$query = sprintf('SELECT COUNT(*) FROM cmt_content_de WHERE cmt_pageid=%u AND cmt_objectgroup=%u AND cmt_visible', $this->pageId, $colNr);
		$this->db->query($query);
		$ret = $this->db->get();
		$n = (int)$ret['COUNT(*)'];
		if ($n > 0) {
			$replaceData = 'false';
		}
		else {
			$replaceData = 'true';
		}
		return $replaceData;
	}

	/**
	 * Yields the number of non-empty (see above) groups
	 * 
	 * @param string $varName	Optional first parameter
	 * @param unknown $params	Optional additional prameters
	 * @return string			Value for canonical tag
	 */
	public function macro_NONEMPTYGROUPS($params) {
		$query = sprintf('SELECT cmt_objectgroup FROM cmt_content_de WHERE cmt_pageid=%u AND cmt_objectgroup > 0 AND cmt_visible GROUP BY cmt_objectgroup;', $this->pageId);
		$this->db->query($query);
		$ret = $this->db->getAll();
		return (count($ret));
	}


	public function setGlobalVar($name = '', $value = '') {
		$this->globals[$name] = $value;
	}

	public function getGlobalVar($name) {
		return (isset($this->globals[$name]) ? $name : '');
	}
	
	/**
	 * Displays the canonical url for the page: Whether the url comes from an included script or from the page's title
	 * 
	 * @param string $varName	Optional first parameter
	 * @param unknown $params	Optional additional prameters
	 * @return string			Value for canonical tag
	 */
	protected function macro_CANONICALURL($varName, $params) {
		
		$url = $this->cmt->getVar('cmtCanonicalUrl');
		
		if (!$url) {
			$url = PAGEURL;
		}
		
		$replaceData = $_SERVER['REQUEST_SCHEME'] . '://' . str_replace('//', '/', $_SERVER['SERVER_NAME'] . '/' . $url);
	
		if ($params[0]) {
			$replaceData = $this->processMacroValue(array_shift($params), $replaceData, $params);
		}

		return $replaceData;
	}
	
	/**
	 * Displays the page's title: Whether the it comes from an included script or from the page's properties
	 * 
	 * @param string $varName	Optional first parameter
	 * @param unknown $params	Optional additional prameters
	 * @return string			Value for page title
	 */
	protected function macro_PAGETITLE($varName, $params) {
		
		$replaceData = $this->cmt->getVar('cmtPageTitle');
		
		if (!$replaceData) {
			$replaceData = PAGETITLE;
		}
		
		if ($params[0]) {
			$replaceData = $this->processMacroValue(array_shift($params), $replaceData, $params);
		}

		return $replaceData;
	}
	
	/**
	 * Displays the page's meta description: Whether the it comes from an included script or from the page's properties
	 * 
	 * @param string $varName	Optional first parameter
	 * @param unknown $params	Optional additional prameters
	 * @return string			Value for page title
	 */
	protected function macro_PAGEMETADESCRIPTION($varName, $params) {
		
		$replaceData = trim($this->cmt->getVar('cmtPageMetaDescription')) ;
		
		if (!$replaceData) {
			$replaceData = $this->pagevars['cmt_meta_description'];
		//{PAGEVAR:halma_meta_description:recursive}
		}
		
		if ($params[0]) {
			$replaceData = $this->processMacroValue(array_shift($params), $replaceData, $params);
		}

		return $replaceData;
	}


	protected function macro_FLASHMESSAGE($value, $params) {

		$flashMessage = $this->session->getSessionVar('flashMessage');
		$this->session->deleteSessionVar('flashMessage');
		$this->session->saveSessionVars();

		if (!empty($flashMessage)) {
			$replaceData = $flashMessage;
		}
		return $replaceData;
	}
}
?>
