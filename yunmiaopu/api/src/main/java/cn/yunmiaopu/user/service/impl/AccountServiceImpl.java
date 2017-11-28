package cn.yunmiaopu.user.service.impl;

import cn.yunmiaopu.user.dao.IAccountDao;
import cn.yunmiaopu.user.entity.Account;
import cn.yunmiaopu.user.service.IAccountService;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.stereotype.Service;

import java.util.List;

/**
 * Created by macbookpro on 2017/10/18.
 */
@Service
public class AccountServiceImpl implements IAccountService {

    @Autowired
    private IAccountDao iAccountDao;


    public Account save(Account a) throws Exception{
        return iAccountDao.save(a);
    }

    public void delete(Account a) throws Exception{
        iAccountDao.delete(a);
    }

    public Account get(long id) throws Exception{
        return iAccountDao.findOne(id);
    }

    public List<Account> find(Account ac){
        return iAccountDao.findByMobilePhone(ac.getMobilePhone());
    }
}