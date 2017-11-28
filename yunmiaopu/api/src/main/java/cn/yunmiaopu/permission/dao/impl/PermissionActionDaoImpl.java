package cn.yunmiaopu.permission.dao.impl;

import cn.yunmiaopu.permission.dao.IPermissionActionDao;
import cn.yunmiaopu.permission.entity.Action;
import org.hibernate.internal.IteratorImpl;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.orm.hibernate5.HibernateOperations;
import org.springframework.stereotype.Repository;

import java.util.ArrayList;
import java.util.Collections;
import java.util.Iterator;
import java.util.List;

/**
 * Created by macbookpro on 2017/9/7.
 */
@Repository
public class PermissionActionDaoImpl implements IPermissionActionDao {

    @Autowired
    private HibernateOperations hibernateTemplate;

    public List<Action> list() throws Exception{
        return (List<Action>)hibernateTemplate.find("FROM permission_action a");
    }

    public int save(List<Action> list) throws Exception{
        for(Action ac : list)
            hibernateTemplate.save(ac) ;
        return 1;
    }

    public Action get(int id) throws Exception {
        return hibernateTemplate.get(Action.class, id);
    }

    public int update(Action ac) throws Exception{
        hibernateTemplate.update(ac) ;
        return 1;
    }

    public int remove(List<Action> list) throws Exception{
        for(Action ac : list)
            hibernateTemplate.delete(ac);
        return 1;
    }

    public void merge(List<Action> list) throws Exception{
        int idx = 0;
        List<Action> src = list();

        for(Action ac : src){
            if(-1 == (idx = Collections.binarySearch(list, ac))){
                hibernateTemplate.delete(ac);
            }else{
                ac.copy(list.get(idx));
                hibernateTemplate.update(ac);
            }
        }
    }

}
