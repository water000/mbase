package cn.yunmiaopu.user.service.impl;

import cn.yunmiaopu.common.util.CrudServiceAdapter;
import cn.yunmiaopu.common.util.CrudServiceTemplete;
import cn.yunmiaopu.permission.service.IActionService;
import cn.yunmiaopu.user.dao.IAccountDao;
import cn.yunmiaopu.user.entity.Account;
import cn.yunmiaopu.user.service.IAccountService;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.stereotype.Service;
import org.springframework.transaction.annotation.Transactional;

import javax.annotation.PostConstruct;
import java.util.List;
import java.util.Optional;

/**
 * Created by macbookpro on 2017/10/18.
 */
@Service
@Transactional
public class AccountServiceImpl extends CrudServiceAdapter implements IAccountService {

    @Autowired
    private IAccountDao dao;

    @PostConstruct
    public void init(){
        super.setRepository(dao);
    }

    public List<Account> findByMobilePhone(String mobile_phone){
        return dao.findByMobilePhone(mobile_phone);
    }

}
