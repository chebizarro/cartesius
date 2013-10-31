// avoid accidental global variable declarations
"use strict";

//requirejs configurations
require.config({
	/*
	 * Let's define short alias for commonly used AMD libraries and name-spaces. Using
	 * these alias, we do not need to specify lengthy paths, when referring a child
	 * files. We will 'import' these scripts, using the alias, later in our application.
	 */
    paths : {
        // requirejs plugins in use
        text : '/lib/require/text',
        i18n : '/lib/require/i18n',
        path : '/lib/require/path',
        // namespace that aggregate core classes that are in frequent use
        Boiler : './core/_boiler_',
        Q : '/lib/breeze/Scripts/q',
        breeze: '/lib/breeze/Scripts/breeze'
    },
    shim: {
        'breeze': { deps: ['Q'], 'exports': 'breeze' }
    },
    priority: [ 'Q', 'breeze' ]

});

/*
 * This is the main entry to the application, this script is called from the main HTML file.
 *
 * We use requirejs for writing modular JavaScript. The 'require' function below
 * behaves just like 'import' in PHP or 'using' in .NET. You may define the
 * relative paths or alias defined above you wish to import.
 *
 * You may note here, third party libraries such as jQuery, Underscore are not imported with
 * requirejs, but defined on the index.html. This is by design as not all thirdparty libs are AMD complient.
 *
 */
define(function(require) {

    /*
     * Let's import all dependencies as variables of this script file.
     *
     * Note: when we define the variables, we use PascalCase for namespaces ('Boiler' in the case) and classes,
     * whereas object instances ('settings' and 'modules') are represented with camelCase variable names.
     */
    var application = require('./app/application');
    application.initialize();
});

