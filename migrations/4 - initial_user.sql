



USE `eg_db`;

INSERT INTO `user` (`email`, `password`) values (`admin@admin.com`, ``);

INSERT INTO `userinfo` (`firstName`,
                        `userlevel`,
                        `lastName`,
                        `petitionHeader`,
                        `pabarid`)
            values     ('admin',
                        1,
                        'admin',
                        'n/a',
                        'n/a',
                        0);
