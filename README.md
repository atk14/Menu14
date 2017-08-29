Menu14
======

Menu14 is a menu generator designated for an ATK14 application.

Menu14 has simple configuration that keeps in mind the ATK14 concept of controllers and actions. Menu14 has no limited count of submenu levels.

Basic usage
-----------

In a controller:

    $menu = new Menu14();
    $submenu = $menu->add("Archive");
    $submenu->add("Top articles",["top_articles/last_month","top_articles/last_year"]);
    $submenu->add("Whole archive",["articles/index"]);


In a template:

    <ul>
    {foreach $menu->getItems() as $item}
      <li{if $item->isActive($controller,$action)} class="active"{/if}>
                                                                          
        <a href="{$item->getUrl()}">{$item->getTitle()}</a>
                                                                          
        {assign var=submenu value=$item->getSubmenu()}
        {if $item->isActive($controller,$action) && !$submenu->isEmpty()}
          <ul>
            {foreach $submenu->getItems() as $s_item}
              ...
            {/foreach}
          </ul>
        {/if}
                                                                          
      </li>
    {/foreach}
    </ul>

Installation
------------

Use the Composer to install Menu14

    cd path/to/your/project/
    composer require atk14/menu14 dev-master

Licence
-------

Files is free software distributed [under the terms of the MIT license](http://www.opensource.org/licenses/mit-license)
