<?php

/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

/**
 * Created: Sep 12, 2011
 */
include_once('include/Dashlets/DashletRssFeedTitle.php');

class Bug46217Test extends Sugar_PHPUnit_Framework_TestCase {

	public $rssFeedClass;
	
	public function setUp() {
		$this->rssFeedClass = new DashletRssFeedTitle("");
	}
	
	public function tearDown() {
		unset($this->rssFeedClass);
	}
	
	public function dataProviderCorrectParse() {
		return array(
			array('<?xml version="1.0" encoding="UTF-8"?>
				<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom"><channel>
				<title>France Info</title>
				<link>http://www.france-info.com</link>
				<description>France Info - A la Une</description>
				<image>
				<url>http://www.france-info.com/IMG/siteon0.gif</url>
				<title>France Info</title>
				<link>http://www.france-info.com</link>
				</image>', 
				
				'France Info'
			),
			array('<?xml version="1.0" encoding="UTF-8" ?>
				<rss version="2.0">
				<channel>
				<title><![CDATA[RSS Title]]></title>
				<description>This is an example of an RSS feed</description>
				<link>http://www.someexamplerssdomain.com/main.html</link>
				<lastBuildDate>Mon, 06 Sep 2010 00:01:00 +0000 </lastBuildDate>
				<pubDate>Mon, 06 Sep 2009 16:45:00 +0000 </pubDate>',
				
				'RSS Title'
			),
		);
	}
	
	/**
	 * @dataProvider dataProviderCorrectParse
	 */
	public function testCorrectTitleParse($rssFeed, $expectedTitle) {
		$this->rssFeedClass->contents = $rssFeed;
		$this->rssFeedClass->getTitle();
		$this->assertEquals($expectedTitle, $this->rssFeedClass->title);
		$this->rssFeedClass->convertEncoding();
		$this->assertEquals($expectedTitle, $this->rssFeedClass->title);
	}
}