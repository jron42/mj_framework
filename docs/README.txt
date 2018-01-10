
file: docs/README.txt

General

The main app file for executing a response is the file: frame/docroot/myapp.php
It is possible to have as many of these as you see fit for logically breaking up your system.

All class definitions must reside in a file by the name <classname>.php

Javascript will be automatically included for both the class and the callback. These files should reside in frame/docroot/js and be named in the format:
  js/page_<cmd>.js
  js/class_<className>.js

For examples on how to set up response classes please look at the following files. I would copy one of these and use it as the base for coding new response handlers.
  frame/docroot/inc/HelloHtml.php
  frame/docroot/inc/HelloJson.php

In both of these files the method that does all the work is the execute() function. 
  With HelloHtml.php you will want to put your final markup into the $this->htmlcode variable which is then used later when the dump() method is called. The html code in $this->htmlcode will be dropped into a hole in the framework for output to the browser.

  With HelloJson.php you will want to put your final output data into the $this->data['data'] variable. Upon dump() this data wil simply be translated into json output and set to the caller. It is prefered that your output json is structured as can be seen in the example with top level data items of "errmsg" and "data". This allows a consistent way to return errors to the calling application. $this->data['data'] can be of singular or mixed values. (This is not required however it is prefered please.) If there is no error than $this->data['errmsg'] should be == "". If there is an error it sohould contain an error presentable to the user (unless you want to perform some more meaningful translation to the end user (I have not found this necessary)).
    

---------------------------------
Configuration:

There are two main sections of configuration you will be most interested in and will be discussed below. 
  Database and Application configuration. 

Applications are configured in by adding the cmd form variable and its handler class to the CmdHandlers section in configuration. Upon execution these class files will be included and the class itself instantiated.

[CmdHandlers]
# cmd         = 'class name'
  html_hello  = 'HelloHtml'
  json_hello  = 'HelloJson'

The above configuration would expect URL's similar to the following:
  http://frame.localhost/myapp.php?cmd=html_hello
  http://frame.localhost/myapp.php?cmd=json_hello

You have the ability to define configure (at a minimum) for the class and the callback. You can also inherit and override configuration if you so desire. In the exmaples below the Json call inherits from and overrides class level values.

[class.HelloHtml]
  hi_string = 'Hello from config for class.HelloHtml'

[class.HelloJson]
  hi_string = 'Hello from config for class.HelloJson'

[cmd.html_hello]
  hi_string = 'Hello from config for cmd.html_hello'

[cmd.json_hello : class.HelloJson]
  hi_string = 'Hello from config for cmd.json_hello'


Note: The "general" section needs to be made workflow supporting as is the "db" section. This will be donw ASAP.









