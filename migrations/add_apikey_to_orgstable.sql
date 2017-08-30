use eg_test_site;
alter table program add column apiKey BLOB;
update program set apiKey = PASSWORD(RAND());


