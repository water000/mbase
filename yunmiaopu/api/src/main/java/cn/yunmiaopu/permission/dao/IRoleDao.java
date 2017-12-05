package cn.yunmiaopu.permission.dao;

import cn.yunmiaopu.permission.entity.Role;
import org.springframework.data.repository.CrudRepository;

import java.util.List;

/**
 * Created by macbookpro on 2017/8/23.
 */
public interface IRoleDao extends CrudRepository<Role, Long> {


}

