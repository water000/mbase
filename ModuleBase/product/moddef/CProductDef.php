<?php

class CProductDef extends CModDef {
	protected function desc() {
		return array(
			self::MOD => array(
				self::G_NM=>'product',
				self::M_CS=>'utf-8',
				self::G_TL=>'产品系统',
				self::G_DC=>''
			),
			self::TBDEF => array(
				'product_info'=>'(
					id int unsigned not null auto_increment,
					en_name varchar(16) not null, -- for table name
					name varchar(16) not null, 
					abstract varchar(64),
					logo_path varchar(255) not null,
					baike_link varchar(512) not null,
					create_time int unsigned,
					last_edit_time int unsigned,
					primary key(id),
					unique key(en_name)
				)',
				'product_attr_def'=>'(
					id int unsigned not null auto_increment,
					en_name varchar(16) not null, -- for field name
					name varchar(16) not null, -- used to shown on page
					abstract varchar(32) not null,
					value_type tinyint unsigned not null default 0, -- char, int , ...
					unit_or_size varchar(32) not null default "", -- unit for number(10m) , size for string()
					value_opts varchar(128) not null default "",
					allow_multi tinyint not null default 0,
					default_value varchar(64) not null default "",
					last_edit_time int unsigned,
					create_time int unsigned,
					primary key(id),
					unique key(en_name)
				)',
				'product_attr_map' => '(
					pid int unsigned not null,
					aid int unsigned not null,
					attr_required tinyint not null default 0, -- 0/1
					relate_time int unsigned not null,
					primary key(pid, aid)
				)',
				/*merchant_product_perchase, merchant_product_sale: generated by CMctProctFiled */
			),
			self::PAGES => array(
				'list'     => array(
					self::P_TLE  => '产品列表',
					self::G_DC   => '产品的列表，显示产品的信息',
					self::P_MGR  => true,
					self::P_ARGS => array(
					),
				),
				'edit'     => array(
					self::P_TLE  => '产品编辑',
					self::G_DC   => '产品的编辑、添加，同时关联相应的属性',
					//self::P_MGR  => false,
					self::P_ARGS => array(
						'en_name'    => array(self::PA_REQ=>1, self::G_TL=>'英文名称', self::G_DC=>'有效的英文单词', self::PA_RNG=>'3, 16'),
						'name'       => array(self::PA_REQ=>1, self::G_TL=>'中文名称', self::PA_RNG=>'2, 16'),
						'abstract'   => array(self::PA_REQ=>1, self::G_TL=>'概要', self::PA_RNG=>'16, 64'),
						'logo_path'  => array(self::PA_REQ=>1, self::PA_TYP=>'file', self::G_TL=>'logo图片'),
						'baike_link' => array(self::G_TL=>'百科链接', self::G_DC=>'百科的站外链接，例如百度百科，维基百科'),
					),
				),
				'attr_list'=> array(
					self::P_TLE  => '属性列表',
					self::G_DC   => '所有属性的列表',
					self::P_MGR  => true,
					self::P_ARGS => array(
					),
				),
				'attr_edit'=> array(
					self::P_TLE  => '属性编辑',
					self::G_DC   => '编辑、添加属性',
					self::P_MGR  => true,
					self::P_ARGS => array(
						'en_name'    => array(self::PA_REQ=>1, self::G_TL=>'英文名称', self::G_DC=>'有效的英文单词', self::PA_RNG=>'3, 16'),
						'name'       => array(self::PA_REQ=>1, self::G_TL=>'中文名称', self::PA_RNG=>'2, 16'),
						'abstract'   => array(self::PA_REQ=>1, self::G_TL=>'概要', self::PA_RNG=>'8, 32'),
						'value_type' => array(self::PA_REQ=>1, self::G_TL=>'值类型', self::G_DC=>'属性值的类型'),
						'unit_or_size'=>array(self::G_TL=>'单位/尺寸', self::G_DC=>'如果属性值类型是数字，则是单位；如果是字符串，则是尺寸', self::PA_RNG=>'1,32'),
						'value_opts' => array(self::G_TL=>'值选项', self::G_DC=>'属性值的选项，多个用封号;分隔', self::PA_RNG=>'2,128'),
						'allow_multi'=> array(),
						'default_value'=>array(self::G_TL=>'默认值', self::G_DC=>'属性的默认值。如果有多选，可不填', self::PA_RNG=>'2,128'),
					),
				),
			),
		);
	}
}

?>