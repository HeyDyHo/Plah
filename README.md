# Plah
PHP little app helper (Plah) is a small library with some classes that can be used as addons
for PHP micro frameworks like the famous [Slim Framework](http://www.slimframework.com/).
For example it has a Config class that uses text files for configuration options or a Language class
that can be used for multi-language setups. A MongoModel base class is also included that makes it
easy to use [MongoDB](http://www.mongodb.org/) for your models instead of or in addition to
[MySQL](https://www.mysql.com/).

## Installation
Use [Composer](https://getcomposer.org/) to get Plah into your project. This will make autoloading easy.

    {
        "require": {
            "heydyho/plah": "~2.0"
        }
    }

You can also install Plah manually by downloading the ZIP file of the current master branch.
You need to register the Plah namespace with your projects autoloader or you have to
manually require/include the necessary files. This is not the recommended way because
some of the classes depend on each other and rely on an autoloader.

## Usage
Because Plah is a toolset there is not one single entry point, you can use only parts of it
or everything. Each part of Plah has it's own configuration options (if necessary). They are
initialized statically with a `::config()` function that gets an array of config options. For
example:

    \Plah\Config::config(array(
        'dir' => __DIR__ . '/../myconfig',
        'file_default' => 'myconfig-default',
        'file_local' => 'myconfig-local'
    ));

Most of the time you will use this in your projects `index.php` file or some kind of init file/class.
You don't need to set all possible options, just those for the parts of Plah that you plan to use. You
even don't need to set all options for one part, only those that you want to be different from the
defaults. For example:

    \Plah\Config::config(array(
        'dir' => __DIR__ . '/../myconfig'
    ));

This will change the config directory only and leave the default file names.

## Components
### Config
The Config class can be used to put config options to one single text file. The following format
is used for a config file:

    ; Mailer settings
    mail.host = "mail.example.com"
    mail.user = "myuser@example.com"
    mail.password = "mypassword"

And this is the way how to get a config option:

    \Plah\Config::getInstance()->get('mail.host');
    
    //You can also use a default value if you are not sure if a config value is set
    \Plah\Config::getInstance()->get('mail.host', 'mail.example2.com');
    
    //Another way
    $config = new \Plah\Config();
    $config->get('mail.host');
    
    //Get all config items as array
    $config = \Plah\Config::getInstance()->getAll();
    echo $config['mail.host'];

There are two files, a default config and a local config. Options in the local config file overwrite
options in the default config file. This feature can be used to have special options in your development
setup, like other passwords or something like that. Keep in mind: Add the local config file to your
`.gitignore` file to keep it away from your repository.
By default the files are named `config-default.ini` and `config-local.ini`. The names can be changed by the
corresponding settings, the .ini file extension is hard coded and needs to be present.

**Settings:**

    \Plah\Config::config(array(
        'dir' => '../config',
        'file_default' => 'config-default',
        'file_local' => 'config-local'
    ));

### Platform
The Platform class can be used as an addition or instead of the Config class. Basically the workflow
is the same. There are two files, `platform-default.ini` and `platform-local.ini` (by default), but you
can add additional files with platform specific settings. So, for example you have two URLs for your
project, one should be used for the english version, one for the german version, where default for
new (unconfigured) URLs should be english. You can have files like this:

    //platform-default.ini
    language = "en"
    
    //myplatform.com.ini
    language = "en"
    
    //myplatform.de.ini
    language = "de"

The code would look something like this:

    //Set the platform
    $hostname = my_function_for_getting_the_hostname();
    \Plah\Platform::getInstance()->set($hostname);
    
    //Get an option
    \Plah\Platform::getInstance()->get('language');
    
    //Like with the Config class you can also use a default value
    \Plah\Platform::getInstance()->get('language', 'en');
    
    //Another way
    $platform = new \Plah\Platform();
    $platform->set($hostname);
    $platform->get('language');
    
    //Get all platform items as array
    $platform = \Plah\Platform::getInstance()->getAll();
    echo $platform['language'];

This loads the `platform-default.ini` file, additionaly the `platform-local.ini` file if present and the
`.ini` file that fits to the hostname (the .ini extension must not be present in $hostname, it's added automatically).
Another use case may be two different config files in parallel. This can be done like that:

    //myconfig1.ini
    language = "en"
    
    //myconfig2.ini
    language = "de"
    
    $config1 = new \Plah\Platform();
    $config2 = new \Plah\Platform();
    $config1->set('myconfig1');
    $config2->set('myconfig2');
    $config1->get('language');  //gives you en
    $config2->get('language');  //gives you de

If you like, you can combine the Config and the Platform class to make it possible to overwrite platform
specific settings at once via the global config file. For example to switch of a feature on all platforms
without having to change the setting in each platform config file.

    //myplatform1.ini
    userupload = 1
    
    //myplatform2.ini
    userupload = 0
    
    //myplatform3.ini
    userupload = 1
    
    //The code to get the userupload setting would look like this
    \Plah\Config::getInstance()->get('userupload', \Plah\Platform::getInstance()->get('userupload'));
    
    //To switch of the userupload for all platforms just add it to your config-default.ini (not the platform-default.ini)
    userupload = 0

In this example the userupload setting is requested via the main config file, as fallback the platform config
is used. This means as long as no userupload option is present in the main config file, the platform setting
is used, as soon as the option is added to `config-default.ini` all platform settings are overwritten. Keep in mind:
the `platform-default.ini` file can not overwrite the platform specific files, it's only used as a basis
where an option in a platform specific file is missing.

**Settings:**

    \Plah\Platform::config(array(
        'dir' => '../platform',
        'file_default' => 'platform-default',
        'file_local' => 'platform-local'
    ));

### Language
The Language class can be used to work with multi-language setups. It works with text files like the
Config and Platform classes, that's an alternative way to the well-known but somethimes kind of complicated
`Gettext` implementations that use pre-compiled files.

    //en.ini
    main.hello = "hello"
    
    //de.ini
    main.hello = "hallo"
    
    \Plah\Language::getInstance()->set('en');
    echo \Plah\Language::getInstance()->get('main.hello');  //hello
    
    \Plah\Language::getInstance()->set('de');
    echo \Plah\Language::getInstance()->get('main.hello');  //hallo
    
    //Like with the Config class you can also use a default value
    echo \Plah\Language::getInstance()->get('main.hello', 'hello');
    
    //Another way
    $language_en = new \Plah\Language();
    $language_en->set('en');
    $language_en->get('main.hello');  //hello
    
    $language_de = new \Plah\Language();
    $language_de->set('de');
    $language_de->get('main.hello');  //hallo
    
    //Get all language items as array
    $language = \Plah\Language::getInstance()->getAll();
    echo $language['main.hello'];

There is not much magic about it. The option `file_default` let's you set a default language file.
This file is always loaded. So if en is your main language and the de file is missing some texts, the
texts of the en file will be used.
You sould select the language in your projects `index.php`  or some kind of init file/class maybe depending
on the browser language or the URL like in the platform example. Keep in mind: The `file_default`
option should not be used as your platform default language setting, it's just a basic language file
that's used for later translations.

**Settings:**

    \Plah\Language::config(array(
        'dir' => '../language',
        'file_default' => 'en'
    ));

### MongoModel
The MongoModel class can be used as base for your own models using MongoDB as a storage backend. A basic model
would look like this:

    class User extends \Plah\MongoModel
    {
        //Basic database settings
        protected static $_db = 'mydatabase';
        protected static $_collection = 'user';
        protected static $_key = '_id';

        //Model properties
        public $_id = null;
        public $email = '';
        public $password = '';
        public $first_name = '';
        public $last_name = '';

        public static function ensureIndexes()
        {
            self::getCollection()->ensureIndex(array('email' => 1), array('background' => true));
        }
    }

`$_db` sets the database and `$_collection` the MongoDB collection (table) that is used for storing the data. These
properties must be set to make the model work. `$_key` can be used to set something like a primary key, usage
can be found in the examples below. The public properties of the model are the fields that are written to the
collection. The `ensureIndexes()` function defines the necessary indexes for your model. You have to run it when the
indexes change. Best practice is to have one file that runs the `ensureIndexes()` functions of all your models.

Here are some examples of how to use your models:

    //Create a new user, set some values and save it
    $user = new User();
    $user->email = 'myaddress@example.com';
    $user->password = 'myunencryptedtopsecretpassword';
    $user->first_name = 'John';
    $user->last_name = 'Doe';
    $user-save();
    
    //Another way to set values
    $user = new User();
    $user->set('email', 'myaddress@example.com');
    
    //Find a user with the email myaddress@example.com, change it and save it
    $user = User::getInstance()->findOne(array('email' => 'myaddress@example.com'));
    $user->email = 'mynewaddress@example.com';
    $user-save();
    
    //Find a user by the primary key (which is a MongoId in this case), change it and save it
    try {
        $user = new User(new MongoId('555ef27165689edd457b23c7'));
        $user->first_name = 'Jane';
        $user-save();
    } catch (\Exception $e) {
    }
    
    //Find all users with the first name John, walk over the results in a loop and show the email addresses
    $users = User::getInstance()->find(array('first_name' => 'John'));
    foreach ($users as $user) {
        echo $user->email;
    }
    
    //Another way to get values
    echo $user->get('email');
    echo $user->get('email', 'unknown');  //Gets 'unknown' as default value if the email property doesn't exist
    
    //Find a user with the email myaddress@example.com and remove it from the collection
    $user = User::getInstance()->findOne(array('email' => 'myaddress@example.com'));
    $user-remove();
    
    //Get a PHP MongoCollection object to use all possible functions
    $user_collection = User::getCollection();

The functions `findOne()`, `find()`, `save()` and `remove()` are wrappers around the functions with the same
names provided by a `MongoCollection` object. `findOne()` returns `null` if nothing was found just like the
original function and an instance of your model's class if something was found. The original function returns
`null` or an array. `find()` returns an empty array or an array of your model's instances, where the
original function returns an empty array or a `MongoCursor` object which returns arrays in iterations.

The `find()` function has some additional parameters for sorting and limiting the results. Here is an example:

    //Find users with the first name John, order the results descending by last name and limit the results to the first 10 matching entries
    $users = User::getInstance()->find(array('first_name' => 'John'), array(), array('last_name' => -1), 0, 10);

In addition to sorting and limiting the results of `find()` you can get the number of found elements without
and with the limits. This can be useful for pagination. Look at the example:

    //Find users with the first name John, order the results descending by last name and limit the results to the first 10 matching entries
    $users = User::getInstance()->find(array('first_name' => 'John'), array(), array('last_name' => -1), 0, 10, $count, $found);
    echo $count;  //This will be the number of all elements with the first name John, maybe 0, 5, 10 or even 100
    echo $found;  //This will be 10 or less because the result set is limited to max 10

The `count()` function is a wrapper around the `MongoCursor` `count()` function, but accepts a query parameter as
well as a skip and a limit parameter just like the `find()` function. It returns the number of datasets found by
the query, limited by skip and limit.
Keep in mind: This is only useful in cases where only the number of datasets matters, in all other cases the
`$count` and `$found` parameters of the `find()` function will do a better job because no second query is necessary
to get the datasets. Here is an example for a count only query:

    //Count the users with first name John
    echo User::getInstance()->count(array('first_name' => 'John'));

You can set some options for the MongoDB connection via the static `config()` function. Most of the options
should be clear, `auth_db` is the database that is used for authentication if user and password are set.

**Settings:**

    \Plah\MongoModel::config(array(
        'host' => 'localhost',
        'port' => '',
        'user' => '',
        'password' => '',
        'auth_db' => ''
    ));

### MongoAutoIncrement
The MongoAutoIncrement class can be used to get auto incremented IDs like SQL databases use them.
MongoAutoIncrement needs a database and a collection, both default to `autoincrement` and can be changed.
Internally a document with a specific key is created, every time you try to get the next auto incement
value for this key a sequence number is incremented by one. Here is a short usage example:

    echo \Plah\MongoAutoIncrement::getInstance()->getNext('user');  //1
    echo \Plah\MongoAutoIncrement::getInstance()->getNext('user');  //2
    echo \Plah\MongoAutoIncrement::getInstance()->getNext('user');  //3
    
    echo \Plah\MongoAutoIncrement::getInstance()->getNext('event');  //1
    echo \Plah\MongoAutoIncrement::getInstance()->getNext('event');  //2
    echo \Plah\MongoAutoIncrement::getInstance()->getNext('user');  //4
    echo \Plah\MongoAutoIncrement::getInstance()->getNext('event');  //3
    
    //Another way
    $auto_increment = new \Plah\MongoAutoIncrement();
    echo $auto_increment->getNext('user');  //5
    echo $auto_increment->getNext('event');  //4

Optionally you can set an init value, to start the counter with this value in case this is the first request
for the key:

    echo \Plah\MongoAutoIncrement::getInstance()->getNext('user', 1000);  //1000
    
    echo \Plah\MongoAutoIncrement::getInstance()->getNext('event');  //1
    echo \Plah\MongoAutoIncrement::getInstance()->getNext('event', 1000);  //2 <- Not the first request for 'event', the init value is ignored

You should run the following line one time to create the necessary indexes for the collection.

    \Plah\MongoAutoIncrement::ensureIndexes();

The MongoAutoIncrement class extends the MongoModel class, so make sure you configure MongoModel properly
before you use MongoAutoIncrement.

**Settings:**

    \Plah\MongoAutoIncrement::config(array(
        'db' => 'autoincrement',
        'collection' => 'autoincrement'
    ));

### MongoSession
The MongoSession class can be used for a MongoDB based session management. The class doesn't use or rely
on PHP sessions in any way, nevertheless it uses the same workflow. First a session must be startet. If
the visitor of a page already has a valid session ID the corresponding data is loaded from a MongoDB
collection, else a new Session ID is generated. To "remember" the visitor's session ID cookies are used.
A new session is not automatically written to the database to prevent flooding the database with empty
sessions (for example in the case of a DDoS attack). The session is saved when the first value is set and
each time a new value is set via the `set()` function. The database and collection for the sessions can be
set via config options as well as the cookie name and expire time. The expire time can be 0 (which means
the cookie ends when the browser is closed), a unix timestamp or a string like '1 day'. Here is a usage
example:

    //Configure the sessions to be valid for 1 day
    \Plah\MongoSession::config(array(
        'expires' => '1 day'
    ));
    
    //Start the session
    \Plah\MongoSession::getInstance()->start();
    
    //Set a session value
    \Plah\MongoSession::getInstance()->set('test', 'testvalue');
    
    //Get a session value
    echo \Plah\MongoSession::getInstance()->get('test');  //shows testvalue
    
    //Get a session value with a default fallback value
    echo \Plah\MongoSession::getInstance()->get('test2', 'testvalue2');  //shows testvalue2 because no other value was set for test2
    
    //Another way
    //This is not recommended because you have to make the $session variable globally available or call start() every time you create a new instance of the MongoSession class
    $session = new \Plah\MongoSession();
    $session->start();
    $session->set('test', 'testvalue');
    echo $session->get('test');

The MongoSession class extends the MongoModel class, so make sure you configure MongoModel properly
before you use MongoSession. The `set()` function of the MongoModel class is overwritten and modified
to save the object to the database each time a property is changed. This is to keep session data
consistent even if a request cannot be completely processed.

**Settings:**

    \Plah\MongoSession::config(array(
        'db' => 'session',
        'collection' => 'session',
        'name' => 'session',
        'expires' => 0
    ));

### Session
The Session class is a wrapper around the `$_SESSION` super global variable. You can use it to get, set
or delete values of `$_SESSION`. Session storage itself is not done by the Session class. Usage is as follows:

    //Get the session language
    $language = \Plah\Session::getInstance()->get('language');
    
    //Like with the Config class you can also use a default value
    $language = \Plah\Session::getInstance()->get('language', 'en');
    
    //Set the session language to en
    \Plah\Session::getInstance()->set('language', 'en');
    
    //Delete the language value
    \Plah\Session::getInstance()->delete('language');
    
    //Another way
    $session = new \Plah\Session();
    $language = $session->get('language');
    $session->set('language', 'en');
    $session->delete('language');

### Pagination
The Pagination class does the necessary calculations to show a pagination and returns the relevant
numbers as an array. It needs four arguments: The total number of data, the number of entries per
page, the currently active page and the desired number of pages in the pagination. The returned array
will contain the first and the last page, previous and next page, pages to show, the active page,
the number of entries on the active page, the total number of entries and the start and end data of
the active page. Non of the numbers will be less than one to avoid division by zero problems. As well
no numbers less than one or non-int will be accepted for the calculations. Here is an example:

    //Calculate a pagination
    $total = 5;
    $entries = 2;
    $page = 1;
    $pages = 4;
    $pagination = \Plah\Pagination::getInstance()->get($total, $entries, $page, $pages);
    print_r($pagination);

    Array
    (
        [first] => 1
        [last] => 3
        [previous] => 1
        [next] => 2
        [pages] => Array
            (
                [0] => 1
                [1] => 2
                [2] => 3
            )
    
        [active] => 1
        [entries] => 2
        [total] => 5
        [start] => 1
        [end] => 2
    )

The example shows that even if 4 pages are desired the Pagination class calculates that only
the pages 1 to 3 are necessary, on page 4 won't be any data.

### Singleton
The Singleton class is mainly used internally by other Plah classes. As you can see in the examples
above you can get a static version of the classes with ::getInstance(). This is done by extending
the Singleton class. For example:

    MyClass
    {
        public function my_function()
        {
            echo "my_function";
        }
    }
    
    $myclass = new MyClass();
    $myclass->my_function();
    
    //By changing
    MyClass
    //to
    MyClass extends \Plah\Singleton
    //you can use your function like this
    MyClass::getInstance()->my_function();

Keep in mind: This is only useful for classes with no special settings that need no different instances.

### IniParser
The IniParser class is used by the Config, Platform and Language classes. It parses a text file in the format
key = "value" and gives back an associative array. It's mainly based on the PHP function `parse_ini_file()`,
except that it always returns an array, even if the file could not be parsed. If this is what you need for your
project you can use the IniParser like this:

    $myoptions = \Plah\IniParser::getInstance()->get('myfile.ini');
    
    //Another way
    $parser = new \Plah\IniParser();
    $myoptions = $parser->get('myfile.ini');
