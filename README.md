## EG-Docker Version Notes

This version of the Expungement Generator has a few minor modifications that make it easier to containerize. See the repository NateV/eg-docker for the docker-compose and Dockerfile that create Docker containers.

This version of the EG should be rebased every so often to make sure it stays up to date with the changes to the EG master branch.

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

* pdftotext - This is available on both windows and linux systems.  On windows, I use 3.03.  On linux I use 3.03.  It is important to use these versions even though they may not be the most up to date.  There was a time when I updated pdftotext on my linux machine and the EG broke.  It seems that different versions of this program parse complex pdfs differently.

* casperscraping - https://bitbucket.org/account/signin/?next=/nate_vogel/casperscraping - admitedly a bad name.  This is a library that uses casper to programatically scrape CPCMS.  CPCMS is a very javascript heavy website and was impossible to scrape without casper.

* sendgrip-php - (https://github.com/sendgrid/sendgrid-php). This is just for mailing the lookup files to legalserver.  If you don't want to use the lookup feature or don't want to allow emailing, don't worry about this.  You also want to play with lookup and mail*php to remove the mailing.  Uses sendgrid to accomplish mailing, although you could use any way to send email.

You can install the template database using database.sql as the structure.

## Motivation

Expungements are the most effective way to help an individual with a criminal record to find employment.  It is near impossible to find decent work if you have a record that is visible to the world.  Expungements are both time consuming and require the attention of a lawyer to properly prepare.  But they are also very boring for lawyers to prepare.  In an organization like CLS where we prepare thousands of expungement petitions a year, it is critical to speed up the process and remove much of the rote work from the lawyers working on expungements.  The EG does both of those things.  I am currently working on EG 2.0, which will be more accessible to non-lawyer petitioners.

## Contributors

If you are interested in contributing, please email mhollander@clsphila.org.

## License
Copyright Community Legal Services 2011-2016
