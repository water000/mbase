package cn.yunmiaopu.permission.dao;

import cn.yunmiaopu.permission.entity.Role;

import java.util.List;

/**
 * Created by macbookpro on 2017/8/23.
 */
public interface IRoleDao {

    Role save(Role r);

    List<Role> list();

    Role update(Role r);

    Role delete(Role r);

}

