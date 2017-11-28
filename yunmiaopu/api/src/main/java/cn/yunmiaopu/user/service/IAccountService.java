package cn.yunmiaopu.user.service;

import cn.yunmiaopu.user.entity.Account;

import java.util.List;

/**
 * Created by macbookpro on 2017/9/27.
 */
public interface IAccountService {
    Account save(Account a) throws Exception;

    void delete(Account a) throws Exception;

    List<Account> find(Account ac);

    Account get(long id) throws Exception;
}
