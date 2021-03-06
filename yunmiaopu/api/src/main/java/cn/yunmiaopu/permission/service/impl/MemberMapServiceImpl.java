package cn.yunmiaopu.permission.service.impl;

import cn.yunmiaopu.common.util.CrudServiceAdapter;
import cn.yunmiaopu.permission.dao.IMemberMapDao;
import cn.yunmiaopu.permission.entity.MemberMap;
import cn.yunmiaopu.permission.service.IMemberMapService;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.stereotype.Service;

import javax.annotation.PostConstruct;

/**
 * Created by a on 2017/12/21.
 */
@Service
public class MemberMapServiceImpl extends CrudServiceAdapter implements IMemberMapService {
    @Autowired
    private IMemberMapDao dao;

    @PostConstruct
    public void init(){
        super.setRepository(dao);
    }

    public Iterable<MemberMap> findByRoleId(long roleId){
        return dao.findByRoleId(roleId);
    }

    public Iterable<MemberMap> findByAccountId(long accountId){
        return dao.findByAccountId(accountId);
    }

    public void deleteByRoleId(long roleId){
        dao.deleteByRoleId(roleId);
    }
}
