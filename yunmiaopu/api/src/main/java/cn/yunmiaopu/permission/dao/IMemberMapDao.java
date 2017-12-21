package cn.yunmiaopu.permission.dao;

import cn.yunmiaopu.permission.entity.MemberMap;
import org.springframework.data.repository.CrudRepository;

/**
 * Created by a on 2017/12/21.
 */
public interface IMemberMapDao extends CrudRepository<MemberMap, Long> {
    Iterable<MemberMap> findByRoleId(long roleId);
}
