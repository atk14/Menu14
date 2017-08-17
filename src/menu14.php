<?php
/**
 * Menu14 is a menu generator designated for an ATK14 application
 *
 * Menu14 has simple configuration that keeps in mind the ATK14 concept of controllers and actions.
 * Menu14 has no limited count of submenu levels.
 *
 * $menu = new Menu14();
 * $submenu = $menu->add("Archive");
 * $submenu->add("Top articles",["top_articles/last_month","top_articles/last_year"]);
 * $submenu->add("Whole archive",["articles/index"]);
 *
 *	<ul>
 *	{foreach $menu->getItems() as $item}
 *		<li{if $item->isActive($controller,$action)} class="active"{/if}>
 *
 *			{$item->getMarkup()}
 *
 *			{assign var=submenu value=$item->getSubmenu()}
 *			{if $item->isActive($controller,$action) && !$submenu->isEmpty()}
 *				<ul>
 *					{foreach $submenu->getItems() as $s_item}
 *						...
 *					{/foreach}
 *				</ul>
 *			{/if}
 *
 *		</li>
 *	{/foreach}
 *	</ul>
 *
 *	{* file: app/layouts/default.tpl *}
 *	...
 *	{render partial="shared/layout/menu" menu=$menu}
 *	...
 *
 *	{* file: app/views/shared/layout/_menu.tpl *}
 * 	{if !$menu->isEmpty()}
 *	<ul>
 *		{foreach $menu->getItems() as $item}
 *		<li{if $item->isActive($controller,$action)} class="active"{/if}>
 *			{$item->getMarkup()}
 *			{render partial="shared/layout/menu" menu=$item->getSubmenu()}
 *		</li>
 *	</ul>
 *	{/if}
 */
class Menu14 {

	protected $parent_menu = null;
	protected $items = array();
	protected $identifier = "";

	function __construct(&$parent_menu = null,$identifier = ""){
		$this->parent_menu = $parent_menu;
		$this->identifier = $identifier;
	}

	/**
	 * $menu->add("Articles",["articles"]);
	 * $menu->add("Register",["logins/create_new","users/create_new"]);
	 *
	 * For future referencing an identifier can be specified
	 * $menu->add("Articles",["articles"],"articles");
	 */
	function &add($snippet,$targets = array(),$identifier = ""){
		$child_menu = new Menu14($this,$identifier);
		$this->child_menu = &$child_menu;

		$this->items[] = new Menu14Item($this,$child_menu,array(
			"snippet" => $snippet,
			"targets" => $targets,
		));
		return $child_menu;
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
}

class Menu14Item {

	protected $menu = null;
	protected $child_menu = null;

	protected $targets = array();
	protected $snippet = "";

	protected $current_controller = null;
	protected $current_action = null;

	function __construct(&$menu,&$child_menu,$options = array()){
		$options += array(
			"snippet" => "Menu",
			"targets" => array()
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

		$this->menu = &$menu;
		$this->child_menu = &$child_menu;
	}

	function getMarkup($linkOptions = array()){
		($link = $this->getLink($linkOptions)) || ($link = "#");

		$out = sprintf('<a href="%s" title="%s">%s</a>',$link,htmlentities($this->snippet, ENT_COMPAT | ENT_HTML401 | ENT_QUOTES),$this->snippet);

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

		foreach($this->targets as $ctrl){
			$ary = explode("/",$ctrl);
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
