Simple database wrapper for PDO
===============================

This is a *very* simplistic database wrapper for PDO, with two goals:
simple usage and security.

First design goal: Simple usage!
--------------------------------

I did not use the Singleton pattern for this class, because Singletons
always involve unnecessarily much code and aren't that nice to use and read.
A typical query execution of a Singleton-based DB-class looks like this:
	$db = DB::getInstance();
	$db->query('SELECT ...');
	$db->exec('INSERT INTO ...');
Or, if it's only one query:
	DB::getInstance()->query('SELECT ...');
Now, I think this `getInstance()->` part of the code neither carries
further information, nor is useful in some way. Therefore, I simply left
this part out, resulting in:
	DB::query('SELECT ...');
	DB::exec('SELECT ...');
Much nicer, isn't it?

So, wonder which static methods you can use? All! All methods PDO implements.
I simply redirect all static calls to the PDO equivalents.

Second design goal: Secure!
---------------------------

Apart from this redirecting functionality this class offers two further methods:
`DB::q()` and `DB::x()`
These methods are shortcuts to `DB::query()` (q) and `DB::exec()` (x) with the difference of
something i called autoQuoting.
Again, let's start with a example:
	DB::q(
		'SELECT * FROM user WHERE lastAction = ? AND group = ?s AND points > ?i',
		'CURRENT_DATE', 'user', 7000 //        ^             ^^              ^^
	)
See those question marks? These are placeholders, which will be replaced with the arguments
passed after the query. There are several types of placeholders:
? simply inserts the argument, not performing any escaping
?s (from string) inserts the argument, performing string escaping, i.e. putting the argument in ' and applying `addslashes`
?i (from integer) inserts the argument, performing integer escaping, i.e. applying `intval`
Therefore the example code above may also be written like this:
	DB::query(
		"SELECT * FROM user WHERE lastAction = CURRENT_DATE AND group = 'user' AND points > 7000"
	)

Configuration
-------------

There are two versions of this class available, one for PHP 5.3
(DB.php) and one for PHP 5.2 (DB_forPHP52.php). The only difference
is, that the former uses `__callStatic` to redirect the static calls
to the PDO instance, the latter simply redefines all methods. (You may
obviously use the 5.2 version on PHP 5.3, it actually should be slightly
faster.)

So, to get going and use this class, you have to modify the
`DB::instance` method, which by default is defined like this:
	private static function instance() {
		if (self::$instance === null) {
			self::$instance = new PDO(
				'mysql:host='.DB_HOST.';dbname='.DB_NAME,
				DB_USER,
				DB_PASS,
				array(
					PDO::ATTR_PERSISTENT => true,
					PDO::MYSQL_ATTR_INIT_COMMAND => 'SET CHARACTER SET utf8',
				)
			);
			self::$instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		}
		
		return self::$instance;
	}
Replace the arguments of `new PDO()` as you wish.
Than, `require_once` the file and have fun using it!

Short reference
---------------
	class DB
	{
		// returns the database instance
		public static function instance()
		
		// redirects static calls to self::instance()
		public static function __callStatic($method, $args)
		
		// DB::query with autoquote
		// either used as DB::q('QUERY', param1, param2, ...)
		// or DB::q('QUERY', array(param1, param2, ...))
		public static function q()
		
		// DB::exec with autoquote
		// either used as DB::x('QUERY', param1, param2, ...)
		// or DB::x('QUERY', array(param1, param2, ...))
		public static function x()
		
		// autoQuote as described above
		public static function autoQuote($query, $args)
	}