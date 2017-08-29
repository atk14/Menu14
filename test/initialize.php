<?php
require(__DIR__ . "/../src/menu14.php");
require(__DIR__ . "/../vendor/autoload.php");

interface IAtk14Global {
	function getValue($key);
	function getLang();
}

$ATK14_GLOBAL = Mockery::mock("Atk14Global");
$ATK14_GLOBAL->shouldReceive('getLang')->andReturn("en");
$ATK14_GLOBAL->shouldReceive('getValue')->with("controller")->andReturn("main");
$ATK14_GLOBAL->shouldReceive('getValue')->with("action")->andReturn("index");
$ATK14_GLOBAL->shouldReceive('getValue')->with("namespace")->andReturn("");

$atk14_url = Mockery::mock("alias:Atk14Url");
$atk14_url->shouldReceive("BuildLink")->with(["namespace" => "", "controller" => "main", "action" => "index", "lang" => "en"],[])->andReturn("/");
$atk14_url->shouldReceive("BuildLink")->with(["namespace" => "", "controller" => "articles", "action" => "index", "lang" => "en"],[])->andReturn("/en/articles/");
$atk14_url->shouldReceive("BuildLink")->with(["namespace" => "", "controller" => "articles", "action" => "recent", "lang" => "en"],[])->andReturn("/en/articles/recent/");
