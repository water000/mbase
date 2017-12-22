
create database IF NOT EXISTS cn_yunmiaopu charset=utf8;

use cn_yunmiaopu;

CREATE TABLE IF NOT EXISTS user_account(
	id int unsigned not null auto_increment,
	name varchar(16) default null,
	mobile_phone char(14) not null default '', -- country(3) + number(11)
	password varchar(64) default null, -- sha-256
	reg_ts int unsigned not null default 0,
	reg_ip varchar(32) not null default '',
	status tinyint not null default 0,
	primary key(id),
	unique key(mobile_phone)
);
insert into user_member values(1, 'admin', '13888888888', '',  unix_timestamp(), '', 0);



-- (1, 'size')
CREATE TABLE IF NOT EXISTS unit_family(
	id int unsigned not null auto_increment,
	name varchar(16) default null,
	type tinyint not null default 0, -- 0: int, 1:double
	primary key(id)
);

-- (1, 1, 'centimeter／厘米', 'cm', '公分', 0,  0)
-- (2, 1, 'decimeter/分米',   'dm', '分米', 1, 10)
-- (3, 1, 'meter/米',         'm',  '米',  2, 10)
CREATE TABLE IF NOT EXISTS unit_item(
	id int unsigned not null auto_increment,
	fid int unsigned not null,
	name varchar(16) default null,
	short varchar(16) default null,
	alias varchar(16) not null,
	sub_id int unsigned not null,
	increment int not null,
	primary key(id)
);

CREATE TABLE IF NOT EXISTS model_basic(
	id int unsigned not null auto_increment,
	en_name varchar(32) default null, -- for table name
	name varchar(16) default null, 
	abstract varchar(64) default null,
	logo_path varchar(255) default null,
	baike_link varchar(512) default null,
	create_ts int unsigned not null,
	edit_ts int unsigned not null,
	category_id int unsigned not null, -- exterior and size
	qty_unit varchar(8) default null, -- 1件衣服，1双鞋，1棵树， 株，。。
	primary key(id),
	unique key(en_name)

);

CREATE TABLE IF NOT EXISTS model_attr_selector(
	id int unsigned not null auto_increment,
	name varchar(16) default null, -- also ref attr's name
	primary key(id)
);

CREATE TABLE IF NOT EXISTS model_attr_selector_options(
	sid int unsigned not null default 0,
	id int unsigned not null auto_increment,
	value varchar(64) default null,
	alias varchar(64) default null, -- for color(1, 1, #fff, 白色)
	image_path varchar(128) default null,
	primary key(id),
	key(sid)
);

CREATE TABLE IF NOT EXISTS model_attr_def(
	id int unsigned not null auto_increment,
	en_name varchar(16) default null, -- for field name
	-- name varchar(16) not null, -- used to shown on page
	abstract varchar(32) default null,
	type tinyint unsigned not null default 0, -- unit, select, html, date, string， color-picker
	select_id int unsigned not null default 0,
	allow_multi tinyint not null default 0, -- only affected on type is select
	edit_ts int unsigned not null,
	create_ts int unsigned not null,
	primary key(id)
);

CREATE TABLE IF NOT EXISTS model_attr_map(
	mid int unsigned not null,
	aid int unsigned not null,
	required tinyint not null default 0, -- 0/1
	primary key(mid, aid)
);

CREATE TABLE IF NOT EXISTS shop_basic(
	id int unsigned not null auto_increment,
	owner_uid int unsigned not null,
	name varchar(32) default null,
	longitude double not null,
	latitude  double not null,
	post_code varchar(9) default null,
	address_detail varchar(32) default null, -- country-street-...
	abstract varchar(256) default null,
	status tinyint not null,
	create_ts int unsigned not null,
	edit_ts int unsigned not null,
	image_num tinyint unsigned not null,
	primary key(id),
	key(owner_uid),
	unique key(name)
);

CREATE TABLE IF NOT EXISTS shop_status_modify_log(
	id int unsigned not null auto_increment,
	shop_id int unsigned not null,
	edit_ts int unsigned not null,
	edit_uid int unsigned not null,
	type tinyint not null,
	reason varchar(128) default null,
	primary key(id),
	key(shop_id)
);

CREATE TABLE IF NOT EXISTS shop_image(
	id int unsigned not null auto_increment,
	shop_id int unsigned not null,
	format tinyint not null, -- image, video, ...
	name varchar(16) default null,
	path varchar(64) default null, -- only path, not include domain
	create_ts int unsigned not null,
	primary key(id),
	key(shop_id)
);

CREATE TABLE IF NOT EXISTS shop_product(
	id int unsigned not null auto_increment,
	shop_id int unsigned not null,
	brand_id int unsigned not null default 0,
	product_model_id int unsigned not null default 0,
	image_num tinyint unsigned not null,
	detail_url varchar(512) default null,
);
CREATE TABLE IF NOT EXISTS shop_product_image(
	id int unsigned not null auto_increment,
	shop_id int unsigned not null,
	format tinyint not null, -- image, video, ...
	name varchar(16) default null,
	path varchar(64) default null, -- only path, not include domain
	create_ts int unsigned not null,
	primary key(id),
	key(shop_id)
);

-- height=75cm, level=normal
-- color=red,  size=M

CREATE TABLE IF NOT EXISTS shop_stock_of_(*model)(
	id int unsigned not null auto_increment,
	shop_product_id int unsigned not null default 0,
	attr_id1,
	attr_id2,
	...,
	qty int unsigned not null default 0,
	available int unsigned not null default 0,
	product_no varchar(64) default null,
	image_num tinyint not null default 0,
	primary key(id),
	key(shop_product_id)
);

CREATE TABLE IF NOT EXISTS product_sale_of(*model)(
	id int unsigned not null auto_increment,
	shop_stock_id int unsigned not null default 0,
	price double not null default 0.0,
	qty int unsigned not null default 0,
	available int unsigned not null default 0, 
	discount ...
);

-- class User{
-- @RequestMapping(name='detail', value='/user/detail')
-- public Object detail(int uid){}
-- }
CREATE TABLE IF NOT EXISTS permission_action( 
	id int unsigned not null auto_increment,
	name varchar(32) default null,  -- =detail
	url_path varchar(64) default null, -- =/user/detail
	handle_method varchar(512) default null, -- User.detail
	is_menu_item tinyint not null default 0, -- 0: no, 1: yes
	primary key(id)
);

CREATE TABLE IF NOT EXISTS permission_role(
	id int unsigned not null auto_increment,
	name varchar(32) default null,
	creator_uid int unsigned not null default 0,
	create_ts int unsigned not null default 0,
	update_ts int unsigned not null default 0,
	primary key(id)
);

CREATE TABLE IF NOT EXISTS permission_role_action_map(
	id int unsigned not null auto_increment,
	role_id int unsigned not null default 0,
	action_id int unsigned not null default 0,
	join_ts int unsigned not null default 0,
	primary key(id),
	unique key(role_id, action_id)
);

CREATE TABLE IF NOT EXISTS permission_role_member_map(
	id int unsigned not null auto_increment,
	role_id int unsigned not null default 0,
	account_id int unsigned not null default 0,
	join_ts int unsigned not null default 0,
	primary key(id),
	unique key(role_id, account_id),
	key(account_id)
);

-- /usr/local/mysql/support-files/mysql.server stop
-- /usr/local/mysql/bin/mysqld_safe --skip-grant-tables
-- mysql -u root
-- UPDATE mysql.user SET authentication_string=PASSWORD('123456') WHERE User='root';
-- FLUSH PRIVILEGES;

