use eg_test_site;
alter table program add column saveCIToDatabase TINYINT(1) DEFAULT 0;
update program set saveCIToDatabase=1 WHERE programid=1;


