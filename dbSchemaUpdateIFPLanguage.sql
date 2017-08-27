# To be run once to update a database to have the new ifpLanguage field.  
# if you run with -f at the mysql commandline, will ignore errors.  This is helpful if you have to 
# run the script a second time for some reason.

ALTER TABLE program ADD COLUMN ifpLanguage text;
UPDATE program SET ifpLanguage=CONCAT(programName, ' is a non-profit legal services organization that provides free legal assistance to low-income individuals.  I, attorney for the petitioner, certify that petitioner meets the financial eligibility standards for representation by ', programName, ' and that I am providing free legal service to petitioner.') WHERE ifp=1;