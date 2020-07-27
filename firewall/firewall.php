<?php declare( strict_types = 1 );
if ( !defined( 'PATH' ) ) { die(); }
/**
 *  Bare Firewall: This is the plugin version of the standalone Firewall with 
 *  a subset of the features to ensure compatibility with Bare
 *  
 *  @link https://github.com/cypnk/Firewall
 *  
 *  This plugin should be added first to ensure it runs before all others
 */


// Location of your database file (based on relative path)
// This will be where Firewall creates a SQLite database
define( 'FIREWALL_DATA',		CACHE . 'firewall.db' );



/**********************************************************************
 *                      Caution editing below
 **********************************************************************/




define( 'FIREWALL_SQL', <<<SQL
CREATE TABLE firewall (
	id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, 
	ip TEXT NOT NULL, 
	ua TEXT NOT NULL, 
	uri TEXT NOT NULL, 
	method TEXT NOT NULL, 
	headers TEXT NOT NULL, 
	expires DATETIME DEFAULT NULL,
	created DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);-- --
CREATE INDEX idx_firewall_on_ip ON firewall ( ip ASC );-- --
CREATE INDEX idx_firewall_on_ua ON firewall ( ua ASC );-- --
CREATE INDEX idx_firewall_on_uri ON firewall ( uri ASC );-- --
CREATE INDEX idx_firewall_on_method ON firewall ( method ASC );-- --
CREATE INDEX idx_firewall_on_expires ON firewall ( expires DESC );-- --
CREATE INDEX idx_firewall_on_created ON firewall ( created ASC );-- --
CREATE TRIGGER firewall_insert AFTER INSERT ON firewall FOR EACH ROW 
BEGIN
	UPDATE firewall SET 
		expires = datetime( 
			( strftime( '%s','now' ) + 604800 ), 
			'unixepoch'
		) WHERE rowid = NEW.rowid;
	
	DELETE FROM firewall WHERE 
		strftime( '%s', expires ) < 
		strftime( '%s', created );
END;
SQL
);

define( 'FIREWALL_DB_INSERT', <<<SQL
INSERT INTO firewall ( ip, ua, uri, method, headers ) 
	VALUES ( :ip, :ua, :uri, :method, :headers );
SQL
);


// End response immediately
function fw_instaKill() {
	// Log error as a firewall entry
	visitorError( 403, 'Firewall' );
	sendError( 403, errorLang( "denied", \MSG_DENIED ) );
}


// IP v4 address in given subnet
function fw_inIPv4Range( $ip, $subnet ) {
	// Set default subnet to 32
	if ( !textHas( $subnet, '/' ) ) {
		$subnet .= '/32';
	}
	
	list( $range, $mask ) = \explode( '/', $subnet, 2 );
	$ndec = -1 << ( 32 - $mask );
	
	return 
	( \ip2long( $ip ) & $ndec ) === ( \ip2long( $range ) & $ndec );
}

// Convert IPv6 mask to byte array for easier matching
function fw_IPv6Array( $net ) {
	$ip	= \str_repeat( 'f', $net / 4 );
	switch( $net % 4 ) {
		case 1:
			$ip .= '8';
			break;
		case 2:
			$ip .= 'c';
			break;
			
		case 3:
			$ip .= 'e';
			break;
	}
	// Fill out mask
	return \pack( 'H*', \str_pad( $ip, 32, '0' ) );
}

// IP v6 address in given subnet
function fw_inIPv6Range( $ip, $subnet ) {
	// Set default subnet to 64
	if ( !textHas( $subnet, '/' ) ) {
		$subnet .= '/64';
	}
	
	list( $range, $mask ) = \explode( '/', $subnet, 2 );
	$sbit	= \inet_pton( $ip );
	
	return 
	( $sbit & fw_IPv6Array( $mask ) ) == \inet_pton( $range );
}

// IP Address in given range collection
function fw_inSubnet( $ip, $subnet ) {
	
	// If this is an IPv6 address
	if ( \filter_var( 
		$ip, \FILTER_VALIDATE_IP, \FILTER_FLAG_IPV6 
	) ) {
		foreach ( $subnet as $net ) {
			// Skip searching IPv4 addresses
			if ( !textHas( $net, ':' ) ) {
				continue;
			}
			if ( fw_inIPv6Range( $ip, $net ) ) {
				return true;
			}
		}
		
	// This is an IPv4 address
	} else {
		foreach ( $subnet as $net ) {
			// Skip searching IPv6 addresses
			if ( textHas( $net, ':' ) ) {
				continue;
			}
			
			if ( fw_inIPv4Range( $ip, $net ) ) {
				return true;
			}
		}
	}
	
	return false;
}


function fw_uaCheck() {
	$ua	= getUA();
	
	// User Agent contains non-ASCII characters?
	if ( !isASCII( $ua ) ) {
		return true;
	}
	
	// Starting flags
	static $ua_start = [
		' ',
		'\"',
		'-',
		';',
		'%',
		'$',
		'<?',
		'(',
		'Android', 
		'iTunes',
		'U;',
		'Korvo',
		'MSIE',
		'user'
	];
	
	if ( textStartsWith( $ua, $ua_start ) ) {
		return true;
	}
	
	// Suspicious user agent fragments
	static $ua_frags = [
		// Injection in the UA
		'<?php',
		'IDATH.c\<?',
		'<?=`$_',
		'IDATH.c??<script',
		'IDATHKc??<script',
		
		// There's no space in front of ;
		' ; MSIE',
		
		// Invalid tokens
		'~~',
		'**',
		'...',
		'\\\\',
		
		// Doesn't exist
		'.NET CLR 1)',
		'.NET CLR1',
		'.NET_CLR',
		'.NET-CLR',
		
		'\r',
		'<sc',
		'(Chrome)',
		'Widows',
		'360Spider',
		'8484 Boston Project',
		
		// There shouldn't be HTML in user agent strings
		'a href=',
		
		'Aboundex',
		'Acunetix',
		'adwords',
		'Alexibot',
		'AIBOT',
		
		// Misspelled "Android"
		'Andriod',
		'Andreod',
		'Andirod',
		'Angroid',
		
		// Forged Android
		'Android --',
		'Android 2.x',
		
		'AntivirXP08',
		'AOLBuild /',
		
		// Misspelled "Apple"
		'Appel',
		'Appl ',
		'Aplle',
		
		'asterias',
		'Atomic',
		'attach',
		'atSpider',
		'autoemail',
		'AWI ',
		'BackDoor',
		'BackWeb',
		'BadBehavior',
		'Bad Behavior',
		
		// Fake Baidu
		'baidu /',
		'baidu/ ',
		'bai du/',
		'baiduspider/ ',
		'baiduspider /',
		'baidu spider',
		'baiduspider/1.',
		
		'Bandit',
		'BatchFTP',
		'Bigfoot',
		
		// Fake Bingbot
		'bingbot /',
		'bingbot/ ',
		'bing bot/',
		
		'Black.Hole',
		'BlackHole',
		'BlackWidow',
		'blogsearchbot-martin',
		'BlowFish',
		'Bot mailto:craftbot@yahoo.com',
		'BotALot',
		'BrowserEmulator',
		'Buddy',
		'BUILDDATE',
		'BuiltBotTough',
		'Bullseye',
		'BunnySlippers',
		'bwh3',
		'CAIME0',
		'CAIMEO',
		'Cegbfeieh',
		'centurybot',
		'changedetection',
		'CheeseBot',
		'CherryPicker',
		'China Local Browse',
		'ChinaClaw',
		'Clarity',
		'Clearswift',
		'clipping',
		'Cogentbot',
		'Collector',
		'ContactBot',
		'ContactSmartz',
		'ContentSmartz',
		
		// Fake compatibility
		'compatible ;',
		'compatible-',
		
		'Cool ',
		'cognitiveseo',
		'CoralWebPrx',
		'core-project',
		'Copier',
		'CopyRightCheck',
		'cosmos',
		'Crescent',
		'Cryptoapi',
		'Custo',
		'DataCha0s',
		'DBrowse',
		'Demo Bot',
		'Diamond',
		'Digger',
		'DIIbot',
		'DISCo',
		'DittoSpyder',
		'discovery',
		'DnyzBot',
		'Download',
		'dragonfly',
		'Drip',
		'DSurf',
		'DTS Agent',
		'eCatch',
		'Easy',
		'EBrowse',
		'ecollector',
		'Educate Search',
		'Email',
		'Emulator',
		'Enchanc',
		'EroCrawler',
		'Exabot',
		'Express WebPictures',
		
		// Extractors
		'Extrac',
		
		'evc-batch',
		'EyeNetIE',
		
		// Fake Facebook bot
		'Facebot Twitterbot',
		'facebookexternal/',
		'facebookexternal /',
		'facebookexternalhit/ ',
		'facebookexternalhit /',
		
		'Fail',
		'Fatal',
		'FlashGet',
		'FHscan',
		
		// Too old to be viable
		'Firebird',
		'Firefox/40',
		
		'flunky',
		'Franklin Locator',
		'Foobot',
		'Forum Poster',
		'FrontPage',
		'FSurf',
		'Full Web Bot',
		'FunWeb',
		'Gecko/2525',
		'Generic',
		'GetRight',
		'GetWeb!',
		'Ghost',
		'Gluten Free Crawler',
		'Go!Zilla',
		'Go-Ahead-Got-It',
		'Go-http-client',
		'gotit',
		
		// Fake Googlebot
		'googlebot /',
		'googlebot/ ',
		'googlebot/1.',
		'Googlebot Image',
		'Googlebot-Image/ ',
		'Googlebot-Image /',
		'Googlebot Video',
		'Googlebot-Video/ ',
		'Googlebot-Video /',
		'Mediapartners Google',
		
		'Gowikibot',
		'Grab',
		'Grafula',
		'GrapeshotCrawler',
		'grub',
		'hanzoweb',
		'Harvest',
		'Havij',
		'hloader',
		'HMView',
		'HttpProxy',
		
		// Resource-hungry archiver (comment to make exception)
		'HTTrack',
		
		'human',
		'hverify',
		
		// Amazon Alexa
		'ia_archiver',
		
		'IlseBot',
		'IndeedBot',
		'Indy Library',
		'InfoNaviRobot',
		'InfoPath',
		'InfoTekies',
		'informant',
		'Insuran',
		'Intelliseek',
		'InterGET',
		
		// *Not* IE. UA is likely a bot
		'Internet Explorer',
		
		// Misspelled "Intel"
		'Intle',
		'Itele',
		'Intle',
		
		'Intraformant',
		
		// Fake iPhone
		'iPhone/',
		'iPhone /',
		'iPhoneOS',
		'iPhone OS/',
		
		'ISC Systems iRc',
		'Iria',
		'Java 1.',
		'Java/1',
		'Jakarta',
		'Jenny',
		'JetCar',
		'JOC',
		'JustView',
		'Jyxobot',
		'Kenjin',
		'Keyword',
		'larbin',
		'Leacher',
		'LexiBot',
		'LeechFTP',
		'libwhisker',
		'libwww-perl',
		'lftp',
		'libWeb/clsHTTP',
		'likse',
		'LinkScan',
		'Lightning',
		'linkdexbot',
		'LNSpiderguy',
		'LinkWalker',
		'Lobster',
		'Locator',
		'LWP',
		
		// Misspelled "Macintosh"
		'Macnitosh',
		'Macinotsh',
		'Mackintosh',
		'Macintohs',
		'Mcintosh',
		
		'Magnet',
		'Mag-Net',
		'MarkWatch',
		'Mata.Hari',
		
		// Automated tool (can be abused)
		'Mechanize',
		
		'Memo',
		'Meterpreter/Windows',
		'Microsoft URL',
		'Microsoft.URL',
		'MIDown',
		'Ming Mong',
		'Missigua',
		'Mister',
		'MJ12bot/v1.0.8',
		'moget',
		'Mole',
		'Morfeus',
		
		// Not the blog engine
		'Movable Type',
		
		// Fake Mozilla
		'Mozilla.*NEWT',
		'Mozilla/0',
		'Mozilla/1',
		'Mozilla/2',
		'Mozilla/3',
		'Mozilla/4.0(',
		'Mozilla/4.0+(compatible;+',
		'Mozilla/4.0 (Hydra)',
		'Mozilla /',
		'Mozilla/9.0',
		
		// Fake MSNBot
		'msnbot /',
		'MS Search 6.0 Robot',
		
		'MSIE 7.0 ; Windows NT',
		'MSIE 7.0; Windows NT 5.2',
		'MSIE 7.0;  Windows NT 5.2',
		
		'Murzillo',
		'MVAClient',
		'MyApp',
		'MyFamily',
		'Navroad',
		'NearSite',
		'NetAnts',
		'NetMechanic',
		'Netsparker',
		'NetSpider',
		'Net Vampire',
		'NetZIP',
		'Nessus',
		'NG',
		'NICErsPRO',
		'Nikto',
		'Ninja',
		'Nimble',
		'Nmap',
		'NPbot',
		'Nomad',
		'Nutch',
		'Nutscrape',
		'NextGen',
		'Octopus',
		'OmniExplorer',
		'Opera/9.64(',
		
		// Offline anything is a scraper
		'Offline',
		
		'Openfind',
		'OutfoxBot',
		'panscient',
		'Papa Foto',
		'PaperLiBot',
		'Parser',
		'pavuk',
		'pcBrowser',
		'PECL::',
		'PeoplePal',
		'Perman Surfer',
		'PHP',
		'Pockey',
		'PMAFind',
		'POE-Component',
		'PowerMapper',
		'ProPowerBot',
		'proximic',
		'psbot',
		'psycheclone',
		'Pump',
		'PussyCat',
		'PycURL',
		'Python-urllib',
		'qiqi',
		'QueryN',
		'raventools',
		'RealDownload',
		'Reaper',
		'Recorder',
		'ReGet',
		'RepoMonkey',
		'Research',
		'RMA',
		'revolt',
		'RukiCrawler',
		
		// Revisions are always numbers, not x
		'rv:x.',
		
		// Misconfigured bot
		'rv:geckoversion',
		
		// Scraper
		'Scrapy',
		
		'Shockwave Flash',
		'SemrushBot',
		'sentiment',
		'SeoBotM6',
		'seocharger',
		'SEOkicks-Robot',
		'Siphon',
		'SiteSnagger',
		'SlySearch',
		'SmartDownload',
		'SMTBot',
		'Snake',
		'Snapbot',
		'sogou',
		'SpaceBison',
		'Spank',
		'spanner',
		'sqlmap',
		'Sqworm',
		'Stress',
		'Stripper',
		'Strateg',
		'strange',
		'study',
		'Sucker',
		'SuperBot',
		'SuperCleaner',
		'Super Happy Fun',
		'SuperHTTP',
		'Surfbot',
		'suzuran',
		'Synapse',
		'Szukacz',
		'taboola',
		'tAkeOut',
		'Test',
		'TightTwatBot',
		'Titan',
		'Teleport',
		'Telesoft',
		'TO-Browser/TOB',
		
		// No space before ;
		'Touch ;',
		
		'TrackBack',
		'trandoshan',
		'Trellian',
		
		// Misspelled "Trident"
		'Tridet',
		'Tridnet',
		'Tridnet /',
		'Trident /',
		
		'True_Robot',
		'Turing Machine',
		'turingos',
		'TurnitinBot',
		'like TwitterBot',
		'Tweetmeme',
		'Ultraseek',
		'Unknown',
		'Ubuntu/9.25',
		'unspecified',
		'user',
		
		// Strange formatting of the two words
		'User Agent:',
		'User-Agent:',
		
		// Fake emulator
		'Version/ ',
		'Version /',
		
		'VoidEYE',
		'w3af',
		'Warning',
		'Web Image Collector',
		'WebaltBot',
		'WebAuto',
		'WebFetch',
		'WebGo',
		
		// Misspelled "WebKit" a la "AppleWebKit"
		' Web Kit',
		' Webkit',
		'Web Kit',
		'Webit',
		'WebiKit',
		'Webikt',
		'Webkit /',
		
		'WebmasterWorldForumBot',
		'WebSauger',
		'WebSite-X Suite',
		'Website eXtractor',
		'Website Quester',
		'Webster',
		'WebWhacker',
		'WebZIP',
		'WeSEE',
		'Whacker',
		'Widow',
		'Winnie Poh',
		
		// These are (very) old. Likely bots
		'Win95',
		'Win98',
		'WinME',
		'Win 9x 4.90',
		'Windows 3',
		'Windows 95',
		'Windows 98',
		
		'Windows NT 4',
		'Windows NT;',
		'Windows NT 5.0;)',
		'Windows NT 5.1;)',
		'Windows NT 9.',
		'Windows XP 5',
		
		'WinHttp',
		
		'WISEbot',
		'WISENutbot',
		
		//  Vulnerability scanner or trackback
		'Wordpress',
		
		'WWWOFFLE',
		'Vacuum',
		'VCI',
		'Xedant',
		'Xaldon',
		'Xenu',
		'XoviBot',
		
		// Fake Yahoo! bot
		'Yahoo !',
		'Slurb;',
		'Slurb ;',
		'Slurp ;',
		'Search Monkey',
		'/ ysearch',
		'/y search',
		
		'Zeus',
		'ZmEu',
		'ZoomBot',
		'Zyborg'
	];
	
	return textNeedleSearch( $ua, $ua_frags );
}

function fw_uriCheck() {
	static $uri_frags	= [
		// Database NULL
		'0x31303235343830303536',
		
		// Directory traversal
		'../',
		'..\\',
		'..%2F',
		'..%u2216',
		
		// Attempt to reveal PHP version
		'?=PHP',
		
		// DB scan
		'%60information_schema%60',
		'DECLARE%20@',
		'~',
		
		// Shouldn't see fragments in the URI sent to the server
		'#',
		
		// Potential vulnerability scan
		'.git/',
		'%7e',
		'<?=`$_',
		'<?php',
		'<script',
		'%3cscript%20',
		'%27%3b%20',
		'%22http%3a%2f%2f',
		'%255c',
		'%%35c',
		'%25%35%63',
		'%c0%af',
		'%c1%9c',
		'%c1%pc',
		'%c0%qf',
		'%c1%8s',
		'%c1%1c',
		'%c1%af',
		'%e0%80%af',
		'%u',
		'+%2F*%21',
		'%27--',
		'%27 --',
		'%27%23',
		'%27 %23',
		'benchmark%28',
		'IDATH.c\<?',
		'IDATH.c??<script',
		'IDATHKc??<script',
		'insert+into+',
		'r3dm0v3',
		'select+1+from',
		'union+all+select',
		'union+select',
		'waitfor+delay+',
		'w00tw00t'
	];
	
	// Set as path?
	return textNeedleSearch( $_SERVER['REQUEST_URI'], $uri_frags );
}

// Check browser and platform matches
function fw_browserCompat( $ua ) {
	$safari		= textHas( $ua, 'Safari' );
	$chrome		= textHas( $ua, 'Chrome' );
	$trident	= textHas( $ua, 'Trident' );
	
	// Browser can't be Chrome, Safari, *and* Trident
	if ( $chrome && $safari && $trident ) {
		return true;
	}
	
	$edge		= textHas( $ua, 'Edge' );
	// Edge is not trident
	if ( $edge && $trident ) {
		return true;
	}
	
	$linux		= textHas( $ua, 'Linux' );
	$mac		= textHas( $ua, 'Mac OS' );
	$x11		= textHas( $ua, 'X11' );
	$wow64		= textHas( $ua, 'WOW64' );
	
	// Wow64 is Windows
	$nix		= $x11 || $linux || $mac;
	if ( $nix && $wow64 ) {
		return true;
	}
	
	// ...But not with Win64
	if ( textHas( $ua, 'Win64' ) && $wow64 ) {
		return true;
	}
	
	// Fake IE
	if ( textHas( $ua, 'MSIE' ) && !$trident ) {
		return true;
	}
	
	// Trident (IE) on recent Mac OS is unlikely
	if ( $mac && $trident ) {
		return true;
	}
	
	// Can't be Safari and Trident too
	if ( $safari && $trident ) {
		return true;
	}
	
	$ie10		= textHas( $ua, 'MSIE 10.' );
	// Can't be both Edge and IE 10 at the same time
	if ( $ie10 && $edge ) {
		return true;
	}
	
	// IE 10 doesn't belong on Windows 10. Compat mode is IE 7
	if ( textHas( $ua, 'Windows NT 10.' ) && $ie10 ) {
		return true;
	}
	
	// Trident doesn't belong on Nix
	if ( ( $x11 && $trident ) || ( $nix && $trident ) ) {
		return true;
	}
	
	$ie5		= textHas( $ua, 'MSIE 5' );
	// Very old IE on newish Windows
	if ( $ie5 && $wow64 ) {
		return true;
	}
	
	// Old IE in places it doesn't belong
	if ( ( $ie5 && $trident ) || ( $ie5 && $nix ) ) {
		return true;
	}
	
	// New IE in places it doesn't belong
	if ( $ie10 && $nix ) {
		return true;
	}
	
	// User agent switcher
	if ( 
		textHas( $ua, 'Windows Phone' ) && 
		textHas( $ua, 'Android' ) 
	) {
		return true;
	}
	
	return false;
}

// Closer evaluation
function fw_browserCheck( $ua, $val ) {
	// Browsers should send Accept
	if ( !\array_key_exists( 'accept', $val ) ) {
		return true;
	}
	
	$pr	= getProtocol();
	
	// Expect and HTTP/1.0 shouldn't go together
	if (
		textHas( $pr, 'HTTP/1.0' ) && 
		\array_key_exists( 'expect', $val )
	) {
		return true;
	}
	
	// Repeated "windows", "wow64" etc...
	$rpt	= 'windows|wow64|linux|gecko|apple|android';
	if ( \preg_match( '/(' .$rpt . ')(.*?)(\s+)?\1/i', $ua ) ) {
		return true;
	}
	
	// HTTP/1.1 and Cache behavior mismatch
	if (
		textHas( $pr, 'HTTP/1.1' ) && 
		textHas( $val['pragma'] ?? '', 'no-cache' ) && 
		!\array_key_exists( 'cache-control', $val ) 
	) {
		return true;
	}
	
	// Obsolete params
	if ( 
		textHas( $val['cookie'] ?? '', '$Version=0' )	|| 
		\array_key_exists( 'cookie2', $val )		|| 
		textHas( $val['range'] ?? '', '=0-' ) 
	) {
		return true;
	}
	
	$mozilla	= textStartsWith( $ua, [ 'Mozilla' ] );
	if ( $mozilla ) {
		// Long since discontinued
		if ( textNeedleSearch( $ua, [ 'Google Desktop' ] ) ) {
			return true;
		}
	}
	
	// TE sent by IE Mobile, but not Akamai
	if ( \preg_match( '/\bTE\b/i', $val['connection'] ) ) {
		if ( 
			!\array_key_exists( 'akamai-origin-hop', $val ) && 
			textNeedleSearch( $ua, [ 'IEMobile' ] )
		) {
			return true;
		}
	}
	
	return fw_browserCompat( $ua );
}

function fw_checkReferer( $ref ) {
	$srv	= $_SERVER['SERVER_NAME'] ?? '';
	$verb	= getMethod();
	
	if ( !SKIP_LOCAL && empty( $srv ) && $verb != 'get' ) {
		return true;
	}
	
	// These shouldn't have referer
	if ( !in_array( 
		$verb, 
		[ 'put', 'delete', 'patch', 'options', 'head' ] 
	) ) {
		return true;
	}
	
	$url	= \parse_url( $ref );
	$host	= $url['host'] ?? '';
	
	// Post should only come from current host
	if ( 
		0 == \strcasecmp( 'post', $verb ) && 
		0 != \strcasecmp( $srv, $host ) 
	) {
		return true;
	}
	
	return false;
}

function fw_headerCheck() {
	$val = getHeaders( true );
	
	if ( 
		// Must not be used
		\array_key_exists( 'proxy-connection', $val )	|| 
		// This is a response header
		\array_key_exists( 'content-range', $val )	||
		// Suspect request headers
		\array_key_exists( 'x-aaaaaaaaaa', $val )
	) {
		return true;
	}
	
	// Fail, if "referrer" correctly spelled
	if ( \array_key_exists( 'referer', $val ) ) {
		return true;
	}
	
	// Should not be empty, if set, and must contain a colon (:)
	if ( \array_key_exists( 'referrer', $val ) ) {
		$ref	= $val['referrer'] ?? '';
		if ( empty( $val['referrer'] ) ) {
			return true;
		}
		if ( !textHas( $ref, ':' ) ) {
			return true;
		}
		
		if ( fw_checkReferer( $ref ) ) {
			return true;
		}
	}
	
	// Contradicting or empty connections
	$cn	= $val['connection'];
	if ( ( 
		textHas( $cn, 'Keep-Alive' ) && 
		textHas( $cn, 'Close' ) 
	) || empty( $cn ) ) {
		return true;
	}
	
	// Repeated words in connection? E.G. "close, close"
	if ( \preg_match( '/(\w{3,}+)(,|\.)(\s+)?\1/i', $cn ) ) {
		return true;
	}
	
	// Referrer spam
	if ( !empty( $val['via'] ) ) {
		if ( 
			textHas( $val['via'], 'PCNETSERVER' )	|| 
			textHas( $val['via'], 'pinappleproxy' ) 
		) {
			return true;
		}
	}
	
	$ua	= getUA();
	
	// Probably not a bot. Then check browser
	return fw_browserCheck( $ua, $val );
}

function fw_sanityCheck() {
	// Empty host?
	if ( empty( getHost() ) ) {
		visitorError( 400, 'Firewall Host' );
		sendError( 400, errorLang( "invalid", \MSG_INVALID ) );
	}
	
	// None of these should be empty
	$pr	= getProtocol();
	$ua	= getUA();
	$mt	= getMethod();
	
	if ( empty( $pr ) || empty( $ua ) || empty( $mt ) ) {
		return true;
	}
	
	// 'HTTP/' without space Should always be in the protocol
	if ( !fw_has( $pr, 'HTTP/' ) ) {
		return true;
	}
	
	// Suspicious UA lengths ("Mozilla/5." alone is 10 characters)
	$ual	= strlen( $ua );
	if ( $ual < 10 || $ual > 300 ) {
		return true;
	}
	
	// Allowed HTTP methods
	switch( $mt ) {
		case 'get':
		case 'post':
		case 'head':
		case 'connect':
		case 'options':
		case 'patch':
		case 'delete':
		case 'put':
			return false;
		
		// Unrecognized method (E.G TRACE can be exploited)
		default:
			return true;
	}
}

function fw_insertLog() {
	$db	= getDb( \FIREWALL_DATA );
	$stm	= $db->prepare( \FIREWALL_DB_INSERT );
	$stm->execute( [
		':ip'		=> getIP(), 
		':ua'		=> getUA(), 
		':uri'		=> getQS(), 
		':method'	=> getMethod(), 
		':headers'	=> \implode( "\n", getHeaders() )
	] );
}

function fw_start() {
	// Fresh request
	if (
		fw_sanityCheck()	|| 
		fw_uriCheck()		|| 
		fw_uaCheck()		|| 
		fw_headerCheck()
	) {
		fw_insertLog();
		
		// Send kill
		fw_instaKill();
	}
}


// Begin Firewall as soon as it's loaded
fw_start();


