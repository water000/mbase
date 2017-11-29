package cn.yunmiaopu.user.dao;

import cn.yunmiaopu.user.entity.Account;

import java.util.List;

import org.springframework.data.repository.CrudRepository;

/**
 * Created by macbookpro on 2017/9/27.
 */
public interface IAccountDao extends CrudRepository<Account, Long>{

    List<Account> findByMobilePhone(String mobile_phone);
    /*Account save(Account a) throws Exception;

    void update(Account a)  throws Exception;

    void delete(Account a) throws Exception;

    List<Account> find(Account ac) throws Exception;

    Account get(long id) throws Exception;*/
}
