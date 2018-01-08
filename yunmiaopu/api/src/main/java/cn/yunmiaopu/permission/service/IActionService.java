package cn.yunmiaopu.permission.service;

import cn.yunmiaopu.common.util.CrudServiceTemplete;
import cn.yunmiaopu.permission.entity.Action;
import cn.yunmiaopu.permission.entity.MemberMap;

import java.util.List;
import java.util.Optional;

/**
 * Created by macbookpro on 2017/9/7.
 */
public interface IActionService extends CrudServiceTemplete {
    Iterable<Action> findByHandleMethod(String hm);

}
