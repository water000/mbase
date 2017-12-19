package cn.yunmiaopu.user.service;

import cn.yunmiaopu.common.util.CrudServiceTemplete;
import cn.yunmiaopu.user.entity.Account;

import java.util.List;

/**
 * Created by macbookpro on 2017/9/27.
 */
public interface IAccountService extends CrudServiceTemplete{
    List<Account> findByMobilePhone(String mobile_phone);
}
