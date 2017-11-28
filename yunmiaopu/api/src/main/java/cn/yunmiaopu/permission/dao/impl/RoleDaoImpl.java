package cn.yunmiaopu.permission.dao.impl;

import cn.yunmiaopu.permission.dao.IRoleDao;
import cn.yunmiaopu.permission.entity.Role;
import org.hibernate.Session;
import org.hibernate.SessionFactory;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.orm.hibernate5.LocalSessionFactoryBean;
import org.springframework.stereotype.Repository;

import java.util.List;

/**
 * Created by macbookpro on 2017/8/23.
 */
@Repository
public class RoleDaoImpl implements IRoleDao {

    @Autowired
    private SessionFactory sessFct;

    public Role save(Role r){
        Session sess = null;

        try{
            sess = sessFct.openSession();
            Object ret = sess.save(r);
        }catch (Exception e){
            e.printStackTrace();
        }finally {
            if(sess != null && sess.isOpen()){
                sess.close();
            }
        }

        return r;
    }

    public List<Role> list(){
        return null;
    }

    public Role update(Role r){
        return null;
    }

    public Role delete(Role r){
        return null;
    }

}
