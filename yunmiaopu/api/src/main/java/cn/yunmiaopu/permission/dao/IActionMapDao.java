package cn.yunmiaopu.permission.dao;

import cn.yunmiaopu.permission.entity.ActionMap;
import cn.yunmiaopu.permission.entity.MemberMap;
import org.springframework.data.repository.CrudRepository;

/**
 * Created by a on 2017/12/21.
 */
public interface IActionMapDao extends CrudRepository<ActionMap, Long> {
    Iterable<ActionMap> findByRoleId(long roleId);
}
