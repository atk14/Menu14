<?php
class TcMenu14 extends TcBase {

	function test(){
		$menu = new Menu14();
		//
		$sites = $menu->add("Atk14 Sites");
		$sites->add("Atk14","http://www.atk14.net/");
		$sites->add("Book","http://book.atk14.net/");
		$sites->add("To be continued"); // no link
		//
		$articles = $menu->add("Articles","articles");
		$articles->add("Recent",["articles/recent"]);
		$articles->add("2017","/en/articles/archive/?year=2017",["active" => true]);
		$articles->add("Archive",["articles/archive"]);

		$items = $menu->getItems();
		//
		$this->assertEquals(2,sizeof($items));
		//
		$this->assertEquals("Atk14 Sites",$items[0]->getTitle());
		$this->assertEquals("http://www.atk14.net/",$items[0]->getUrl()); // the URL of the first item
		$this->assertEquals(false,$items[0]->isActive());
		//
		$this->assertEquals("Articles",$items[1]->getTitle());
		$this->assertEquals("/en/articles/",$items[1]->getUrl());
		$this->assertEquals(true,$items[1]->isActive()); // one of children is active

		// Articles submenu
		$art_submenu = $items[1]->getSubmenu();
		$art_items = $art_submenu->getItems();
		$this->assertEquals(3,sizeof($art_items));
		$this->assertEquals("Recent",$art_items[0]->getTitle());
		$this->assertEquals("/en/articles/recent/",$art_items[0]->getUrl());
		$this->assertEquals(false,$art_items[0]->isActive());
		$this->assertEquals("2017",$art_items[1]->getTitle());
		$this->assertEquals("/en/articles/archive/?year=2017",$art_items[1]->getUrl());
		$this->assertEquals(true,$art_items[1]->isActive());

		// Atk14 Sites submenu
		$submenu = $items[0]->getSubmenu();
		$items = $submenu->getItems();
		$this->assertEquals(3,sizeof($items));
		$this->assertEquals("Atk14",$items[0]->getTitle());
		$this->assertEquals("http://www.atk14.net/",$items[0]->getUrl());
		$this->assertEquals("Book",$items[1]->getTitle());
		$this->assertEquals("http://book.atk14.net/",$items[1]->getUrl());
		$this->assertEquals("To be continued",$items[2]->getTitle());
		$this->assertEquals(null,$items[2]->getUrl());

		$submenu = $items[0]->getSubmenu();
		$items = $submenu->getItems();
		$this->assertEquals(0,sizeof($items));
	}

	function test_breadcrumbs(){
		// also testing ArrayAccess, Countable and Iterator

		$breadcrumbs = new Menu14();

		$this->assertEquals(0,sizeof($breadcrumbs));

		$breadcrumbs[] = ["Home", "main/index"];
		$breadcrumbs->add("Articles", "articles/index");
		$breadcrumbs[] = "Best article in the universe";

		$this->assertEquals(3,sizeof($breadcrumbs));

		$this->assertEquals("Home",$breadcrumbs[0]->getTitle());
		$this->assertEquals("/",$breadcrumbs[0]->getUrl());

		$this->assertEquals("Articles",$breadcrumbs[1]->getTitle());
		$this->assertEquals("/en/articles/",$breadcrumbs[1]->getUrl());

		$this->assertEquals("Best article in the universe",$breadcrumbs[2]->getTitle());
		$this->assertEquals(null,$breadcrumbs[2]->getUrl());

		$ary = [];
		foreach($breadcrumbs as $item){
			$ary[] = $item->getTitle();
		}
		$this->assertEquals("Home / Articles / Best article in the universe",join(" / ",$ary));
	}
}
