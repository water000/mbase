package cn.yunmiaopu.permission.service;

import cn.yunmiaopu.common.util.CrudServiceTemplete;
import cn.yunmiaopu.permission.entity.MemberMap;

/**
 * Created by a on 2017/12/21.
 */
public interface IMemberMapService extends CrudServiceTemplete {
    Iterable<MemberMap> findByRoleId(long roleId);
    void deleteByRoleId(long roleId);
}
