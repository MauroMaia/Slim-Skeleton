create table if not exists slim.User (
	id         bigint auto_increment primary key,
	name       text                                 null,
	email      text                                 null,
	password   text                                 null comment 'Password must be encrypted before',
	created_at datetime default current_timestamp() not null,
	updated_at datetime default current_timestamp() null on update current_timestamp(),
	constraint User_email_pk unique (email) using hash
);

