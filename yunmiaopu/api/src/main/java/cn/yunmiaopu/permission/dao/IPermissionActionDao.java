package cn.yunmiaopu.permission.dao;

import cn.yunmiaopu.permission.entity.Action;

import java.util.List;

/**
 * Created by macbookpro on 2017/9/7.
 */
public interface IPermissionActionDao {

    int save(List<Action> list)  throws Exception; // mark the list as permission-action

    Action get(int id) throws Exception;

    int update(Action ac) throws Exception;

    int remove(List<Action> list) throws Exception;

    List<Action> list() throws Exception;

    void merge(List<Action> list) throws Exception;

}
