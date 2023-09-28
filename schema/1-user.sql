-- drop table if exists role_permission;
-- drop table if exists user_role;
-- drop table if exists role;
-- drop table if exists user;


create table if not exists role
(
    id              bigint auto_increment primary key,
    name            text                                 not null,
    created_at      datetime default current_timestamp() not null,
    updated_at      datetime default current_timestamp() not null on update current_timestamp()
);

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

create table if not exists user_role
(
    user_id bigint not null,
    role_id bigint not null,
    created_at      datetime default current_timestamp() not null,
    primary key (role_id, user_id),
    constraint user_role_user_id_fk foreign key (user_id) references user (id),
    constraint user_role_role_id_fk foreign key (role_id) references role (id)

);

create table if not exists role_permission
(
    role_id     bigint                                 not null,
    permission varchar(50)                            not null,
    enabled    boolean  default false,
    created_at datetime   default current_timestamp() not null,
    updated_at datetime   default current_timestamp() not null on update current_timestamp(),
    primary key (role_id, permission),
    constraint role_permissions_role_id_fk foreign key (role_id) references role (id)
);

insert into role(name)
values ('ADMIN'), ('GUEST'), ('READ_ONLY'), ('OPERATOR');

insert into user(id, username, firstName, lastName, email, password, jobTitle)
VALUES (1,'admin', 'Admin', 'God', '',
		'$2y$10$cRtLRozXvVgq3A8B06OQX.IBAqa7B0tFbuEjwyibQr3TYWN2kYJ0.',
		'God (at least for this site)');

insert into user_role(user_id, role_id)
values (1, 1);

insert into role_permission(permission, role_id,enabled)
values ('admin', 1, true);
insert into role_permission(permission, role_id,enabled)
values ('guest', 2, true);
insert into role_permission(permission, role_id,enabled)
values ('read_only', 3, true);

create index user_email_index       on user(email);
create index user_username_index    on user(username);