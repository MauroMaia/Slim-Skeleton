create table if not exists slim.user (
	id         bigint auto_increment primary key,
	username   text                                 not null comment 'For login purposes',
	firstName  text                                 null,
	lastName   text                                 null,
	email      text                                 not null,
	password   text                                 not null comment 'Password must be encrypted before',
	created_at datetime default current_timestamp() not null,
	updated_at datetime default current_timestamp() not null on update current_timestamp(),
	constraint User_email_pk unique (email) using hash,
	constraint User_username_pk unique (username) using hash
);

insert into slim.user(username, firstName, lastName, email, password)
VALUES ('admin', 'FirstName', 'LastName', 'admin@exemple.com',
		'$2y$10$WwHzl9gP1IvZ3lQvgFLaIenm41U2pUZNhGs9dyz4Uo6/gJ2NYUoXK');


