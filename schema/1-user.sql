create table if not exists slim.user (
	id              bigint auto_increment primary key,
	username        text                                 not null comment 'For login purposes',
	firstName       text                                 null,
	lastName        text                                 null,
	email           text                                 not null,
	password        text                                 not null comment 'Password must be encrypted before',
	recoverPassword text                                 null comment 'Password must be encrypted before',
	created_at      datetime default current_timestamp() not null,
	updated_at      datetime default current_timestamp() not null on update current_timestamp(),
	constraint User_email_pk unique (email) using hash,
	constraint User_username_pk unique (username) using hash
);

insert into slim.user(username, firstName, lastName, email, password)
VALUES ('admin', 'FirstName', 'LastName', 'admin@exemple.com',
		'$2y$10$WwHzl9gP1IvZ3lQvgFLaIenm41U2pUZNhGs9dyz4Uo6/gJ2NYUoXK');

create table if not exists slim.user_permissions (
	permission         text                                 not null,
	userid     bigint                               null,
	created_at datetime default current_timestamp() not null,
	updated_at datetime default current_timestamp() not null on update current_timestamp(),
	constraint user_permissions_id_userid_pk unique (permission, userid) using hash,
	constraint user_permissions_user_id_fk foreign key (userid) references slim.user(id)
);

insert into slim.user_permissions(permission, userid)
values ('ADMIN', 1);

