<?php
class TcMenu14 extends TcBase {

	function test(){
		$menu = new Menu14();
		//
		$sites = $menu->add("Atk14 Sites");
		$sites->add("Atk14","http://www.atk14.net/",array("meta" => array("color" => "red", "image" => "atk14_birdie.png")));
		$sites->add("Book","http://book.atk14.net/",array("meta" => array("image" => "book.png")));
		$sites->add("To be continued"); // no link -> disabled by default
		//
		$articles = $menu->add("Articles","articles");
		$articles->add("Recent",array("articles/recent"));
		$articles->add("2017","/en/articles/archive/?year=2017",array("active" => true));
		$articles->add("2018","/en/articles/archive/?year=2018",array(
			"disabled" => true // year 2018 is not yet ready
		));
		$articles->add("Archive",array("articles/archive"));

		$items = $menu->getItems();
		//
		$this->assertEquals(2,sizeof($items));
		//
		$this->assertEquals("Atk14 Sites",$items[0]->getTitle());
		$this->assertEquals("http://www.atk14.net/",$items[0]->getUrl()); // the URL of the first item
		$this->assertEquals(false,$items[0]->isActive());
		$this->assertEquals(false,$items[0]->isDisabled());
		//
		$this->assertEquals("Articles",$items[1]->getTitle());
		$this->assertEquals("/en/articles/",$items[1]->getUrl());
		$this->assertEquals(true,$items[1]->isActive()); // one of children is active
		$this->assertEquals(false,$items[1]->isDisabled());

		// Articles submenu
		$art_submenu = $items[1]->getSubmenu();
		$art_items = $art_submenu->getItems();
		$this->assertEquals(4,sizeof($art_items));
		$this->assertEquals("Recent",$art_items[0]->getTitle());
		$this->assertEquals("/en/articles/recent/",$art_items[0]->getUrl());
		$this->assertEquals(false,$art_items[0]->isActive());
		//
		$this->assertEquals("2017",$art_items[1]->getTitle());
		$this->assertEquals("/en/articles/archive/?year=2017",$art_items[1]->getUrl());
		$this->assertEquals(true,$art_items[1]->isActive());
		$this->assertEquals(false,$art_items[1]->isDisabled());
		//
		$this->assertEquals("2018",$art_items[2]->getTitle());
		$this->assertEquals("/en/articles/archive/?year=2018",$art_items[2]->getUrl());
		$this->assertEquals(false,$art_items[2]->isActive());
		$this->assertEquals(true,$art_items[2]->isDisabled());

		// Atk14 Sites submenu
		$submenu = $items[0]->getSubmenu();
		$items = $submenu->getItems();
		$this->assertEquals(3,sizeof($items));
		//
		$this->assertEquals("Atk14",$items[0]->getTitle());
		$this->assertEquals("http://www.atk14.net/",$items[0]->getUrl());
		$this->assertEquals(false,$items[0]->isDisabled());
		$this->assertEquals("red",$items[0]->getMeta("color"));
		$this->assertEquals("atk14_birdie.png",$items[0]->getMeta("image"));
		$this->assertEquals(array("color" => "red", "image" => "atk14_birdie.png"),$items[0]->getMeta());
		//
		$this->assertEquals("Book",$items[1]->getTitle());
		$this->assertEquals("http://book.atk14.net/",$items[1]->getUrl());
		$this->assertEquals(false,$items[1]->isDisabled());
		$this->assertEquals(false,$items[1]->isDisabled());
		$this->assertEquals(null,$items[1]->getMeta("color"));
		$this->assertEquals("book.png",$items[1]->getMeta("image"));
		$this->assertEquals(array("image" => "book.png"),$items[1]->getMeta());
		//
		$this->assertEquals("To be continued",$items[2]->getTitle());
		$this->assertEquals(null,$items[2]->getUrl());
		$this->assertEquals(true,$items[2]->isDisabled());
		$this->assertEquals(null,$items[2]->getMeta("color"));
		$this->assertEquals(null,$items[2]->getMeta("image"));
		$this->assertEquals(array(),$items[2]->getMeta());
		// ... playing with setDisabled()
		$items[2]->setDisabled();
		$this->assertEquals(true,$items[2]->isDisabled());
		//
		$items[2]->setDisabled(false);
		$this->assertEquals(false,$items[2]->isDisabled());

		$submenu = $items[0]->getSubmenu();
		$items = $submenu->getItems();
		$this->assertEquals(0,sizeof($items));
	}

	function test_addItem(){
		$menu = new Menu14();

		$item_1 = $menu->addItem("Item 1","main/index");
		$this->assertTrue(is_a($item_1,"Menu14Item"));

		$item_2 = $menu->addItem("Item 2","main/contact");
		$this->assertTrue(is_a($item_2,"Menu14Item"));
		$item_2->setDisabled();

		$items = $menu->getItems();
		$this->assertEquals(2,sizeof($items));
		$this->assertEquals(false,$items[0]->isDisabled());
		$this->assertEquals(true,$items[1]->isDisabled());
	}

	function test_inserting_replacing(){
		$menu = new Menu14();
		$menu->add("Item 1","main/index");
		$menu->add("Item 2","main/index");
		$menu->add("Item 1,5","main/index",array("index" => 1));
		$menu->add("Item 1 (replaced)","main/index",array("index" => 0, "overwrite" => true));

		$items = $menu->getItems();
		$this->assertEquals(3,sizeof($items));
		$this->assertEquals("Item 1 (replaced)",$items[0]->getTitle());
		$this->assertEquals("Item 1,5",$items[1]->getTitle());
		$this->assertEquals("Item 2",$items[2]->getTitle());
	}

	function test_breadcrumbs(){
		// also testing ArrayAccess, Countable and Iterator

		$breadcrumbs = new Menu14();

		$this->assertEquals(0,sizeof($breadcrumbs));

		$breadcrumbs[] = array("Home", "main/index");
		$breadcrumbs->add("Articles", "articles/index");
		$breadcrumbs[] = "Best article in the universe";

		$this->assertEquals(3,sizeof($breadcrumbs));

		$this->assertEquals("Home",$breadcrumbs[0]->getTitle());
		$this->assertEquals("/",$breadcrumbs[0]->getUrl());

		$this->assertEquals("Articles",$breadcrumbs[1]->getTitle());
		$this->assertEquals("/en/articles/",$breadcrumbs[1]->getUrl());

		$this->assertEquals("Best article in the universe",$breadcrumbs[2]->getTitle());
		$this->assertEquals(null,$breadcrumbs[2]->getUrl());

		$ary = array();
		foreach($breadcrumbs as $item){
			$ary[] = $item->getTitle();
		}
		$this->assertEquals("Home / Articles / Best article in the universe",join(" / ",$ary));
	}

	function test_metadata(){
		$menu = new Menu14();

		$this->assertEquals(array(),$menu->getMeta());
		$this->assertEquals(null,$menu->getMeta("color"));
		$this->assertEquals(null,$menu->getMeta("image"));

		$menu->setMeta("color","green");
		$menu->setMeta("border","2px");

		$this->assertEquals(array("color" => "green", "border" => "2px"),$menu->getMeta());
		$this->assertEquals("green",$menu->getMeta("color"));
		$this->assertEquals(null,$menu->getMeta("image"));

		$item = $menu->addItem("First","main/first");

		$this->assertEquals(array(),$item->getMeta());
		$this->assertEquals(null,$item->getMeta("color"));
		$this->assertEquals(null,$item->getMeta("image"));

		$item->setMeta("color","yellow");
		$item->setMeta("border","1px");

		$this->assertEquals(array("color" => "yellow", "border" => "1px"),$item->getMeta());
		$this->assertEquals("yellow",$item->getMeta("color"));
		$this->assertEquals(null,$item->getMeta("image"));
	}
}
