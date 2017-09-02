<?php
/**
 * Menu14 is a menu and breadcrumbs generator designated for ATK14 applications
 *
 * For more information see https://github.com/atk14/Menu14/blob/master/README.md
 */
class Menu14 implements ArrayAccess, Iterator, Countable {

	protected $parent_menu = null;
	protected $items = array();
	protected $identifier = "";

	function __construct(&$parent_menu = null,$identifier = ""){
		$this->parent_menu = $parent_menu;
		$this->identifier = $identifier;
	}

	/**
	 * Add new item to the menu
	 *
	 *	$menu->add("Articles",["articles"]);
	 *	$menu->add("Register",["logins/create_new","users/create_new"]);
	 *
	 *	$menu->add("Anual Report",$this->_link_to(["action" => "articles/detail", "id" => 1234]));
	 *
	 * For future referencing an identifier can be specified
	 *	$menu->add("Articles",["articles"],"articles");
	 *
	 * Also "active" status can be specified. Otherwise there is an autodetection.
	 *	$menu->add("Anual Report",$this->_link_to(["action" => "articles/detail", "id" => 1234]),["active" => true, "identifier" => "anual_report"]);
	 *
	 * @return Menu14Item New item
	 */
	function &addItem($snippet,$targets = array(),$options = array()){
		if(is_array($snippet)){
			$targets = $snippet[1];
			$snippet = $snippet[0];
		}

		if(is_string($options)){
			$options = array("identifier" => $options);
		}

		if(is_bool($options)){
			$options = array("active" => $options);
		}

		$options += array(
			"identifier" => "",
			"active" => null, // null, true, false, "auto"; null means auto detection
			"disabled" => null, // null, true, false, "auto"; null means auto detection (no link -> disabled)
		);

		$child_menu = new Menu14($this,$options["identifier"]);
		$this->child_menu = &$child_menu;

		$this->items[] = new Menu14Item($this,$child_menu,array(
			"snippet" => $snippet,
			"targets" => $targets,
			"active" => $options["active"],
			"disabled" => $options["disabled"],
		));
		return $this->items[sizeof($this->items)-1];
	}

	/**
	 * The same like addItem() but it returns Menu14
	 *
	 * @return Menu14
	 */
	function &add($snippet,$targets = array(),$options = array()){
		$item = $this->addItem($snippet,$targets,$options);
		$submenu = $item->getSubmenu();
		return $submenu;
	}

	function &getParentMenu(){
		return $this->parent_menu;
	}

	function getItems(){
		return $this->items;
	}

	function isEmpty(){
		return sizeof($this->items)==0;
	}

	function getIdentifier(){
		return $this->identifier;
	}

	function getPath(){
		$out[] = $this->getIdentifier();
		$item = $this;
		while($parent = $item->getParentMenu()){
			$id = $parent->getIdentifier();
			(strlen($id)) && ($out[] = $id);
			$item = $parent;
		}
		$out = array_reverse($out);
		return join("/",$out);
	}

	/*** functions implementing array like access ***/
	/**
	 * @ignore
	 */
	function offsetGet($value){ return $this->items[$value]; }

	/**
	 * @ignore
	 */
	function offsetSet($key, $value){
		if(!isset($key)){
			$key = sizeof($this->items);
		}
		$this->add($value);
	}

	/**
	 * @ignore
	 */
	function offsetUnset($value){ $this->items[$value]; }

	/**
	 * @ignore
	 */
	function offsetExists($value){ return isset($this->items[$value]); }

	/**
	 * @ignore
	 */
	function current(){ return current($this->items); }

	/**
	 * @ignore
	 */
	function key(){ return key($this->items); }

	/**
	 * @ignore
	 */
	function next(){ return next($this->items); }

	/**
	 * @ignore
	 */
	function rewind(){ reset($this->items); }

	/**
	 * @ignore
	 */
	function valid(){
		$key = key($this->items);
		return ($key !== null && $key !== false);
	}

	/**
	 * @ignore
	 */
	function count(){ return sizeof($this->items); }
}

class Menu14Item {

	protected $menu = null;
	protected $child_menu = null;

	protected $targets = array();
	protected $snippet = "";
	protected $active = null;
	protected $disabled = null;

	protected $current_controller = null;
	protected $current_action = null;

	function __construct(&$menu,&$child_menu,$options = array()){
		$options += array(
			"snippet" => "Menu",
			"targets" => array(),
			"active" => null, // null, true, false, "auto"; null means auto detection
			"disabled" => null, // null, true, false, "auto"; null means auto detection (no link -> disabled)
		);

		if(!is_array($options["targets"])){
			if($options["targets"]){
				$options["targets"] = array($options["targets"]); // "articles" -> ["articles"]
			}else{
				$options["targets"] = array(); // "" -> []
			}
		}

		$this->targets = $options["targets"];
		$this->snippet = $options["snippet"];
		$this->active = $options["active"];
		$this->disabled = $options["disabled"];

		$this->menu = &$menu;
		$this->child_menu = &$child_menu;
	}

	function getTitle(){
		return $this->snippet;
	}

	function getUrl($options = array()){
		return $this->getLink($options);
	}

	/**
	 * Generic markup of the item
	 */
	function getMarkup($linkOptions = array()){
		($link = $this->getLink($linkOptions)) || ($link = "#");

		$out = sprintf('<a href="%s">%s</a>',htmlentities($link),htmlentities($this->getTitle()));

		return $out;
	}

	function getLink($linkOptions = array()){
		global $ATK14_GLOBAL;

		$linkOptions_orig = $linkOptions;

		$linkOptions += array(
			"namespace" => $ATK14_GLOBAL->getValue("namespace"),
			"lang" => $ATK14_GLOBAL->getLang(),
		);

		$namespace = $linkOptions["namespace"];
		$lang = $linkOptions["lang"];

		unset($linkOptions["namespace"]);
		unset($linkOptions["lang"]);

		foreach($this->targets as $target){
			if(preg_match('/^(\/|https?:)/',$target)){
				return $target;
			}

			$ary = explode("/",$target);
			$params = sizeof($ary)==1 ? array("controller" => $ary[0], "action" => "index") : array("controller" => $ary[0], "action" => $ary[1]);
			$params["namespace"] = $namespace;
			$params["lang"] = $lang;

			return Atk14Url::BuildLink($params, $linkOptions);
		}

		$submenu = $this->getSubmenu();
		foreach($submenu->getItems() as $s_item){
			if($link = $s_item->getLink($linkOptions_orig)){ return $link; }
		}
	}

	function &getSubmenu(){
		return $this->child_menu;
	}

	function &getParentItem(){
		$this->menu->getParentMenu();
	}

	/**
	 *
	 */
	function isActive($current_controller = null,$current_action = null){
		// takova pekna volovina na to, jak si zapamatovat naposledy predany controller a action
		if(!isset($current_controller)){ $current_controller = $this->current_controller; }
		if(!isset($current_action)){ $current_action = $this->current_action; }
		//
		if(!isset($current_controller)){ $current_controller = $GLOBALS["ATK14_GLOBAL"]->getValue("controller"); }
		if(!isset($current_action)){ $current_action = $GLOBALS["ATK14_GLOBAL"]->getValue("action"); }
		//
		$this->current_controller = $current_controller;
		$this->current_action = $current_action;

		if(isset($this->active) && strtolower($this->active)!=="auto"){
			// !! true or false
			return $this->active;
		}

		foreach($this->targets as $ctrl){
			$ary = explode("/",$ctrl); // "articles/archive" -> ["articles","archive"]
			if(sizeof($ary)==1 && $ary[0]==$current_controller){
				return true;
			}
			if(sizeof($ary)==2 && $ary[0]==$current_controller && $ary[1]==$current_action){
				return true;
			}
		}

		$submenu = $this->getSubmenu();
		foreach($submenu->getItems() as $item){
			if($item->isActive($current_controller,$current_action)){ return true; }
		}

		return false;
	}

	/**
	 * Is this menu item disabled?
	 */
	function isDisabled(){
		if(is_null($this->disabled) || $this->disabled==="auto"){
			// no link -> disabled by default
			return $this->getUrl() ? false : true;
		}
		return (boolean)$this->disabled;
	}

	/**
	 * Sets this menu item to a disabled state
	 *
	 *	$item->setDisabled();
	 *	$item->setDisabled(true);
	 *
	 * To deactivate the disabled state:
	 *
	 *	$item->setDisabled(false);
	 */
	function setDisabled($disabled = true){
		$this->disabled = (boolean)$disabled;
	}

	function getIdentifier(){ return $this->child_menu->getIdentifier(); }
	function getPath(){ return $this->child_menu->getPath(); }

	/**
	 * $item->getTargets(); // array("articles", "archives/articles")
	 */
	function getTargets(){
		return $this->targets;
	}

	function __toString(){
		return $this->getMarkup();
	}
}
