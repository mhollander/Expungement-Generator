# To be run once to update a database to have the new apiKey field.  
# if you run with -f at the mysql commandline, will ignore errors.  This is helpful if you have to 
# run the script a second time for some reason.

ALTER TABLE program ADD COLUMN apiKey varchar(255);

UPDATE program SET apiKey = PASSWORD(RAND()) WHERE apiKey = "" OR apiKey IS NULL;