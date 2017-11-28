package cn.yunmiaopu.user.dao.impl;

import cn.yunmiaopu.user.dao.IAccountDao;
import cn.yunmiaopu.user.entity.Account;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.orm.hibernate5.HibernateOperations;
import org.springframework.stereotype.Repository;

import java.util.List;

/**
 * Created by macbookpro on 2017/10/17.

@Repository
public class AccountDaoImpl implements IAccountDao {

    @Autowired
    private HibernateOperations hibernateTemplate;

    public Account save(Account a) throws Exception{
        hibernateTemplate.persist(a);
        return a;
    }

    public void update(Account a) throws Exception{
        hibernateTemplate.update(a);
    }

    public void delete(Account a) throws Exception{
        hibernateTemplate.delete(a);
    }

    public List<Account> find(Account ac) throws Exception{
        return hibernateTemplate.findByExample(ac);
    }

    public Account get(long id) throws Exception{
        return hibernateTemplate.get(Account.class, id);
    }
}
 */
