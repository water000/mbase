package cn.yunmiaopu.permission.service.impl;

import cn.yunmiaopu.common.util.CrudServiceAdapter;
import cn.yunmiaopu.permission.dao.IActionMapDao;
import cn.yunmiaopu.permission.entity.ActionMap;
import cn.yunmiaopu.permission.service.IActionMapService;
import org.springframework.beans.factory.annotation.Autowired;

import javax.annotation.PostConstruct;

/**
 * Created by a on 2017/12/21.
 */
public class ActionMapServiceImpl extends CrudServiceAdapter implements IActionMapService {
    @Autowired
    private IActionMapDao dao;

    @PostConstruct
    public void init(){
        super.setRepository(dao);
    }

    public Iterable<ActionMap> findByRoleId(long roleId){
        return dao.findByRoleId(roleId);
    }

    public void deleteByRoleId(long roleId){
        dao.deleteByRoleId(roleId);
    }
}
