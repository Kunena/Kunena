# Kunena 5.2 Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [5.2.6-dev]
### Fixed
* [#8216](https://github.com/Kunena/Kunena-Forum/issues/8216): Crypsisb3 & Crypsisb4 : The BBcode editor loads no toolbar (missing images in crypsisb4)
* [#8216](https://github.com/Kunena/Kunena-Forum/issues/8216): Crypsisb3 & Crypsisb4 : The BBcode editor loads no toolbar
* [#8214](https://github.com/Kunena/Kunena-Forum/issues/8214): Only with Crypsisb3 & Crypsisb4: It does not switch to another editor
* [#8211](https://github.com/Kunena/Kunena-Forum/issues/8211): Temporary rendering error at editor
* [#8208](https://github.com/Kunena/Kunena-Forum/issues/8208): Only with PHP 8 : Warning at bullet list
* [#8205](https://github.com/Kunena/Kunena-Forum/issues/8205): Only with PHP 8 : Warning: getimagesize - when protect attachment is on (apply the fix too in crypsisb3)
* [#8205](https://github.com/Kunena/Kunena-Forum/issues/8205): Only with PHP 8 : Warning: getimagesize - when protect attachment is on
* [#8204](https://github.com/Kunena/Kunena-Forum/issues/8204): Only with PHP 8 : Warning: Undefined array key at quoted text
* [#8166](https://github.com/Kunena/Kunena-Forum/issues/8166): [CKEditor] CKEditor bug using lists
* [#8170](https://github.com/Kunena/Kunena-Forum/issues/8170): Error when trying to view profile of banned user

### Added
* Reintroduce the editors available in Kunena 5.1.x to let users replace CKeditor

## [5.2.5]
### Fixed
* [#7997](https://github.com/Kunena/Kunena-Forum/issues/7997): Can't add an attachment to the message when Add Suffix to URL and rewrite url is enabled #7997
* Workaround on CKeditor to avoid to it fails completly when the start or end tag aren't terminated like : [b]Hello world![/b
* [#8074](https://github.com/Kunena/Kunena-Forum/issues/8074): Editing removes the subscription (Part 4)
* Exact forum post link is missing in search results
* [#8050](https://github.com/Kunena/Kunena-Forum/issues/8050): Kunena Discuss Error: Rendering Error in layout Topic/Item when topic layout is Threaded or Indented
* [#8033](https://github.com/Kunena/Kunena-Forum/issues/8033): Poll: Notice: Undefined variable: polllifespan - (if the Lifetime field is not filled)
* In backend, in templates list view before to do the request to get the list of premium templates it check if the method to do it are presents in the system
* Allowing ',' in font name in bbcode
* [#8020](https://github.com/Kunena/Kunena-Forum/issues/8020): Polls are not displayed Rendering Error in layout ...
* [#6023](https://github.com/Kunena/Kunena-Forum/issues/6023): Ban from forum does not work (part 2)
* Don't logout the user when do unban, related to the issue [#5956](https://github.com/Kunena/Kunena-Forum/issues/5956)
* When the poll has ended display a different string next to the poll life span
* Quote on messages done on previous versions not show the author user of the quote but the current user
* On crypsisb3 and crypsisb4, the poll icon is disabled on categories which have polls enabled which contains sub-categories
* On crypsisb3 and crypsisb4 when edit a poll the fields (poll title, options...) aren't filled
* On crypsisb3 and crypsisb4 some strings of poll dialog aren't here and the poll title label isn't translated
* [#7996](https://github.com/Kunena/Kunena-Forum/issues/7996): Impossible to choose birthdate with datepicker
* An username still appears on quote even on quote not related to a message
* When quote a message from someone which have a name or an username with more than one word, it displays only the first word
* When try to enabling some integration plugins it doesn't work
* [#5956](https://github.com/Kunena/Kunena-Forum/issues/5956): We need another message when someone try to enable not needed Kunena plugins

### Added
* Display poll end time if exist next to the poll title on results and vote layout

### Changed
* Update CKeditor to 4.16.1
* Set a bit smaller the poll life time
* Set the following date format for all datepicker : yyyy-mm-dd

## [5.2.4] - 2021-04-04
### Fixed
* Log all the actions into Php file during Kunena install, to have a way to find the error in case of fail
* Missing call for namespace Joomla\CMS\Language\Text in some templates files
* Improve display of quote in message and move quote in template
* When Altauserpoints plugin integration is enabled it don't follow Altauserpoints settings to display the name or the username
* When Easy profile plugin integration is enabled it don't follow Easy profile settings to display the name or the username
* [#7977](https://github.com/Kunena/Kunena-Forum/issues/7977): KunenaBbcodeLibrary::DoInstagram() must be an instance of Nbbc\BBCode, string given
* When Jomsocial plugin integration is enabled it don't follow Jomsocial settings to display the name or the username
* [#7945](https://github.com/Kunena/Kunena-Forum/issues/7945): The menu item Recent Topics shows either all or nothing
* Rendering Error in layout User/Ban/History: Call to a member function getName() on string in /libraries/kunena/layout/base.php on line 168
* Users always showing as "online" even when offline on the crypsisb4 template
* When integration with easysocial is enabled, get displayname setting from easysocial configuration to know what to display
* [#7954](https://github.com/Kunena/Kunena-Forum/issues/7954): Quote adds the message ID of the quoted post
* [#7955](https://github.com/Kunena/Kunena-Forum/issues/7955): [CKEditor]The editor not recognize our font size spec
* [#7947](https://github.com/Kunena/Kunena-Forum/issues/7947): Names not displayed when Kunena Profile is disabled (in the Kunena Integration plugin)

### Changed
* Update fontawesome to 5.15.3
* During install or update of Kunena the directories of CKeditor plugins not present anymore are deleted
* Typo in language file en-GB.com_kunena.templates.ini
* Add a new setting in templates to load fontawesome compatibility layer for version 4.x
* In KunenaUser class don't need to call in each method the config object because is called in contructor

### Removed
* Remove check on install on fileinfo and openssl which prevent some users to install it

## [5.2.3] - 2021-03-07
### Fixed
* [#7943](https://github.com/Kunena/Kunena-Forum/issues/7943): Variable escaped {KunenaFactory::getProfile() in quote
* When enabling Easysocial integration in Kunena the username of user has disppear
* [#7573](https://github.com/Kunena/Kunena-Forum/issues/7573): Subscription New Topics - notification is sent for each new message
* When enabling Jomsocial integration in Kunena the username of user has disppear
* When do a clean install of Kunena you aren't redirected to the install page
* [#7875](https://github.com/Kunena/Kunena-Forum/issues/7875): [CKEditor] The attachment button Insert All duplicates the content of the posts
* [#7930](https://github.com/Kunena/Kunena-Forum/issues/7930): Error 500 string to array conversion
* [#7859](https://github.com/Kunena/Kunena-Forum/issues/7859): Reason for editing is hidden
* [#7923](https://github.com/Kunena/Kunena-Forum/issues/7923): Poll in cat which contain subcategories aren't possible #7923
* [#7919](https://github.com/Kunena/Kunena-Forum/issues/7919): Trying to get property 'userid' of non-object profile.php
* [#7917](https://github.com/Kunena/Kunena-Forum/issues/7917): Trying to get property 'userid' of non-object profile.php
* [#7913](https://github.com/Kunena/Kunena-Forum/issues/7913): Topic Template:Call undefined method KunenaUser::getName()
* [#7869](https://github.com/Kunena/Kunena-Forum/issues/7869): Topic template - Options from the modal will not Implement
* [#7845](https://github.com/Kunena/Kunena-Forum/issues/7845): enabling CB integration plugin errors (Part 2)
* Update part to check Php extensions because was duplicated
* Not possible to display a tweet on a message with blue eagle
* Missing setting for custom config file in crypsisb3 and b4
* [#7857](https://github.com/Kunena/Kunena-Forum/issues/7857) : Crypsisb3 and b4 | The poll symbol not available category
* Check for ckeditor custom config file was wrong template (Part 2)
* Check for ckeditor custom config file was wrong template params
* Undefined variable: bigicon in /kunena/icons/icons.php line 1357
* Disable SpellChecker from Ckeditor to let the browser handle it
* Fix in case using parameters on CBForums plugin to sideprofile

### Added
* The changelog is a PHP file
* [#7845](https://github.com/Kunena/Kunena-Forum/issues/7845): enabling CB integration plugin errors
* Add check for php extensions during Kunena installation
* Add a template params to load a custom config file for ckeditor

### Changed
* [#7933](https://github.com/Kunena/Kunena-Forum/issues/7933): Type mismatch in libraries/bbcode/bbcode.php
* Update CKeditor to 4.16.0
* Avoid to put line return on icons in Ckeditor
* Update fontawesome to 5.15.2

### Removed
* Remove the button in template to access to manage categories from frontend, this feature isn't working (Part 2: forgot in crypsisb3)
* Remove the button in template to access to manage categories from frontend, this feature isn't working
* Remove uneeded plugins files for CKeditor

## [5.2.2] - 2021-01-13

## [5.2.1] - 2020-12-27

## [5.2.0] - 2020-12-09