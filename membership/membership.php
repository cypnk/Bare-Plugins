<?php declare( strict_types = 1 );
if ( !defined( 'PATH' ) ) { die(); }

// WORK IN PROGRESS - Do not use

/**
 *  Bare Membership: This plugin lets Bare have registered users for various 
 *  roles such as commenting and managing (via another plugin). This plugin 
 *  depends on the render and moderation plugins
 *  
 *  This plugin requires 'allow_post' to be set to 1 (I.E. Enabled)
 *  
 *  Important: 
 *  After setting the admin username and password, add this plugin to 
 *  'plugins_enabled' after the render and moderation plugins
 */

/**
 *  Note: The following two settings cannot be set in config.json
 *  Only used for first run and will be ignored after user database creation
 *  
 *  Change the password again after logging in as a precaution
 */
define( 'MEMBER_ADMIN_USER',	'' );
define( 'MEMBER_ADMIN_PASS',	'' );


/**
 *  Standard settings
 */

// Enable new member registrations
define( 'MEMBER_REGISTER',	1 );

// Enable logins for existing members
define( 'MEMBER_LOGIN',		1 );

// Maximum username length
define( 'MEMBER_MAX_USER',	180 );

// Minimum username length
define( 'MEMBER_MIN_USER',	1 );

// Minimum password length (there is no maximum except for form submission size)
define( 'MEMBER_MIN_PASS',	5 );

// Minimum time between login attempts in seconds
define( 'MEMBER_LOGIN_DELAY',	10 );

// Maximum number of successive login/register attempts before throttling
define( 'MEMBER_ATTEMPTS',	5 );

// User database (will be created if it doesn't exist)
// WARNING: Altering the users database may cause all users to be deleted
define( 'MEMBER_DATA',		CACHE . 'users.db' );

// Disallowed usernames (one per line or add to 'member_blacklist' in config.json)
define( 'MEMBER_BLACKLIST',	<<<BLACK

BLACK
);


/**
 *  These can be overriden in the "errors" section of the language file 
 *  E.G. en-us.json in the CACHE directory
 */
// User login error
define( 'MSG_LOGINFAIL',	'Error logging in.' );

// Duplicate username
define( 'MSG_USER_EXISTS',	'A user by that name already exists.' );

// Login throttle
define( 'MSG_LOGINWAIT',	'Please wait before attempting to login again.' );

// Username length or missing
define( 'MSG_USER_NAMEERR',	'Username is invalid.' );

// Password too hort
define( 'MSG_PASS_SHORT',	'Password is too short.' );

// Password repeat for registration
define( 'MSG_PASS_REPEAT',	'Password must be repeated.' );

// Passwords don't match for registration
define( 'MSG_PASS_MATCH',	'Repeated passwords do not match.' );

// Login credentials invalid
define( 'MSG_EMPTYCRED',	'Login credentials invalid. Please try again.' );

// Login fail
define( 'MSG_LOGINFAIL',	'Login unsuccssful. Please try again.' );






/**********************************************************************
 *                      Caution editing below
 **********************************************************************/



define( 'AUTH_STATUS_SUCCESS',	0 );
define( 'AUTH_STATUS_NOUSER',	1 );
define( 'AUTH_STATUS_FAILED',	-1 );


define( 'MEMBER_SQL',		<<<SQL
-- Generate a random unique string
CREATE VIEW rnd AS 
SELECT lower( hex( randomblob( 16 ) ) ) AS id;-- --

-- GUID/UUID generator helper
CREATE VIEW uuid AS SELECT lower(
	hex( randomblob( 4 ) ) || '-' || 
	hex( randomblob( 2 ) ) || '-' || 
	'4' || substr( hex( randomblob( 2 ) ), 2 ) || '-' || 
	substr( 'AB89', 1 + ( abs( random() ) % 4 ) , 1 )  ||
	substr( hex( randomblob( 2 ) ), 2 ) || '-' || 
	hex( randomblob( 6 ) )
) AS id;-- --

-- User profiles
CREATE TABLE users (
	id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
	uuid TEXT DEFAULT NULL COLLATE NOCASE,
	username TEXT NOT NULL COLLATE NOCASE,
	password TEXT NOT NULL,
	user_clean TEXT NOT NULL COLLATE NOCASE,
	display TEXT DEFAULT NULL COLLATE NOCASE,
	bio TEXT DEFAULT NULL COLLATE NOCASE,
	created DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
	updated DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
	settings TEXT NOT NULL DEFAULT '{}' COLLATE NOCASE,
	status INTEGER NOT NULL DEFAULT 0
);-- --
CREATE UNIQUE INDEX idx_user_name ON users( username );-- --
CREATE UNIQUE INDEX idx_user_clean ON users( user_clean );-- --
CREATE UNIQUE INDEX idx_user_uuid ON users( uuid )
	WHERE uuid IS NOT NULL;-- --
CREATE INDEX idx_user_created ON users ( created );-- --
CREATE INDEX idx_user_updated ON users ( updated );-- --
CREATE INDEX idx_user_status ON users ( status );-- --

-- User searching
CREATE VIRTUAL TABLE user_search 
	USING fts4( username, tokenize=unicode61 );-- --


-- Cookie based login tokens
CREATE TABLE logins(
	id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
	user_id INTEGER NOT NULL,
	lookup TEXT NOT NULL COLLATE NOCASE,
	updated DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
	hash TEXT DEFAULT NULL,
	
	CONSTRAINT fk_logins_user 
		FOREIGN KEY ( user_id ) 
		REFERENCES users ( id )
		ON DELETE CASCADE
);-- --
CREATE UNIQUE INDEX idx_login_user ON logins( user_id );-- --
CREATE UNIQUE INDEX idx_login_lookup ON logins( lookup );-- --
CREATE INDEX idx_login_updated ON logins( updated );-- --
CREATE INDEX idx_login_hash ON logins( hash )
	WHERE hash IS NOT NULL;-- --


-- Secondary identity providers E.G. two-factor
CREATE TABLE id_providers( 
	id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
	label TEXT NOT NULL COLLATE NOCASE,
	sort_order INTEGER NOT NULL DEFAULT 0,
	
	-- Serialized JSON
	settings TEXT NOT NULL DEFAULT '{}' COLLATE NOCASE
);-- --
CREATE UNIQUE INDEX idx_provider_label ON id_providers( label );-- --
CREATE INDEX idx_provider_sort ON id_providers( sort_order ASC );-- --


-- User authentication and activity metadata
CREATE TABLE user_auth(
	id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
	user_id INTEGER NOT NULL,
	provider_id INTEGER DEFAULT NULL,
	email TEXT DEFAULT NULL COLLATE NOCASE,
	mobile_pin TEXT DEFAULT NULL COLLATE NOCASE,
	info TEXT DEFAULT NULL,
	
	-- Activity
	last_ip TEXT DEFAULT NULL COLLATE NOCASE,
	last_ua TEXT DEFAULT NULL COLLATE NOCASE,
	last_active DATETIME DEFAULT NULL,
	last_login DATETIME DEFAULT NULL,
	last_pass_change DATETIME DEFAULT NULL,
	last_lockout DATETIME DEFAULT NULL,
	last_session_id TEXT DEFAULT NULL,
	
	-- Auth status,
	is_approved INTEGER NOT NULL DEFAULT 0,
	is_locked INTEGER NOT NULL DEFAULT 0,
	
	-- Authentication tries
	failed_attempts INTEGER NOT NULL DEFAULT 0,
	failed_last_start DATETIME DEFAULT NULL,
	failed_last_attempt DATETIME DEFAULT NULL,
	
	created DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
	expires DATETIME DEFAULT NULL,
	
	CONSTRAINT fk_auth_user 
		FOREIGN KEY ( user_id ) 
		REFERENCES users ( id )
		ON DELETE CASCADE, 
		
	CONSTRAINT fk_auth_provider
		FOREIGN KEY ( provider_id ) 
		REFERENCES providers ( id )
		ON DELETE SET NULL
);-- --
CREATE UNIQUE INDEX idx_user_email ON user_auth( email );-- --
CREATE INDEX idx_user_auth_user ON user_auth( user_id );-- --
CREATE INDEX idx_user_auth_provider ON user_auth( provider_id )
	WHERE provider_id IS NOT NULL;-- --
CREATE INDEX idx_user_pin ON user_auth( mobile_pin ) 
	WHERE mobile_pin IS NOT NULL;-- --
CREATE INDEX idx_user_ip ON user_auth( last_ip )
	WHERE last_ip IS NOT NULL;-- --
CREATE INDEX idx_user_ua ON user_auth( last_ua )
	WHERE last_ua IS NOT NULL;-- --
CREATE INDEX idx_user_active ON user_auth( last_active )
	WHERE last_active IS NOT NULL;-- --
CREATE INDEX idx_user_login ON user_auth( last_login )
	WHERE last_login IS NOT NULL;-- --
CREATE INDEX idx_user_session ON user_auth( last_session_id )
	WHERE last_session_id IS NOT NULL;-- --
CREATE INDEX idx_user_auth_approved ON user_auth( is_approved );-- --
CREATE INDEX idx_user_auth_locked ON user_auth( is_locked );-- --
CREATE INDEX idx_user_failed_last ON user_auth( failed_last_attempt )
	WHERE failed_last_attempt IS NOT NULL;-- --
CREATE INDEX idx_user_auth_created ON user_auth( created );-- --
CREATE INDEX idx_user_auth_expires ON user_auth( expires )
	WHERE expires IS NOT NULL;-- --


-- User auth last activity
CREATE VIEW auth_activity AS 
SELECT user_id, 
	provider_id,
	is_approved,
	is_locked,
	last_ip,
	last_ua,
	last_active,
	last_login,
	last_lockout,
	last_pass_change,
	last_session_id,
	failed_attempts,
	failed_last_start,
	failed_last_attempt
	
	FROM user_auth;-- --


-- Auth activity helpers
CREATE TRIGGER user_last_login INSTEAD OF 
	UPDATE OF last_login ON auth_activity
BEGIN 
	UPDATE user_auth SET 
		last_ip			= NEW.last_ip,
		last_ua			= NEW.last_ua,
		last_session_id		= NEW.last_session_id,
		last_login		= CURRENT_TIMESTAMP, 
		last_active		= CURRENT_TIMESTAMP,
		failed_attempts		= 0
		WHERE id = OLD.id;
END;-- --

CREATE TRIGGER user_last_ip INSTEAD OF 
	UPDATE OF last_ip ON auth_activity
BEGIN 
	UPDATE user_auth SET 
		last_ip			= NEW.last_ip, 
		last_ua			= NEW.last_ua,
		last_session_id		= NEW.last_session_id,
		last_active		= CURRENT_TIMESTAMP 
		WHERE id = OLD.id;
END;-- --

CREATE TRIGGER user_last_active INSTEAD OF 
	UPDATE OF last_active ON auth_activity
BEGIN 
	UPDATE user_auth SET last_active = CURRENT_TIMESTAMP
		WHERE id = OLD.id;
END;-- --

CREATE TRIGGER user_last_lockout INSTEAD OF 
	UPDATE OF is_locked ON auth_activity
	WHEN NEW.is_locked = 1
BEGIN 
	UPDATE user_auth SET 
		is_locked	= 1,
		last_lockout	= CURRENT_TIMESTAMP 
		WHERE id = OLD.id;
END;-- --

CREATE TRIGGER user_failed_last_attempt INSTEAD OF 
	UPDATE OF failed_last_attempt ON auth_activity
BEGIN 
	UPDATE user_auth SET 
		last_ip			= NEW.last_ip, 
		last_ua			= NEW.last_ua,
		last_session_id		= NEW.last_session_id,
		last_active		= CURRENT_TIMESTAMP,
		failed_last_attempt	= CURRENT_TIMESTAMP, 
		failed_attempts		= ( failed_attempts + 1 ) 
		WHERE id = OLD.id;
	
	-- Update current start window if it's been 24 hours since 
	-- last window
	UPDATE user_auth SET failed_last_start = CURRENT_TIMESTAMP 
		WHERE id = OLD.id AND ( 
		failed_last_start IS NULL OR ( 
		strftime( '%s', 'now' ) - 
		strftime( '%s', 'failed_last_start' ) ) > 86400 );
END;-- --



-- Login view
-- Usage:
-- SELECT * FROM login_view WHERE lookup = :lookup;
-- SELECT * FROM login_view WHERE name = :username;
CREATE VIEW login_view AS SELECT 
	logins.user_id AS id, 
	users.uuid AS uuid, 
	logins.lookup AS lookup, 
	logins.hash AS hash, 
	logins.updated AS updated, 
	users.status AS status, 
	users.username AS name, 
	users.password AS password, 
	users.settings AS user_settings, 
	ua.is_approved AS is_approved, 
	ua.is_locked AS is_locked, 
	ua.expires AS expires
	
	FROM logins
	JOIN users ON logins.user_id = users.id
	LEFT JOIN user_auth ua ON users.id = ua.user_id;-- --


-- Login regenerate. Not intended for SELECT
-- Usage:
-- UPDATE logout_view SET lookup = '' WHERE user_id = :user_id;
CREATE VIEW logout_view AS 
SELECT user_id, lookup FROM logins;-- --

-- Reset the lookup string to force logout a user
CREATE TRIGGER user_logout INSTEAD OF UPDATE OF lookup ON logout_view
BEGIN
	UPDATE logins SET lookup = ( SELECT id FROM rnd ), 
		updated = CURRENT_TIMESTAMP
		WHERE user_id = NEW.user_id;
END;-- --

-- New user, generate UUID, insert user search and create login lookups
CREATE TRIGGER user_insert AFTER INSERT ON users FOR EACH ROW 
BEGIN
	-- Create search data
	INSERT INTO user_search( docid, username ) 
		VALUES ( NEW.id, NEW.username );
	
	-- New login lookup
	INSERT INTO logins( user_id, lookup )
		VALUES( NEW.id, ( SELECT id FROM rnd ) );
	
	UPDATE users SET uuid = ( SELECT id FROM uuid )
		WHERE id = NEW.id;
END;-- --

-- Update last modified
CREATE TRIGGER user_update AFTER UPDATE ON users FOR EACH ROW
BEGIN
	UPDATE users SET updated = CURRENT_TIMESTAMP 
		WHERE id = OLD.id;
	
	UPDATE user_search 
		SET username = NEW.username || ' ' || NEW.display
		WHERE docid = OLD.id;
END;-- --


-- Delete user search data following user delete
CREATE TRIGGER user_delete BEFORE DELETE ON users FOR EACH ROW 
BEGIN
	DELETE FROM user_search WHERE rowid = OLD.rowid;
END;-- --



-- User roles
CREATE TABLE roles(
	id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
	label TEXT NOT NULL COLLATE NOCASE,
	description TEXT DEFAULT NULL COLLATE NOCASE
);-- --
CREATE UNIQUE INDEX idx_role_label ON roles( label ASC );-- --

-- Third party role permission providers
CREATE TABLE permission_providers(
	id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
	label TEXT NOT NULL COLLATE NOCASE,
	settings TEXT DEFAULT NULL COLLATE NOCASE
);-- --
CREATE UNIQUE INDEX idx_perm_provider_label ON permission_providers( label ASC );-- --

-- Role permissions
CREATE TABLE role_privileges(
	id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
	role_id INTEGER NOT NULL,
	permission_id INTEGER DEFAULT NULL,
	
	-- Serialized JSON
	settings TEXT NOT NULL DEFAULT '{}' COLLATE NOCASE,
	
	CONSTRAINT fk_privilege_role 
		FOREIGN KEY ( role_id ) 
		REFERENCES roles ( id )
		ON DELETE CASCADE, 
	
	CONSTRAINT fk_privilege_provider
		FOREIGN KEY ( permission_id ) 
		REFERENCES permission_providers ( id )
		ON DELETE RESTRICT
);-- --
CREATE INDEX idx_privilege_role ON role_privileges( role_id );-- --
CREATE INDEX idx_privilege_provider ON role_privileges ( permission_id )
	WHERE permission_id IS NOT NULL;-- --

-- User role relationships
CREATE TABLE user_roles(
	id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
	role_id INTEGER NOT NULL,
	user_id INTEGER NOT NULL,
	
	CONSTRAINT fk_user_roles_user 
		FOREIGN KEY ( user_id ) 
		REFERENCES users ( id )
		ON DELETE CASCADE,
	
	CONSTRAINT fk_user_roles_role 
		FOREIGN KEY ( role_id ) 
		REFERENCES roles ( id )
		ON DELETE CASCADE
);-- --
CREATE UNIQUE INDEX idx_user_role ON 
	user_roles( role_id, user_id );-- --

-- Role based user permission view
CREATE VIEW user_permission_view AS 
SELECT 
	user_id AS id, 
	GROUP_CONCAT( DISTINCT roles.label ) AS label,
	GROUP_CONCAT( 
		COALESCE( rp.settings, '{}' ), ',' 
	) AS privilege_settings,
	GROUP_CONCAT( 
		COALESCE( pr.settings, '{}' ), ',' 
	) AS provider_settings
	
	FROM user_roles
	JOIN roles ON user_roles.role_id = roles.id
	LEFT JOIN role_privileges rp ON roles.id = rp.role_id
	LEFT JOIN permission_providers pr ON rp.permission_id = pr.id;

SQL
);


/**
 *  Process username
 */
function username( string $name ) {
	static $maxu;
	if ( !isset( $maxu ) ) {
		$maxu = 
		config( 'member_max_user', \MEMBER_MAX_USER, 'int' );
	}
	
	return title( $name, $maxu );
}

/**
 *  Helper to turn full username to index-friendly term
 *  
 *  @param string	$name		Entered username 
 *  @return string
 */
function cleanUsername( string $name ) {
	return 
	username( unifySpaces( lowercase( bland( 
		normal( $name ) , true 
	) ), '' );
}

/**
 *  Reset authenticated user data types for processing
 *  
 *  @param array	$user		Stored user in database/session
 *  @return array
 */
function formatAuthUser( array $user ) : array {
	$user['is_approved']	??= false;
	$user['is_locked']	??= false;
	$user['user_settings']	??= [];
	
	return [
		'id'		=> ( int ) ( $user['id'] ?? 0 ), 
		'status'	=> ( int ) ( $user['status'] ?? 0 ), 
		'name'		=> $user['name'] ?? '', 
		'hash'		=> $user['hash'] ?? '',
		'is_approved'	=> $user['is_approved'] ? true : false,
		'is_locked'	=> $user['is_locked'] ? true : false, 
		'auth'		=> $user['auth'] ?? '',
		'settings'	=> 
			\is_array( $user['user_settings'] ) ? 
				$user['user_settings'] : []
	];
}

/**
 *  Check user authentication session
 *  
 *  @param bool		$delete		Forget existing auth if true
 *  @return array
 */
function authUser( bool $delete = false ) : array {
	static $user;
	sessionCheck();
	
	if ( $delete ) {
		unset( $user );
		return [];
	}
	
	if ( isset( $user ) ) {
		return $user;
	}
	
	if ( 
		empty( $_SESSION['user'] ) || 
		!\is_array(  $_SESSION['user'] ) 
	) { 
		// Session was empty? Check cookie lookup
		$cookie	= $_COOKIE['user'] ?? '';
		if ( empty( $cookie ) ) {
			return [];
		}
		// Sane defaults
		if ( mb_strlen( $cookie, '8bit' ) > 255 ) {
			return [];
		}
		$user	= findCookie( pacify( $cookie ) );
		
		if ( empty( $user ) ) {
			return [];
		}
		// Reset data types
		$user	= formatAuthUser( $user );
		
		// User found, apply authorization
		setAuth( $user, true );
		
		// Update activity
		updateUserActivity( $user['id'], 'active' );
		return $_SESSION['user'];
		
	} else {
		// Fetched results must be a 4-item array
		$user		= $_SESSION['user'];
		if ( \count( $user ) !== 8 ) { 
			$_SESSION['user']	= '';
			unset( $user );
			return []; 
		}
	}
	
	// Reset data types
	$user			= formatAuthUser( $user );
	
	// Check if current browser changed since auth token creation
	$auth			= 
	\hash( 'tiger160,4', getUA() . $user['hash'] );
	
	if ( 0 != \strcmp( ( string ) $user['auth'], $auth ) ) { 
		unset( $user );
		return []; 
	}
	
	updateUserActivity( $user['id'], 'active' );
	return $user;
}
	
/**
 *  Apply user auth session and save the current signature hash
 *  
 *  @param array	$user		User info stored in database
 *  @param bool		$cookie		Set auth cookie if true
 */
function setAuth( array $user, bool $cookie ) {
	sessionCheck();
	$auth			= 
	\hash( 'tiger160,4', getUA() . $user['hash'] );
	
	// Set user session data
	$_SESSION['user']	= [
		'id'		=> $user['id'],
		'status'	=> $user['status'],
		'name'		=> $user['name'],
		'is_approved'	=> $user['is_approved'],
		'is_locked'	=> $user['is_locked'],
		'auth'		=> $auth,
		'settings'	=> $user['settings']
	];
	
	if ( $cookie ) {
		// Set cookie lookup code
		\setcookie( 'user', $user['lookup'], 1, \COOKIE_PATH );
	}
}
	
/**
 *  End user session
 */
function endAuth() {
	sessionCheck( true );
	
	// Delete existing auth
	authUser( true );
	
	// Delete lookup cookie
	deleteCookie( 'user' );
}


/**
 *  Login path redirect helper
 *  
 *  @param string	$redir		Relative path to append to login
 */
function sendLogin( string $redir = '' ) {
	$path = eventRoutePrefix( 'memberlogin', 'login' ) . '/';
	
	// Send redirect with current login path prefixed
	sendPage( 401, $path . $redir );
}



/**
 *  Hash password to storage safe format
 *  
 *  @param string	$password	Raw password as entered
 *  @return string
 */
function hashPassword( string $password ) : string {
	return 
	\base64_encode(
		\password_hash(
			\base64_encode(
				\hash( 'sha384', $password, true )
			),
			\PASSWORD_DEFAULT
		)
	);
}

/**
 *  Check hashed password
 *  
 *  @param string	$password	Password exactly as entered
 *  @param string	$stored		Hashed password in database
 */
function verifyPassword( 
	string		$password, 
	string		$stored 
) : bool {
	$stored = \base64_decode( $stored, true );
	if ( false === $stored ) {
		return false;
	}
	
	return 
	\password_verify(
		\base64_encode( 
			\hash( 'sha384', $password, true )
		),
		$stored
	);
}
	
/**
 *  Check if user password needs rehashing
 *  
 *  @param string	$stored		Already hashed, stored password
 *  @return bool
 */
function passNeedsRehash( 
	string		$stored 
) : bool {
	$stored = \base64_decode( $stored, true );
	if ( false === $stored ) {
		return false;
	}
	
	return 
	\password_needs_rehash( $stored, \PASSWORD_DEFAULT );
}



/**
 *  Form validation
 */

/**
 *  Preliminary field filter from raw user input to check for empty etc...
 *  
 *  @param mixed	$input		Raw user input
 *  @return string
 */
function memberPrefilter( $input ) : string {
	if ( \is_array( $input ) ) {
		return '';
	}
	
	return trim( unifySpaces( ( string ) $input ) );
}

/**
 *  Bio/profile filter helper
 *  
 *  @param mixed	$bio Raw bio data entered by the user
 *  @return string
 */
function memberBio( $bio ) : string {
	if ( \is_array( $bio ) ) {
		return '';
	}
	
	return entities( pacify( $bio ) );
}

/**
 *  Check form processing status and send appropriate error on failure (exits)
 *  
 *  @param int		$status		Form validation status
 */
function memberFormStatus( int $status ) {
	switch( $status ) {
		case FORM_STATUS_INVALID:
		case FORM_STATUS_EXPIRED:
			sendDenied( 'Expired', 'expired', \MSG_EXPIRED );
		
		case FORM_STATUS_FLOOD:
			visitorError( 429, 'Flood' );
			sendError( 429, errorLang( "toomany", \MSG_TOOMANY ) );
	}
}

/**
 *  Basic username filter
 *  
 *  @param string	$user		Raw username as entered
 *  @return string
 */
function memberName( string $user ) : string {
	$user	= memberPrefilter( $user );
	$umin	= config( 'member_min_user', \MEMBER_MIN_USER, 'int' );
	$umax	= config( 'member_max_user', \MEMBER_MAX_USER, 'int' );
	$usent	= strsize( $username );
	
	if ( $usent > $umin || $usent < $umin ) {
		return '';
	}
	
	return $user;
}

/**
 *  Detailed credential validation
 *  
 *  @param array	$data		Form data sent by the user
 *  @return array Error messages
 */
function memberCredStatus( array $data ) : array {
	// Possible spaces only password
	$tpass = memberPrefilter( $data['password'] ?? '' );
	
	if ( empty( $data['username'] ) || empty( $tpass ) ) {
		return [ errorLang( 'member_err_emptycred', \MSG_EMPTYCRED ) ];
	}
	
	$data['username'] = memberName( $data['username'] );
	$pmin	= config( 'member_min_pass' \MEMBER_MIN_PASS, 'int' );
	$psent	= strsize( $tpass ) ;
	
	$msg	= [];
	if ( empty( $data['username'] ) ) {
		$msg[] = errorLang( 'member_err_user_nameerr', \MSG_USER_NAMEERR );
	}
	
	if ( $psent < $pmin ) {
		$msg[]	= errorLang( 'member_err_pass_short', \MSG_PASS_SHORT );
	}
	
	return $msg;
}

/**
 *  User login form
 *  
 *  @param int		$status		Form validation status
 *  @param array	$msg		Any error messages sent back
 *  @param string	$user		Login form sent with specific username
 */
function loginForm( int &$status, array &$msg, string $user = '' ) : array {
	static $filter = [
		'nonce'		=> [
			'filter'	=> 
				\FILTER_SANITIZE_FULL_SPECIAL_CHARS,
			'options'	=> [ 'default' => '' ]
		],
		'token'		=> [
			'filter'	=> 
				\FILTER_SANITIZE_FULL_SPECIAL_CHARS,
			'options'	=> [ 'default' => '' ]
		],
		'username'	=> [
			'filter'	=> \FILTER_CALLBACK,
			'options'	=> 'memberPrefilter'
		],
		
		// Passwords handled differently from other inputs
		'password'	=> \FILTER_UNSAFE_RAW,
		
		// "Remember me"
		'rem'		=> [
			'filter'	=> \FILTER_VALIDATE_INT,
			'options'	=> [
				'default'	=> 0,
				'min_range'	=> 0,
				'max_range'	=> 1
			]
		]
	];
	
	// Form fields being verified
	$fields	= [ 
		'nonce', 
		'token', 
		'username=' . $user, 
		'password',  
		'rem' 
	];
	
	// Validate form status
	$status = 
	validateForm( 'loginform', false, true, $fields );
	
	if ( $status == \FORM_STATUS_VALID ) {
		// Raw user input
		$data	= \filter_input_array( \INPUT_POST, $filter );
		
		$msg = memberCredStatus( $data );
		if ( !empty( $msg ) ) {
			return [];
		}
		
		// Login form hook
		hook( [ 'loginform', [ 
			'input'		=> $data,
			'fields'	=> $fields,
			'messages'	=> $msg,
			'formstatus'	=> $status
		] ] );
		
		return [ 
			'username'	=> $data['username'],
			'password'	=> ( string ) $data['password'],
			'rem'		=> $data['rem']
		];
	}
	
	return [];
}

/**
 *  New user registration form
 *  
 *  @param int		$status		Form validation status
 *  @param array	$msg		Any error messages sent back
 *  @return array
 */
function registerForm( int &$status, array &$msg ) : array {
	static $filter = [
		'nonce'		=> \FILTER_SANITIZE_FULL_SPECIAL_CHARS,
		'token'		=> \FILTER_SANITIZE_FULL_SPECIAL_CHARS,
		'username'	=> [
			'filter'	=> \FILTER_CALLBACK,
			'options'	=> 'memberPrefilter'
		],
		
		// Passwords handled differently from other inputs
		'password'	=> \FILTER_UNSAFE_RAW,
		'password2'	=> \FILTER_UNSAFE_RAW,
		
		// "Remember me"
		'rem'		=> [
			'filter'	=> \FILTER_VALIDATE_INT,
			'options'	=> [
				'default'	=> 0,
				'min_range'	=> 0,
				'max_range'	=> 1
			]
		]
	];
	
	static $fields	= [ 
		'nonce', 
		'token', 
		'username=', 
		'password', 
		'password2', 
		'rem' 
	];
	
	$status = 
	validateForm( 'registerform', false, true, $fields );
	
	if ( $status == \FORM_STATUS_VALID ) {
		$data	= \filter_input_array( \INPUT_POST, $filter );
		
		// Check base credentials
		$msg[]	= memberCredStatus( $data );
		
		// Repeat password
		$trp2	= memberPrefilter( $data['password2'] );
		if ( empty( $trp2 ) ) {
			$msg[] = 
			errorLang( 'member_err_pass_repeat', \MSG_PASS_REPEAT );
			
		} else {
			$data['password'] = ( string ) $data['password'];
			
			if ( 0 !== \strcmp( 
				$data['password'], 
				( string ) $data['password2'] ) ) {
				$msg[] = 
				errorLang( 
					'member_err_pass_match', 
					\MSG_PASS_MATCH 
				);
			}
		}
		
		// Register form hook
		hook( [ 'registerform', [ 
			'input'		=> $data,
			'fields'	=> $fields,
			'messages'	=> $msg,
			'formstatus'	=> $status
		] ] );
		
		if ( !empty( $msg ) ) {
			return [];
		}
		
		return [ 
			'username'	=> $data['username'],
			'password'	=> ( string ) $data['password'],
			'rem'		=> $data['rem']
		];
	}
	
	return [];
}


/**
 *  Change existing user display profile
 */
function profileForm( int &$status, int $id ) : array {
	string $filter = [
		'nonce'		=> \FILTER_SANITIZE_FULL_SPECIAL_CHARS,
		'token'		=> \FILTER_SANITIZE_FULL_SPECIAL_CHARS,
		'id'		=> [
			'filter'	=> \FILTER_VALIDATE_INT,
			'options'	=> [
				'default'	=> 0,
				'min_range'	=> 1
			]
		],
		
		// Display title, different from username
		'display'	=> [
			'filter'	=> \FILTER_CALLBACK,
			'options'	=> 'title'
		],
		
		// Filter on output
		'bio'		=> [
			'filter'	=> \FILTER_CALLBACK,
			'options'	=> 'memberBio'
		]
	];
	
	static $fields	= [ 
		'nonce', 
		'token', 
		'id=' . $id,
		'display', 
		'bio' 
	];
	
	$status = 
	validateForm( 'profileform', false, true, $fields );
	
	if ( $status == FORM_STATUS_VALID ) {
		$data	= \filter_input_array( \INPUT_POST, $filter );
		
		// TODO: Profile data validation
		
		// Register form hook
		hook( [ 'profileform', [ 
			'input'		=> $data,
			'fields'	=> $fields,
			'formstatus'	=> $status
		] ] );
		return $data;
	}
	return [];
}

/**
 *  Change existing user password (requires old password)
 */
function changePassForm( int &$status, int $id ) : array {
	static $filter = [
		'nonce'		=> \FILTER_SANITIZE_FULL_SPECIAL_CHARS,
		'token'		=> \FILTER_SANITIZE_FULL_SPECIAL_CHARS,
		'id'		=> [
			'filter'	=> \FILTER_VALIDATE_INT,
			'options'	=> [
				'default'	=> 0,
				'min_range'	=> 1
			]
		],
		
		// Passwords handled differently from other inputs
		'old_password'	=> \FILTER_UNSAFE_RAW,
		'new_password'	=> \FILTER_UNSAFE_RAW
	];
	
	$fields	= [ 
		'nonce', 
		'token', 
		'id=' . $id
	];
	
	$status = 
	validateForm( 'passwordform', false, true, $fields );
	
	$status	= verifyNoncePair( $data['token'], $data['nonce'] );
	if ( $status == FORM_STATUS_VALID ) {
		$data	= \filter_input_array( \INPUT_POST, $filter );
		$trold	= memberPrefilter( $data['old_password'] ?? '' );
		$trnew	= memberPrefilter( $data['new_password'] ?? '' );
		
		if ( empty( $trold ) || empty( $trnew ) ) {
			return [];
		}
		
		$data['old_password'] = ( string ) $data['old_password'];
		$data['new_password'] = ( string ) $data['new_password'];
		return $data;
	}
	return [];
}


/**
 *  Auth helper. Redirect to login if user isn't already logged in
 *  
 *  @param array	$user	Authenticated user information
 *  @param string	$redir	Redirect path after login
 */
function checkLogin( array &$user, string $redir = 'profile' ) {
	// Get user profile data first
	$user	= authUser();
	if ( empty( $user ) ) {
		// Send to login
		sendLogin( $redir );
	}
}

/**
 *  Check elevated user level
 *  
 *  @param int		$level	Minimum status level
 *  @param string	$redir	Redirect path after login
 *  @return array
 */
function checkElevated( int $level, string $redir ) {
	$user = [];
	checkLogin( $user, $redir );
	
	// Send denied if minimum level not met
	if ( $user['status'] <= $level ) {
		sendDenied();
	}
	
	return $user;
}


/**
 *  Set a new password for the user
 *  
 *  @param int		$id		User ID to change password
 *  @param string	$param		Raw password as entered
 *  @return bool
 */
function savePassword( int $id, string $password ) : bool {
	$sql	= 
	"UPDATE users SET password = :password 
		WHERE id = :id";
	
	return
	setUpdate( $sql, [ 
		':password'	=> hashPassword( $password ), 
		':id'		=> $id 
	], \MEMBER_DATA );
}


/**
 *  User data functions
 */

/**
 *  Find user authorization by cookie lookup
 *  
 *  @param string	$lookup		Raw cookie lookup term
 *  @return array
 */
function findCookie( string $lookup ) : array {
	$sql = "SELECT * FROM login_view
		WHERE lookup = :lookup LIMIT 1;";	
	$db	= getDb( \MEMBER_DATA );
	$stm	= $db->prepare( $sql );
	
	// First find lookup
	if ( $stm->execute( [ ':lookup' => $lookup ] ) ) {
		$results = $stm->fetchAll();
	}
	
	// No logins found
	if ( empty( $results ) ) {
		return [];
	}
	
	// One login found
	$user	= $results[0];
	$cexp	= config( 'cookie_exp', \COOKIE_EXP, 'int' );
	
	// Check for cookie expiration
	if ( ( time() - ( ( int ) $user['updated'] ) ) > $cexp ) {
		$user['lookup']	= 
		resetLookup( ( int ) $user['id'] );
	}
	
	return $user;
}

/**
 *  Reset cookie lookup token and return new lookup
 *  
 *  @param int		$id		Logged in user's ID
 *  @return string
 */
function resetLookup( int $id ) : string {
	$db	= getDb( \MEMBER_DATA );
	$stm	= 
	$db->prepare( 
		"UPDATE logout_view SET lookup = '' 
			WHERE user_id = :id;" 
	);
	
	if ( $stm->execute( [ ':id' => $id ] ) ) {
		// SQLite should have generated a new random lookup
		$rst = 
		$db->prepare( 
			"SELECT lookup FROM logins WHERE 
				user_id = :id;"
		);
		
		if ( $rst->execute( [ ':id' => $id ] ) ) {
			return $stm->fetchColumn();
		}
	}
	
	return '';
}

/**
 *  Get profile details by id
 *  
 *  @param int		$id		User's id
 *  @return array
 */
function findUserById( int $id ) : array {
	$sql		= 
	"SELECT * FROM login_view WHERE id = :id LIMIT 1;";
	$data	= getResults( $sql, [ ':id' => $id ], \MEMBER_DATA );
	if ( empty( $data ) ) {
		return [];
	}
	return $data[0];
}

/**
 *  Get login details by username
 *  
 *  @param string	$username	User's login name as entered
 *  @return array
 */
function findUserByUsername( string $username ) : array {
	$sql		= 
	"SELECT * FROM login_pass WHERE username = :user LIMIT 1;";
	$data	= getResults( $sql, [ ':user' => $username ], \MEMBER_DATA );
	if ( empty( $data ) ) {
		return [];
	}
	return $data[0];
}

/**
 *  Update the last activity IP of the given user
 *  Most of these actions use triggers in the database
 *  
 *  @param int		$id	User unique identifier
 *  @param string	$mode	Activity type
 *  @return bool
 */
function updateUserActivity(
	int	$id, 
	string	$mode	= '' 
) : bool {
	
	$now	= utc();
	switch ( $mode ) {
		case 'active':
			$sql	= 
			"UPDATE auth_activity SET 
				last_ip		= :ip, 
				last_ua		= :ua, 
				last_session_id = :sess 
				WHERE user_id = :id;";
			
			$params = [
				':ip'	=> getIP(), 
				':ua'	=> getUA(), 
				':sess'	=> \session_id(), 
				':id'	=> $id
			];
			break;
			
		case 'login':
			$sql	= 
			"UPDATE auth_activity SET 
				last_ip		= :ip, 
				last_ua		= :ua, 
				last_login	= :login, 
				last_session_id = :sess 
				WHERE user_id = :id;";
			
			$params = [
				':ip'		=> getIP(), 
				':ua'		=> getUA(),
				':login'	=> $now,
				':sess'		=> \session_id(),
				':id'		=> $id
			];
			break;
		
		case 'passchange':
			// Change table itself instead of the view
			$sql	= 
			"UPDATE user_auth SET 
				last_ip			= :ip, 
				last_ua			= :ua, 
				last_active		= :active,
				last_pass_change	= :change, 
				last_session_id		= :sess 
				WHERE user_id = :id;";
			
			$params = [
				':ip'		=> getIP(), 
				':ua'		=> getUA(),
				':active'	=> $now,
				':change'	=> $now,
				':sess'		=> \session_id(),
				':id'		=> $id
			];
			break;
		
		case 'failedlogin':
			$sql	= 
			"UPDATE auth_activity SET 
				last_ip			= :ip, 
				last_ua			= :ua, 
				last_session_id		= :sess, 
				failed_last_attempt	= :fdate
				WHERE user_id = :id;";
				
			$params = [
				':ip'		=> getIP(), 
				':ua'		=> getUA(),
				':sess'		=> \session_id(),
				':fdate'	=> $now,
				':id'		=> $id
			];
			break;
		
		case 'lock':
			$sql	= 
			"UPDATE auth_activity SET 
				is_locked = 1 WHERE id = :id;";
			$params	= [ ':id' => $id ];
			break;
			
		case 'unlock':
			$sql	= 
			"UPDATE user_auth SET 
				is_locked = 0 WHERE id = :id;";
			$params	= [ ':id' => $id ];
			break;
		
		case 'approve':
			$sql	= 
			"UPDATE user_auth SET 
				is_approved = 1 WHERE id = :id;";
			$params	= [ ':id' => $id ];
			break;
			
		case 'unapprove':
			$sql	= 
			"UPDATE user_auth SET 
				is_approved = 0 WHERE id = :id;";
			$params	= [ ':id' => $id ];
			break;
			
		default:
			// First run? Create or replace auth basics
			
			// Auto approve new auth?
			$ap = 
			config( 'auto_approve_reg', 
				\AUTO_APPROVE_REG, 'bool' );
			
			return 
			setInsert( 
				"REPLACE INTO user_auth ( 
					user_id, last_ip, last_ua, 
					last_session_id, is_approved
				) VALUES( :id, :ip, :ua, :sess, :ap );", 
				[
					':id'	=> $id, 
					':ip'	=> getIP(), 
					':ua'	=> getUA(),
					':sess'	=> \session_id(),
					':ap'	=> $ap ? 1 : 0
				], 
				\FORUM_DATA 
			) ? true : false;
	}
	
	return setUpdate( $sql, $params, \FORUM_DATA );
}

/**
 *  Login user credentials
 *  
 *  @param string	$username	Login name to search
 *  @param string	$password	User provided password
 *  @param int		$status		Authentication success etc...
 *  @return array
 */
function authByCredentials(
	string	$username,
	string	$password,
	int	&$status
) : array {
	$user = findUserByUsername( $username );
	
	// No user found?
	if ( empty( $user ) ) {
		$status = \AUTH_STATUS_NOUSER;
		return [];
	}
	
	// Verify credentials
	if ( verifyPassword( $password, $user['password'] ) ) {
		
		// Refresh password if needed
		if ( passNeedsRehash( $user['password'] ) ) {
			savePassword( ( int ) $user['id'], $password );
		}
		
		$status = \AUTH_STATUS_SUCCESS;
		return $user;
	}
	
	// Login failiure
	$status = \AUTH_STATUS_FAILED;
	return [];
}


/**
 *  Limit login attempts
 */
function loginBuffer() {
	if ( empty( $_SESSION['login'] ) ) {
		$_SESSION['login']	= [ 1, time() ];
		return;
	}
	
	$tdata			= $_SESSION['login'];
	
	if ( !\is_array( $tdata ) ) {
		$tdata	=	[ 1, time() ];
	} else {
		if ( count( $tdata ) != 2 ) {
			$tdata	=	[ 1, time() ];
		}
		$tdata	= 
		[ ( int ) $tdata[0], ( int ) $tdata[1] ];
	}
	// Increment attempts
	$_SESSION['login']	= [ $tdata[0] + 1, time() ];
	
	// Check if form is being accessed too quickly
	$atm	= config( 'member_attempts', MEMBER_ATTEMPTS, 'int' );
	$delay	= config( 'member_login_delay', MEMBER_LOGIN_DELAY, 'int' );
	$cur	= \abs( time() - $tdata[1] );
	if ( $cur  < $delay && $tdata[0] > $atm ) {
		sendError( 429, errorLang( 
			'member_loginwait', \MSG_LOGINWAIT 
		) );
	} elseif( $cur > $delay ) {
		// Reset
		$_SESSION['login']	= [ 1, time() ];
	}
}

/**
 *  Check blacklist for username
 *  
 *  @param string	$name		Suspect username
 *  @return bool
 */
function checkUser( string $name ) : bool {
	static $users; 
	if ( !isset( $users ) ) {
		$banned	= config( 'member_blacklist', \MEMBER_BLACKLIST );
		$users	= \is_array( $banned ) ? 
				$banned : 
				lineSettings( $banned, -1, 'title' );
	}
	
	return \in_array( $name, $banned );
}

/**
 *  End current session
 */
function processLogout( bool $redir ) {
	$user = authUser();
	if ( !empty( $user ) ) {
		endAuth();
		// Reset cookie lookup hash
		resetLookup( $user['id'] );
	}
	if ( $redir ) {
		sendPage( '', 202 );
	}
}

/**
 *  Process user login
 */
function processLogin( array $data, int &$status ) {
	sessionCheck();
	
	// Check for banned username
	if ( checkUser( $data['username'] ) ) {
		sendError( 403, errorLang( 
			'member_loginfail', \MSG_LOGINFAIL 
		) );
		
		// Banned or suspended user check
		$stored = exactFilter( $data['username'], 'username' );
		if ( !empty( $stored ) ) {
			// TODO: Check duration and filter for banned user
			sendError( 403, errorLang( 
				'member_loginfail', \MSG_LOGINFAIL 
			) );
		}
	}
	
	$status = \AUTH_STATUS_FAILED;
	$user	= 
	authByCredentials( 
		$data['username'], 
		$data['password'], 
		$status
	);
	
	switch( $status ) {
		case \AUTH_STATUS_SUCCESS:
			// "Remember me"
			$rem	=  ( bool ) ( $data['rem'] ?? 0 );
			
			// Format auth user
			$user	= formatAuthUser( $user );
			
			// Set login session
			setAuth( $user, $rem );
			
			// Set login activity
			updateUserActivity( $user['id'], 'login' );
			
			// Check a redirect path
			$path	= $data['all'] ?? '';
			
			// Redirect to path
			sendPage( $path, 202 );
			break;
			
		case \AUTH_STATUS_NOUSER:
			// TODO: Do no user things
			// Fall through to login fail
		default:
			sendError( 403, errorLang( 
				'member_loginfail', \MSG_LOGINFAIL 
			) );
			
	}
}

/**
 *  Process user registration
 */
function processRegister( array $data ) {
	sessionCheck();
	
	// Check for banned username
	if ( checkUser( $data['username'] ) ) {
		sendError( 
			401, 
			errorLang( 'member_err_user_exists', \MSG_USER_EXISTS ) 
		);
	}
	
	// Banned or suspended username
	$stored = exactFilter( $data['username'], 'username' );
	if ( !empty( $stored ) ) {
		sendError( 
			401, 
			errorLang( 'member_err_user_exists', \MSG_USER_EXISTS ) 
		);
	}
	
	$existing	= findUserByUsername( $data['username'] );
	if ( !empty( $existing ) ) {
		sendError( 
			401, 
			errorLang( 'member_err_user_exists', \MSG_USER_EXISTS ) 
		);
	}
	
	// Complete registration
	$data		= saveUser( $data );
	
	// Check if saving succeeded
	if ( empty( $data['id'] ) ) {
		sendError( 
			500, 
			errorLang( 'generic', \MSG_GENERIC ) 
		);
	}
	
	// Get complete info
	$existing	= findUserByUsername( $data['username'] );
	
	// "Remember me"
	$rem		=  ( bool ) ( $data['rem'] ?? 0 );
	
	// Format data
	$user		= formatAuthUser( $existing );
	
	// Create auth
	updateUserActivity( $user['id'] );
	
	// Set authentication
	setAuth( $user, $rem );
	
	// Check a redirect path
	$path		=
		eventRoutePrefix( 'memberlogin', 'login' ) . '/' . 
		( $data['all'] ?? '' );
	
	// Redirect to login
	sendPage( $path, 202 );
}

// TODO: Process user database creation
function memberDBCreated( string $event, array $hook, array $params ) {
	if ( !isset( $params['dbname'] ) ) {
		return;
	}
	
	// New user database was created
	if ( 0 == \strcmp( $params['dbname'], \MEMBER_DATA ) ) {
		$user	= username( \MEMBER_ADMIN_USER );
		
		if ( 
			empty( $user ) ||
			empty( \MEMBER_ADMIN_PASS )
		) {
			logError( 'Membership: Default admin user and/or password not set' );
			return;
		}
		
		$id	= 
		dataExec( 
			"INSERT INTO users ( username, uesr_clean, password ) 
				VALUES ( :user, :clean, :pass );", 
			[ 
				':user'		=> $user,
				':clean'	=> cleanUsername( $user ), 
				':pass'		=> hashPassword( \MEMBER_ADMIN_PASS )
			], 
			'insert', 
			\MEMBER_DATA 
		);
		
		if ( empty( $id ) ) {
			logError( 'Membership: Error creating admin user' );
		}
	}
}

// TODO: Build login form
function memberLoginRoute( string $event, array $hook, array $params ) {
	$reg = config( 'member_login', \MEMBER_LOGIN, 'int' );
	if ( !$reg ) {
		sendNotFound( 'Membership: Login disabled' );
	}
	
	shutdown( 'cleanup' );
	send( 200, 'Login page' );
}

// TODO: Process sent login
function memberLoginProcess( string $event, array $hook, array $params ) {
	$reg = config( 'member_login', \MEMBER_LOGIN, 'bool' );
	if ( !$reg ) {
		sendNotFound( 'Membership: Login disabled' );
	}
	
	loginBuffer();
	
	$status	= \FORM_STATUS_INVALID;
	$msg	= [];
	$form	= loginForm( $status, $msg );
	
	// Check form status
	memberFormStatus( $status );
	
	// Check form data
	if ( !empty( $msg ) ) {
		// TODO: Fail with message
	}
	// TODO: Put through content filter
	
	processLogin( $form, $status );
}

// TODO: Build register form
function memberRegisterRoute( string $event, array $hook, array $params ) {
	$reg = config( 'member_register', \MEMBER_REGISTER, 'bool' );
	if ( !$reg ) {
		sendNotFound( 'Membership: Registration disabled' );
	}
	
	shutdown( 'cleanup' );
	send( 200, 'Register page' );
}

// TODO: Process sent registration
function memberRegisterProcess( string $event, array $hook, array $params ) {
	$reg = config( 'member_register', \MEMBER_REGISTER, 'bool' );
	if ( !$reg ) {
		sendNotFound( 'Membership: Registration disabled' );
	}
	
	loginBuffer();
	
	$status	= \FORM_STATUS_INVALID;
	$msg	= [];
	$form	= registerForm( $status, $msg );
	
	memberFormStatus( $status );
	
	// Check form data
	if ( !empty( $msg ) ) {
		// TODO: Fail with message
	}
	// TODO: Put through content filter
	
	processRegister( $form )
}



/**
 *  Member settings validator
 */
function checkMemberConfig( string $event, array $hook, array $params ) {
	$filter	= [
		'member_max_user'	=> [
			'filter'	=> \FILTER_VALIDATE_INT,
			'options'	=> [
				'min_range'	=> 1,
				'max_range'	=> 255,
				'default'	=> \MEMBER_MAX_USER
			]
		],
		
		'member_min_user'	=> [
			'filter'	=> \FILTER_VALIDATE_INT,
			'options'	=> [
				'min_range'	=> 1,
				'max_range'	=> 255,
				'default'	=> \MEMBER_MIN_USER
			]
		],
		
		'member_min_pass'	=> [
			'filter'	=> \FILTER_VALIDATE_INT,
			'options'	=> [
				'min_range'	=> 5,
				'max_range'	=> 255,
				'default'	=> \MEMBER_MIN_PASS
			]
		],
		
		'member_login_delay'	=> [
			'filter'	=> \FILTER_VALIDATE_INT,
			'options'	=> [
				'min_range'	=> 5,
				'max_range'	=> 7200,
				'default'	=> \MEMBER_LOGIN_DELAY
			]
		],
		
		'member_attempts'	=> [
			'filter'	=> \FILTER_VALIDATE_INT,
			'options'	=> [
				'min_range'	=> 1,
				'max_range'	=> 20,
				'default'	=> \MEMBER_ATTEMPTS
			]
		],
		
		'member_blacklist'	=> 
			'filter'	=> \FILTER_CALLBACK,
			'flags'		=> \FILTER_REQUIRE_ARRAY,
			'options'	=> 'memberPrefilter'
		]
	];
	
	return 
	\array_merge( $hook, \filter_var_array( $params, $filter ) );
}

/**
 *  Membership function routes
 */
function addMemberRoutes( string $event, array $hook, array $params ) {
	return 
	[
	
	/**
	 *  Membership routes
	 */
	[ 'get', 'users/:all',				'memberprofile' ],
	
	[ 'get', 'login',				'memberlogin' ],
	[ 'get', 'login/:all',				'memberloginparams' ],
	[ 'post', 'login',				'memberloginsent' ],
	
	[ 'get', 'register',				'memberregister' ],
	[ 'get', 'register/:all',			'memberregisterparams' ],
	[ 'post', 'register',				'memberregistersent' ],
	
	[ 'get', 'logout',				'memberlogout' ],
	[ 'post', 'logout',				'memberlogout' ]
	
	]
}


// Member events

// Check configuration
hook( [ 'checkconfig',		'checkMemberConfig' ] );

// Member routes
hook( [ 'initroutes',		'addMemberRoutes' ] );

// Handlers
hook( [ 'dbcreated',		'memberDBCreated' ] );

// Login routes
hook( [ 'memberlogin',		'memberLoginRoute' ] );
hook( [ 'memberloginparams',	'memberLoginRoute' ] );
hook( [ 'memberloginsent',	'memberLoginProcess' ] );

hook( [ 'memberregister',	'memberRegisterRoute' ] );
hook( [ 'memberregisterparams',	'memberRegisterRoute' ] );
hook( [ 'memberloginsent',	'memberRegisterProcess' ] );



