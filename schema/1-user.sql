-- drop table user_permissions;
-- drop table user;

create table if not exists user
(
    id              bigint auto_increment primary key,
    username        text                                 not null comment 'For login purposes',
    firstName       text                                 null,
    lastName        text                                 null,
    email           text                                 not null,
    password        text                                 not null comment 'Password must be encrypted before',
    recoverPassword text     default ''                  not null comment 'Password must be encrypted before',
    jobTitle		text     default ''                  not null,
    created_at      datetime default current_timestamp() not null,
    updated_at      datetime default current_timestamp() not null on update current_timestamp(),
    constraint User_email_pk unique (email) using hash,
    constraint User_username_pk unique (username) using hash
);

create index user_email_index on user(email);

create index user_username_index on user(username);

insert into user(username, firstName, lastName, email, password, jobTitle)
VALUES ('admin', 'Admin', 'God', 'admin@exemple.com',
		'$2y$10$WwHzl9gP1IvZ3lQvgFLaIenm41U2pUZNhGs9dyz4Uo6/gJ2NYUoXK',
		'God (at least for this site)');

create table if not exists user_permissions
(
    permission text                                 not null,
    userid     bigint                               null,
    created_at datetime default current_timestamp() not null,
    updated_at datetime default current_timestamp() not null on update current_timestamp(),
    constraint user_permissions_id_userid_pk unique (permission, userid) using hash,
    constraint user_permissions_user_id_fk foreign key (userid) references user (id)
);

insert into user_permissions(permission, userid)
values ('ADMIN', 1);

