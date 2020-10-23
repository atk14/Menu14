Menu14
======

Menu14 is a menu generator designated for ATK14 applications.

Menu14 has simple configuration that keeps in mind the ATK14 concept of controllers and actions.

Menu14 can build branched navigation structures. There is no limit of submenu levels count.

Menu14 can also be used for breadcrumbs.

Menu14 implements ArrayAccess, Iterator and Countable for easy usage.

Basic usage
-----------

In a controller:

    $menu = new Menu14();

    $submenu = $menu->add("Archive");
    $submenu->add("Whole archive",["articles/index"]);
    $top_articles = $submenu->add("Top articles","top_articles");
      $top_articles->add("Last Month","top_articles/last_month");
      $top_articles->add("Last Year","top_articles/last_year");

    // another submenu
    $submenu = $menu->add("Information");
    $submenu->add("About us","main/about");
    $submenu->add("Contact","main/contact");

    // disabled items should be displayed non clickable
    $user_menu = $menu->add("User");
    $user_menu->add("Login","logins/create_new",["disabled" => $is_user_logged]);
    $user_menu->add("Logout","logins/destroy",["disabled" => !$is_user_logged]);
    $user_menu->add("Profile","users/detail",["disabled" => !$is_user_logged]);

    $this->tpl_data["menu"] = $menu;

In a template:

    {* file: shared/_menu.tpl *}
    {if !$menu->isEmpty()}
    <ul>
      {foreach $menu->getItems() as $item}
        <li{if $item->isActive()} class="active"{/if}{if $item->isDisabled()} class="disabled"{/if}>

          {if $item->getUrl() && !$item->isDisabled()}
            <a href="{$item->getUrl()}">{$item->getTitle()}</a>
          {else}
            {$item->getTitle()}  
          {/if}

          {* recursion *}
          {render partial="shared/menu" menu=$item->getSubmenu()}
                                                                            
        </li>
      {/foreach}
    </ul>
    {/if}

There are two methods for creating node in a menu.

    $menu->add("Label",$targets_or_url,$options); // creates new menu item and return its submenu (which is at the moment empty)
    $menu->addItem("Label",$targets_or_url,$options); // creates new menu item - this new menu item is returned

Breadcrumbs
-----------

In a controller:

    $breadcrumbs = new Menu14();

    $breadcrumbs[] = ["Home","main/index"];
    $breadcrumbs[] = ["Articles","articles/index"];
    if($tag = $article->getPrimaryTag()){
      $breadcrumbs[] = ["$tag",$this->_link_to(["action" => "articles/index", "tag_id" => $tag])];
    }
    $breadcrumbs[] = "Best article in the universe";

    $this->tpl_data["breadcrumbs"] = $breadcrumbs;

In a template:

    {if sizeof($breadcrumbs)>1} {* It is not so useful to display only a single bread crumb *}
      <ol class="breadcrumb">
        {foreach $breadcrumbs as $breadcrumb}
          <li>
            {if $breadcrumb->getUrl() && !$breadcrumb@last} {* we don't want to have a link on the last breadcrumb *}
              <a href="{$breadcrumb->getUrl()}">{$breadcrumb->getTitle()}</a>
            {else}
              {$breadcrumb->getTitle()}
            {/if}
          </li>
        {/foreach}
      </ol>
    {/if}

Check out http://skelet.atk14.net/en/articles/ to see this example live.

Metadata
--------

Metadata can be set on menus or menu items. It may be useful for storing things like colors, images, fade effects...

    $menu = new Menu14();

    $websites = $menu->add("Frameworks");
    $websites->setMeta("image_url","/public/icons/sprockets.png");

    $item_atk14 = $website->addItem("Atk14","https://www.atk14.net/",[
      "meta" => [
        "image_url" => "/public/icons/atk14_birdie.png",
        "color" => "black"
      ]
    ]);

    $item_laravel = $website->addItem("Laravel","https://laravel.com/");
    $item_laravel->setMeta("image_url","/public/icons/laravel.png");
    $item_laravel->setMeta("color","red");

    // getting metadata

    $websites->getMeta("image_url"); // "/public/icons/sprockets.png"
    $websites->getMeta("color"); // null
    $item_atk14->getMeta("image_url"); // "/public/icons/atk14_birdie.png"
    $item_atk14->getMeta("color"); // "black"
    $item_laravel->getMeta("image_url"); // "/public/icons/laravel.png"
    $item_laravel->getMeta("color"); // "red"

Installation
------------

Use the Composer to install Menu14

    cd path/to/your/project/
    composer require atk14/menu14

Licence
-------

Menu14 is free software distributed [under the terms of the MIT license](http://www.opensource.org/licenses/mit-license)

<!-- vim: et:ts=2 -->
