package cn.yunmiaopu.permission.service.impl;

import cn.yunmiaopu.common.util.CrudServiceAdapter;
import cn.yunmiaopu.permission.dao.IRoleDao;
import cn.yunmiaopu.permission.entity.Role;
import cn.yunmiaopu.permission.service.IRoleService;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.stereotype.Service;

import javax.annotation.PostConstruct;
import javax.annotation.Resource;

/**
 * Created by macbookpro on 2017/8/23.
 */
@Service
public class RoleServiceImpl extends CrudServiceAdapter implements IRoleService {

    @Autowired
    private IRoleDao dao;

    @PostConstruct
    public void init(){
        super.setRepository(dao);
    }
}
