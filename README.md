# Email Cloaking PlugIn  \(plg_email_cloaking\)

## Introduction

Email cloaking plugin that is compatible with Moyo's CCK. The CCK was developed by [Moyo Web Architects](http://moyoweb.nl).

Although this plugin works automatically, you may be interested in reading [Usage](#Usage) section below.

## What is Cloaking?

It is good practice to make sure that email addresses that appear on your website are obfuscated, or 'cloaked'. This means that they are readable by people, but not by bots that harvest email addresses from websites for spamming purposes. Email addresses are made un-readable to these bots by 'assembling' them via JavaScript when the page is loaded. Although they appear on your screen as a readable e-mail address, the actual email address itself never appears in the code.

Joomla 2.5 & 3.x has built-in email cloaking functionality that can be called within a component or module template, through the JHtml Class. In order to use that standard 
plug-in it has to be enabled in the Joomla back-end CMS; *Administration* \> *Extension Manager* \> *PlugIn Manager*. Doumentation for the 
standard [Content - Email Cloaking](https://docs.joomla.org/How_to_cloak_email_addresses) can further be found here.

## Requirements

   * Joomla 3.x .
   * Koowa 0.9 or 1.0 (as yet, Koowa 2 is not supported)
   * PHP 5.3.10 or better
   * Moyo's Content Creation Kit \(CCK\) Components

## Installation

### Composer

Installation is done through composer. In your `composer.json` file, you should add the following lines to the repositories
section:

from the local repository;

```json
{
    "name": "cta/com_email-cloaking",
    "type": "vcs",
    "url": "https://github.com/cta-int/com_email-cloaking.git"
}
```

The require section should contain the following line:

```json
    "cta/com_email-cloaking": "1.0.*",
```

Afterwards, one just needs to run the command `composer update` from the root of your Joomla project. This will 
effectively create a `composer.lock` file which will contain the collected dependencies and the hash codes for 
each latest release \(depending on the require section's format\) for each particular repo. Should installations 
problems occur due to a bad ordering of the dependencies, one may need to go into the lock file and manualy change 
the order of the components. Running `composer update` again will again cause a reordering of the lock file, beware of 
this factor when running an update. Thereafter, you can run the command `composer install`. 

If you have not setup an alias to use the command composer, then you will need to replace the word composer in the previous commands with the 
commands with `php composer.phar` followed by the desired action \(eg. update or install\).

### jsymlinker

Another option is to run the [jsymlink script](https://github.com/derjoachim/moyo-git-tools) in the root folder, available via the original Moyo developer, Joachim van de Haterd's repository, under 
the [Moyo Git Tools](https://github.com/derjoachim/moyo-git-tools).

#### License jsymlinker

The joomlatools/installer plugin is free and open-source software licensed under the [GPLv3 license](https://github.com/derjoachim/joomla-composer/blob/develop/gplv3-license).

## Usage

In order to use this email plug-in built for the CCK environment, one would likely wish to disable the Standard Joomla PlugIn and enable this
plug-in in the Joomla back-end CMS; *Administration* \> *Extension Manager* \> *PlugIn Manager*.

The plugin runs on its own hooking into the `onAfterDispatch` to build up the client-side javascript that will obscure the email addresses 
in both javascript, html, link contents. The obfuscation of the returned DOM to the client occurs in the `onAfterRender` function.

Unlike the built-in [JHTML Class](http://docs.joomla.org/Screen.plugins.edit.15#Content_-_Email_Cloaking) there is no built in toggle which can be placed in an extension to disable auto cloaking of email addresses on a pages like: 

`{emailcloak=off}`

within this similar plugin for the Content Creation Kit \(CCK\) Environment.
