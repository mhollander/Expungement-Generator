## ARCHIVED
This project was archived on 8/29/2018 as Nate Vogel forked the EG and created a version that is containerized using docker.  Because some of the modifications that we made on Nate's fork required changes that affected the overall configuration of the project, including how we connect to databsaes and what the database schema looks like, we had to keep the project's separate.  All of the active development is happening there.  Goodbye, EG.  

You can find Nate's fork here (https://github.com/NateV/Expungement-Generator).

## Synopsis

The ExpungementGenerator is a web based utility that reads the PDF criminal record docket sheets generated by the Adminstrative Office of Pennsylvania Courts and determines whether a given arrest (or set of arrests) is able to be expungemed.  If an arrest is able to be expunged, the EG generates an odt expungement petition, order, and IFP petition if applicable.

This project was created by Michael Hollander of Community Legal Services of Philadelphia in 2011 and has been continually refined since then.  It is used by CLS, the Pennsylvania Lawyers for Social Equity, Legal Aid of Southeast Pennsylvania, and a number of other programs throughout PA.

## Installation and Dependencies

The vast majority of the program is written in PHP and has been tested on PHP 5.5.12.  It uses a mysql database (tested on 5.6).  

There are three  external dependencies, one of which is bundled with this code in git.
* PHPWORD (https://github.com/PHPOffice/PHPWord) - a library that allows you to make template DOCX files and then use PHP to modify variables within the templates and generate DOCX files.  I use version .13.
I had to modify a line of code in TemplateProcessor.php to allow for multiline edits: 
in setValueForParts, I added $replace = preg_replace('~\R~u', '</w:t><w:br/><w:t>', $replace); just after the line that sets UTF8 encoding.
The modified TemplateProcessor.php file is in this repository.  You can replace the one in PHPWord

* pdftotext - This is available on both windows and linux systems.  This can be found in the poppler-utils package on linux.

* casperscraping - https://github.com/CLSPhila/casperscraping - admitedly a bad name.  This is a library that uses casper to programatically scrape CPCMS.  CPCMS is a very javascript heavy website and was impossible to scrape without casper.

* sendgrip-php - (https://github.com/sendgrid/sendgrid-php). This is just for mailing the lookup files to legalserver.  If you don't want to use the lookup feature or don't want to allow emailing, don't worry about this.  You also want to play with lookup and mail*php to remove the mailing.  Uses sendgrid to accomplish mailing, although you could use any way to send email.

You can install the template database using database.sql as the structure.

## Motivation

Expungements are the most effective way to help an individual with a criminal record to find employment.  It is near impossible to find decent work if you have a record that is visible to the world.  Expungements are both time consuming and require the attention of a lawyer to properly prepare.  But they are also very boring for lawyers to prepare.  In an organization like CLS where we prepare thousands of expungement petitions a year, it is critical to speed up the process and remove much of the rote work from the lawyers working on expungements.  The EG does both of those things.  I am currently working on EG 2.0, which will be more accessible to non-lawyer petitioners.

## Contributors

If you are interested in contributing, please email mhollander@clsphila.org.

## License
Copyright Community Legal Services 2011-2018
