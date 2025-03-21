Monolog Extension for Magento 1/OpenMage
===========

A Magento Extension which provides a custom writer model to transparently use 
Monolog as logging library.

The following Monolog's Handlers are supported at the moment:

* `StreamHandler` - writes to file
* `NativeMailHandler` - sends each log entry via email
* `NewRelicHandler` - logs in NewRelic app

Installation
------------

```
composer require aleron75/magemonolog
```

**Common tasks**

After installation:

* if you have cache enabled, disable or refresh it;
* if you have compilation enabled, disable it or recompile the code base.

Usage example
-------------
Once installed, the module automatically replaces the default Magento Log Writer
with Monolog's StreamHandler.

This is obtained through the following config node in `config.xml`:

    <config>
        <global>
            <log>
                <core>
                    <writer_model>Aleron75_Magemonolog_Model_Logwriter</writer_model>
                </core>
            </log>
        </global>
    </config>

which instructs Magento to use a custom log writer class when logging via the
`Mage::log()` native static function.

The configured `Aleron75_Magemonolog_Model_Logwriter` class is a wrapper for
Monolog and allows to use Monolog's Handlers.

Monolog's Handlers are configured in the `config.xml` through the config node `magemonolog/handlers`.  
It is assumed you know Monolog's Handlers to understand the meaning of `params`
node.

Multiple Log Handlers can be activated at the same time with different log
filter level; this way, for example, it is possible to log any message into a
file and only serious errors via e-mail.

You can also use Monolog's Formatters like shown below:

    <config>
        <default>
            <magemonolog>
                <handlers>
                    <StreamHandler>
                        <active>1</active>
                        <class>Aleron75_Magemonolog_Model_HandlerWrapper_StreamHandler</class>
                        <params>
                            <stream>%channel%.log</stream>
                            <level>DEBUG</level>
                            <bubble>true</bubble>
                            <filePermission>null</filePermission>
                            <useLocking>false</useLocking>
                        </params>
                        <formatter>
                            <class>Monolog\Formatter\LogstashFormatter</class>
                            <args>
                                <applicationName><![CDATA[MyAppName]]></applicationName>
                                <systemName><![CDATA[]]></systemName>
                                <extraKey><![CDATA[]]></extraKey>
                                <contextKey><![CDATA[]]></contextKey>
                            </args>
                        </formatter>
                    </StreamHandler>
                </handlers>
            </magemonolog>
        </default>
    </config>

The `<args>` tag should contain proper Formatter's contructor arguments. Arguments' tag name is not important, values
are passed to Formatter's constructor in the exact order the constructor requires them. You should consult Formatter's
constructor signature to know which are its arguments, their meaning and their order.

Closing words
-------------
Any feedback is appreciated.

This extension is published under the [Open Software License (OSL 3.0)](http://opensource.org/licenses/OSL-3.0).

Any contribution or feedback is extremely appreciated.
