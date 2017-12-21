package cn.yunmiaopu.permission.service;

import cn.yunmiaopu.common.util.CrudServiceTemplete;
import cn.yunmiaopu.permission.entity.ActionMap;

/**
 * Created by a on 2017/12/21.
 */
public interface IActionMapService extends CrudServiceTemplete {
    Iterable<ActionMap> findByRoleId(long roleId);
}
