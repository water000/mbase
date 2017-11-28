package cn.yunmiaopu.permission.service.impl;

import cn.yunmiaopu.permission.dao.IPermissionActionDao;
import cn.yunmiaopu.permission.entity.Action;
import cn.yunmiaopu.permission.service.IPermissionActionService;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.stereotype.Service;
import org.springframework.transaction.annotation.Transactional;

import java.util.List;

/**
 * Created by macbookpro on 2017/9/8.
 */
@Service("fileManageService")
@Transactional
public class PermissionActonServiceImpl implements IPermissionActionService {

    @Autowired
    private IPermissionActionDao paDao;

    public int save(List<Action> list) throws Exception{
        return paDao.save(list);
    }

    public Action get(int id) throws Exception{
        return paDao.get(id);
    }

    public int update(Action ac) throws Exception{
        return paDao.update(ac);
    }

    public int remove(List<Action> list) throws Exception{
        return paDao.remove(list);
    }

    public List<Action> list() throws Exception{
        return paDao.list();
    }

    public void merge(List<Action> list) throws Exception{
        paDao.merge(list);
    }

}
