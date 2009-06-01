<?php

// Some functions will go here, this is intended to clean up the mucky code that can be done if you aren't careful.

// Check whether this is for Drupal.
function skinIsDrupal() {
	return function_exists( "drupal_page_footer" );
}

// Check whether this is for MediaWiki.
function skinIsMediaWiki() {
	if( defined( 'MEDIAWIKI' ) ) {
		return true;
	} else {
		return false;
	}
}

// MediaWiki uses XHTML 1.0 Transitional, whereas Drupal uses strict. This could cause trouble with things like the parser if not chosen correctly.
if( skinIsDrupal() ) {
	$doctype = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">' . "\r\n";
} elseif( skinIsMediaWiki() ) {
	$doctype = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">' . "\r\n";
}

// For the html tag, stuff like lang annd xml namespaces.
if( skinIsDrupal() ) {
	$htmltag = '<html xmlns="http://www.w3.org/1999/xhtml" lang="' . $language . '" xml:lang="' . $language . '">' . "\r\n";
} elseif( skinIsMediaWiki() ) {
	$htmltag = '<html xmlns="' . $this->data[ 'xhtmldefaultnamespace' ] . '" xml:lang="' . $this->data[ 'lang' ] . '" lang="' . $this->data[ 'lang' ] . '" dir="' . $this->data[ 'dir' ] . '">' . "\r\n";
}

// Page title.
if( skinIsMediaWiki() ) {
	$head_title = $this->data[ 'pagetitle' ];
}

// Header tags.
if( skinIsDrupal() ) {
	/*$head = $head .  $scripts . $styles;*/
} elseif( skinIsMediaWiki() ) {
	$head = Skin::makeGlobalVariablesScript( $this->data );
	$head .= "\t\t" . '<script type="' . $this->data[ 'jsmimetype' ] . '" src="' . $this->data[ 'stylepath' ] . '/commmon/wikibits.js?' . $GLOBALS['wgStyleVersion' ] . '"><!-- wikibits js --></script>' . "\r\n";
	if( $this->data[ 'pagecss' ] ) {
		$head .= "\t\t" . '<style type="text/css">' . $this->data[ 'pagecss' ] . '</style>' . "\r\n";
	}
	if( $this->data[ 'usercss' ] ) {
		$head .= "\t\t" . '<style type="text/css">' . $this->data[ 'usercss' ] . '</style>' . "\r\n";
	}
	if( $this->data[ 'jsvarurl' ] ) {
		$head .= "\t\t" . '<script type="' . $this->data[ 'jsmimetype' ] . '" src="' . $this->data[ 'jsvarurl' ] . '"><!-- site js --></script>' . "\r\n";
	}
	if( $this->data[ 'userjs' ] ) {
		$head .= "\t\t" . '<script type="' . $this->data[ 'jsmimetype' ] . '" src="' . $this->data[ 'userjs' ] . '"><!-- site js --></script>' . "\r\n";
	}
	if( $this->data[ 'userjsprev' ] ) {
		$head .= "\t\t" . '<script type="' . $this->data[ 'jsmimetype' ] . '" src="' . $this->data[ 'userjsprev' ] . '"><!-- site js --></script>' . "\r\n";
	}
	if( $this->data[ 'trackbackhtml' ] ) {
		$head .= "\t\t" . $this->data[ 'trackbackhtml' ] . "\r\n";
	}
	$head .= $this->data[ 'headscripts' ];
	$head .= "\t\t" . '<link rel="shortcut icon" href="/d/misc/favicon.ico" type="image/x-icon" />' . "\r\n";
}

// content.
if( skinIsMediaWiki() ) {
	$content = $this->data[ 'bodytext' ];
	if($this->data['catlinks']) {
		$content .= "<div id=\"catlinks\">";
		$content .= $this->data[ 'catlinks' ];
		$content .= "</div>";
	}
}

// Search box.
if( skinIsDrupal() ) {
	$search = '<form action="/d/index.php?q=search/node" method="post">
						<div class="search-form2">
							<input class="search-term" type="text" maxlength="200" size="16" accesskey="f" title="Enter the terms you wish to search for. [f]" name="search_theme_form_keys"  />
							<input class="search-submit" type="submit" value="Search" title="Search the pages for this text." name="op" />
						</div>
					</form>';
} elseif( skinIsMediaWiki() ) {
	$search = '<form action="/wiki/Special:Search" method="post">
						<div class="search-form2">
							<input class="search-term" type="text" maxlength="200" size="16" accesskey="f" title="Enter the terms you wish to search for. [f]" name="search" />
							<input class="search-submit" type="submit" value="Go" title="Go to a page with this exact name if exists." name="go" />&nbsp;
							<input class="search-submit" type="submit" value="Search" title="Search the pages for this text." name="fulltext" />
						</div>
					</form>';
}

// Title
if( skinIsMediaWiki() ) {
	$title = $this->data[ 'title' ];
}

// Sidebar right
if( skinIsMediaWiki() ) {
	function makeBlock( $title, $content, $first ) {
		if( $first ) {
			$id = " id='block-user-0'";
		} else {
			$id = "";
		}
		return '
		<div class="block"' . $id .'>
				<div class="rb1"></div><div class="rb2"></div><div class="rb3"></div><div class="rb4"></div><div class="rb5"></div><div class="rb6"></div><div class="rb7"></div>
				<div class="rboxcontent">
					<h2 class="title">' . $title .'</h2>
					' . $content .'
				</div>
				<div class="rb7"></div><div class="rb6"></div><div class="rb5"></div><div class="rb4"></div><div class="rb3"></div><div class="rb2"></div><div class="rb1"></div>
			</div>';
		}
	$temp = "";
	foreach( $this->data[ 'personal_urls' ] as $key => $item ) {
		$temp .= "<li id=\"pt-";
		$temp .= Sanitizer::escapeId( $key );
		$temp .= "\"";
		if( $item[ 'active' ] ) $temp .= "class=\"active\"";
		$temp .= "><a href=\"";
		$temp .= htmlspecialchars( $item[ 'href' ] );
		$temp .= "\" ";
		$skin->tooltipAndAccesskey( 'pt-' . $key );
		if( !empty( $item[ 'class' ] ) ) {
			$temp .= "class=\"";
			$temp .= htmlspecialchars( $item[ 'class' ] );
		} 
		$temp .= ">";
		$temp .= htmlspecialchars( $item['text'] );
		$temp .= "</a></li>\r\n";
	}
	$sidebar_right = makeBlock( "Personal Tools", "<ul>\r\n$temp</ul>", true );
	$temp = "";
	if( $this->data[ 'notspecialpage' ] ) {
		$temp .= "<li id=\"t-whatlinkshere\"><a href=\"";
		$temp .= htmlspecialchars( $this->data[ 'nav_urls' ][ 'whatlinkshere' ][ 'href' ] );
		$temp .= "\"";
		$temp .= $skin->tooltipAndAccesskey( 't-whatlinkshere' );
		$temp .= ">";
		$temp .= htmlspecialchars( $this->translator->translate( 'whatlinkshere' ) );
		$temp .= "</a></li>\r\n";
	}
	if( $this->data[ 'nav_urls' ][ 'recentchangeslinked' ] ) {
		$temp .= "<li id=\"t-recentchangeslinked\"><a href=\"";
		$temp .=  htmlspecialchars( $this->data[ 'nav_urls' ][ 'recentchangeslinked' ][ 'href' ] );
		$temp .= "\"";
		$temp .=  $skin->tooltipAndAccesskey( 't-recentchangeslinked' );
		$temp .= ">";
		$temp .= htmlspecialchars( $this->translator->translate( 'recentchangeslinked' ) );
		$temp .= "</a></li>\r\n";
	}
	if( isset( $this->data[ 'nav_urls' ][ 'trackbacklink' ] ) ) {
		$temp .= "<li id=\"t-trackbacklink\"><a href=\"";
		$temp .= htmlspecialchars( $this->data[ 'nav_urls' ][ 'trackbacklink' ][ 'href' ] );
		$temp .= "\"";
		$temp .= $skin->tooltipAndAccesskey( 't-trackbacklink' );
		$temp .= ">";
		$temp .= htmlspecialchars( $this->translator->translate( 'trackbacklinks' ) );
		$temp .= "</a></li>\r\n";
	}
	if($this->data['feeds']) {
		$temp .= "<li id=\"feedlinks\">";
		foreach($this->data['feeds'] as $key => $feed) {
			$temp .= "<span id=\"feed-";
			$temp .= Sanitizer::escapeId($key);
			$temp .= "\"><a href=\"";
			$temp .= htmlspecialchars($feed['href']);
			$temp .= "\"";
			$temp .= $skin->tooltipAndAccesskey('feed-'.$key);
			$temp .= ">";
			$temp .= htmlspecialchars($feed['text']);
			$temp .= "</a>&nbsp;</span>";
		}
		$temp .= "</li>\r\n";
	}
	foreach( array( 'contributions', 'log', 'blockip', 'emailuser', 'upload', 'specialpages' ) as $special ) {
		if( $this->data[ 'nav_urls' ][ $special ] ) {
			$temp .= "<li id=\"t-$special\"><a href=\"";
			$temp .= htmlspecialchars( $this->data[ 'nav_urls' ][ $special ][ 'href' ] );
			$temp .= "\"";
			$temp .= $skin->tooltipAndAccesskey( 't-' . $special );
			$temp .= ">";
			$temp .= htmlspecialchars( $this->translator->translate( $special ) );
			$temp .= "</a></li>\r\n";
		}
	}
	if( $this->data[ 'nav_urls' ] && 1) {
		global $wgContLang;
		$temp .= "<li id=\"t-recentchanges\"><a href=\"";
		$temp .= "/wiki/" . $wgContLang->specialPage( 'recentchanges' );
		$temp .= "\"";
		$temp .= ">";
		$temp .= htmlspecialchars( $this->translator->translate( 'recentchanges' ) );
		$temp .= "</a></li>\r\n";
	}
	if( !empty( $this->data[ 'nav_urls' ][ 'print' ][ 'href' ] ) ) {
		$temp .= "<li id=\"t-print\"><a href=\"";
		$temp .= htmlspecialchars( $this->data[ 'nav_urls' ][ 'print' ][ 'href' ] );
		$temp .= "\"";
		$temp .= $skin->tooltipAndAccesskey( 't-print' );
		$temp .= ">";
		$temp .= htmlspecialchars( $this->translator->translate( 'printableversion' ) );
		$temp .= "</a></li>\r\n";
	}
	if( !empty( $this->data[ 'nav_urls' ][ 'permalink' ][ 'href' ] ) ) {
		$temp .= "<li id=\"t-permalink\"><a href=\"";
		$temp .= htmlspecialchars( $this->data[ 'nav_urls' ][ 'permalink' ][ 'href' ] );
		$temp .= "\"";
		$temp .= $skin->tooltipAndAccesskey( 't-permalink' );
		$temp .= ">";
		$temp .= htmlspecialchars( $this->translator->translate( 'permalink' ) );
		$temp .= "</a></li>\r\n";
	} elseif( $this->data[ 'nav_urls' ][ 'permalink' ][ 'href' ] === '') {
		$temp .= "<li id=\"t-ispermalink\"";
		$temp .= $skin->tooltip( 't-ispermalink' );
		$temp .= ">";
		$temp .= htmlspecialchars( $this->translator->translate( 'permalink' ) );
		$temp .= "</li>\r\n";
	}
	$sidebar_right .= makeBlock( htmlspecialchars( $this->translator->translate( 'toolbox' ) ), "\r\n<ul>\r\n$temp</ul>\r\n" );
	$temp = "";
	if( $this->data[ 'language_urls' ] ) {
		$temp .= "<ul>";
		foreach( $this->data[ 'language_urls' ] as $langlink ) {
			$temp .= "<li class=\"";
			$temp .= htmlspecialchars( $langlink[ 'class' ] );
			$temp .= "\"";
			$temp .= ">";
			$temp .= "<a href=\"";
			$temp .= htmlspecialchars( $langlink[ 'href' ] );
			$temp .= "\"{$langlink['text']}</a></li>";
		}
		$temp .= "</ul>";
	}
	makeBlock( htmlspecialchars( $this->translator->translate( 'otherlanguages' ) ), $temp );
}

// tabs
if( skinIsMediaWiki() ) {
	$tabs = "<ul class=\"primary\">\r\n";
	foreach( $this->data[ 'content_actions' ] as $key => $tab ) {
		$tabs .= "<li id=\"ca-";
		$tabs .= Sanitizer::escapeId( $key );
		$tabs .= "\" ";
		if( $tab[ 'class' ] ) {
			$tabs .= "class=\"";
			$tabs .= htmlspecialchars( $tab[ 'class' ] );
			$tabs .= "\"";
		}
		$tabs .= "><a href=\"";
		$tabs .= htmlspecialchars( $tab[ 'href' ] );
		$tabs .= "\"";
		$tabs .= $skin->tooltipAndAccesskey( 'ca-' . $key );
		$tabs .= ">";
		$tabs .= htmlspecialchars( $tab[ 'text' ] );
		$tabs .= "</a></li>\r\n";
	}
	$tabs .= "</ul>\r\n";
}

// footer message
if( skinIsMediaWiki() ) {
	$footer_message = "All trademarks referenced herein are the properties of their respective owners.  This site is not sponsored, endorsed, or run by the U.S. Government.";
}

// page class
if( skinIsMediaWiki() ) {
	$pageclass = "mediawiki " . $this->data['nsclass'] . " " . $this->data['dir'] . " " . $this->data['pageclass'];
}

// include quote generator
require_once( "quotes.php" );

?>
<?php echo $doctype; ?>
<?php echo $htmltag; ?>
	<head>
		<title><?php echo $head_title; ?></title>
		<link rel="stylesheet" href="/skin2/style.css" type="text/css" />
		<?php if (ereg("iPhone", $_SERVER['HTTP_USER_AGENT'])) { ?>
			/* turn off the search footer if they're coming from an iPhone */
			<link rel="stylesheet" href="/skin2/style-iphone.css" type="text/css" /><![endif]-->
		<?php } ?>
		<?php if (ereg("Macintosh", $_SERVER['HTTP_USER_AGENT'])) { ?>
			<link rel="stylesheet" href="/skin2/style-mac.css" type="text/css" /><![endif]-->
		<?php } ?>
		<!--[if IE]><link rel="stylesheet" href="/skin2/style-ie.css" type="text/css" /><![endif]-->
		<!--[if lte IE 6]><link rel="stylesheet" href="/skin2/style-ie-lte-6.css" type="text/css" /><![endif]-->
		<!--[if IE 7]><link rel="stylesheet" href="/skin2/style-ie-7.css" type="text/css" /><![endif]-->
	</head>
	<body class="<?php echo $pageclass; ?>"><div id="container">
		<div id="header">
			<a id="header-right"></a>
			<a id="header-left"></a>
			<a id="header-text" href="http://brlcad.org/"></a>
			<a id="header-xyz"></a>
			<div id="header-navigation">
				<div class="rboxcontent">
					<a class="navitem" href="/d/about">About</a>
					<a class="navitem" href="/d/download">Download</a>
					<a class="navitem" href="/wiki/Documentation">Documentation</a>
					<a class="navitem" href="http://sourceforge.net/tracker/?group_id=105292">Support</a>
					<a class="navitem" href="/wiki/Main_Page">Wiki</a>
					<a class="navitem" href="/gallery">Gallery</a>
<!--					<a class="navitem" href="/d/community">Community</a>
					<a class="navitem" href="/d/development">Development</a>
					<a class="navitem" href="/wiki/Support">Support</a>
-->
					<a class="navitem" href="/d/contact">&iquest;?</a>
				</div>
<!--				<b class="rb7"></b><b class="rb6"></b><b class="rb5"></b><b class="rb4"></b><b class="rb3"></b><b class="rb2"></b><b class="rb1"></b> -->
			</div>
			<a id="header-line"></a>
			<a id="header-download" href="/d/download"></a>
			<div id="header-search">
				<div class="sboxcontent">
					<?php echo $search . "\n"; ?>
				</div>
				<b class="sb6"></b><b class="sb5"></b><b class="sb4"></b><b class="sb3"></b><b class="sb2"></b><b class="sb1"></b>
			</div>
		</div>
		<div id="sidebar">
			<?php echo "\r\n$sidebar_right"; ?>
		</div>
		<div id="center">
			<?php echo $sidebar_left; ?>

			<h1 id="title" class="firstHeading"><?php echo $title; ?></h1>
			<div id="tabs"><?php print $tabs ?></div>
			<div id="breadcrumbs"><?php echo $breadcrumb; ?></div>
			<?php print $help ?>
			<?php print $messages ?>
			<?php echo $content; ?>
			<br />
			<br />
			<br />
			<br />
			<br />
		</div>
		<div id="footer">
			<div id="footer-line-top"></div>
			<div id="footer-search">
				<div class="sboxcontent">
					<?php echo $search . "\n"; ?>
				</div>
				<b class="bb6"></b><b class="bb5"></b><b class="bb4"></b><b class="bb3"></b><b class="bb2"></b><b class="bb1"></b>
			</div>
			<p id="footer-quotes"><?php echo htmlspecialchars( getQuote() ); ?></p>
			<p id="footer-message"><?php print $footer_message ?></p>
		</div>
	</div></body>
</html>