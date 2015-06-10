<?php
class TcLinkFinder extends TcBase{
	function testBasicUsage(){
		$src = 'Lorem www.ipsum.com. dolor@sit.net. Thank you';
		$lf = new LinkFinder();
		$this->assertEquals('Lorem <a href="http://www.ipsum.com">www.ipsum.com</a>. <a href="mailto:dolor@sit.net">dolor@sit.net</a>. Thank you',$lf->process($src));

		// auto escaping of HTML entities
		$src = 'Lorem www.ipsum.com <http://www.ipsum.com/>.
			Dolor: dolor@sit.new <dolor@sit.net>. Thank you';
		$this->assertEquals('Lorem <a href="http://www.ipsum.com">www.ipsum.com</a> &lt;<a href="http://www.ipsum.com/">http://www.ipsum.com/</a>&gt;.
			Dolor: <a href="mailto:dolor@sit.new">dolor@sit.new</a> &lt;<a href="mailto:dolor@sit.net">dolor@sit.net</a>&gt;. Thank you',$lf->process($src));

		// disabling auto escaping may produce invalid markup
		$src = 'Lorem www.ipsum.com <http://www.ipsum.com/>.
			Dolor: dolor@sit.new <dolor@sit.net>. Thank you';
		$this->assertEquals('Lorem <a href="http://www.ipsum.com">www.ipsum.com</a> <<a href="http://www.ipsum.com/">http://www.ipsum.com/</a>>.
			Dolor: <a href="mailto:dolor@sit.new">dolor@sit.new</a> <<a href="mailto:dolor@sit.net">dolor@sit.net</a>>. Thank you',$lf->process($src,array("escape_html_entities" => false)));

		// an example from the README.md
		$src = 'Find more at www.ourstore.com <http://www.ourstore.com/>';
		$this->assertEquals('Find more at <a href="http://www.ourstore.com">www.ourstore.com</a> &lt;<a href="http://www.ourstore.com/">http://www.ourstore.com/</a>&gt;',$lf->process($src));
	}

	function testLinks(){
		$links = array(
			"www.ipsum.com" => "http://www.ipsum.com",
			"http://www.ipsum.com/" => "http://www.ipsum.com/",
			"https://www.example.com/article.pl?id=123" => "https://www.example.com/article.pl?id=123",
			"www.example.com/article.pl?id=123" => "http://www.example.com/article.pl?id=123",
			"www.example.com/article.pl?id=123&format=raw" => "http://www.example.com/article.pl?id=123&format=raw",
			"www.example.com/article.pl?id=123;format=raw" => "http://www.example.com/article.pl?id=123;format=raw",
			"www.www.example.intl" => "http://www.www.example.intl",

			"ftp://example.com/public/" => "ftp://example.com/public/",

			//"http://grooveshark.com/#!/album/AirMech/8457898" => "http://grooveshark.com/#!/album/AirMech/8457898", // TODO:
		);

		$templates = array(
			"%s",
			"Lorem %s Ipsum",
			"Lorem %s, Ipsum",
			"Lorem %s. Ipsum",
			"Lorem %s",
			"%s, Lorem",
			"%s,Lorem",
			"%s. Lorem",
			"Lorem: %s",
			"Lorem:%s",
			"Lorem %s!",
			"Lorem <%s>",
		);

		$lf = new LinkFinder();
		foreach($links as $src => $expected){
			$expected = str_replace('&','&amp;',$expected); // "www.example.com/article.pl?id=123&format=raw" => "www.example.com/article.pl?id=123&amp;format=raw"
			foreach($templates as $template){
				$out = $lf->process($_src = sprintf($template,$src));
				$this->assertEquals(true,!!preg_match('/<a href="([^"]+)">/',$out,$matches),"$_src is containing a link");
				$this->assertEquals($expected,$matches[1],"$_src is containing $expected");
			}
		}
	}
}
