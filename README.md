## Synopsis

The ExpungementGenerator is a web based utility that reads the PDF criminal record docket sheets generated by the Adminstrative Office of Pennsylvania Courts and determines whether a given arrest (or set of arrests) is able to be expungemed.  If an arrest is able to be expunged, the EG generates an odt expungement petition, order, and IFP petition if applicable.

This project was created by Michael Hollander of Community Legal Services of Philadelphia in 2011 and has been continually refined since then.  It is used by CLS, the Pennsylvania Lawyers for Social Equity, Legal Aid of Southeast Pennsylvania, and a number of other programs throughout PA.

## Installation and Dependencies

The vast majority of the program is written in PHP and has been tested on PHP 5.5.12.  It uses a mysql database (tested on 5.6).  

There are two external dependencies, one of which is bundled with this code in git.
* PHPWORD (https://github.com/PHPOffice/PHPWord) - a library that allows you to make template DOCX files and then use PHP to modify variables within the templates and generate DOCX files.  I use version .12.  
I had to modify a line of code in TemplateProcessor.php to allow for multiline edits: 
in setValueForParts, I added $replace = preg_replace('~\R~u', '</w:t><w:br/><w:t>', $replace); just after the line that sets UTF8 encoding.

* pdftotext - This is available on both windows and linux systems.  On windows, I use 3.03.  On linux I use .  It is important to use these versions even though they may not be the most up to date.  There was a time when I updated pdftotext on my linux machine and the EG broke.  It seems that different versions of this program parse complex pdfs differently.

You can install the template database using database.sql as the structure.

## Motivation

Expungements are the most effective way to help an individual with a criminal record to find employment.  It is near impossible to find decent work if you have a record that is visible to the world.  Expungements are both time consuming and require the attention of a lawyer to properly prepare.  But they are also very boring for lawyers to prepare.  In an organization like CLS where we prepare thousands of expungement petitions a year, it is critical to speed up the process and remove much of the rote work from the lawyers working on expungements.  The EG does both of those things.  I am currently working on EG 2.0, which will be more accessible to non-lawyer petitioners.

## Contributors

If you are interested in contributing, please email mhollander@clsphila.org.

## License
Copyright Community Legal Services 2011-2016