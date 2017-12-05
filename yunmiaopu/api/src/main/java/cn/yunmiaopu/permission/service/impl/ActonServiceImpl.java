package cn.yunmiaopu.permission.service.impl;

import cn.yunmiaopu.common.util.CrudServiceAdapter;
import cn.yunmiaopu.permission.dao.IActionDao;
import cn.yunmiaopu.permission.entity.Action;
import cn.yunmiaopu.permission.service.IActionService;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.stereotype.Service;
import org.springframework.transaction.annotation.Transactional;

import javax.annotation.PostConstruct;
import java.util.List;

/**
 * Created by macbookpro on 2017/9/8.
 */
@Service
public class ActonServiceImpl<Action, Long> extends CrudServiceAdapter implements IActionService {

    @Autowired
    private IActionDao paDao;

    @PostConstruct
    public void init(){
        super.setRepository(paDao);
    }

}
