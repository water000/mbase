package cn.yunmiaopu.permission.entity;

import javax.persistence.Entity;

/**
 * Created by a on 2017/12/20.
 */
@Entity(name = "permission_role_member_map")
public class MemberMap {
    private long accountId;
    private long roleId;
    private long joinTs;

    public long getAccountId() {
        return accountId;
    }

    public void setAccountId(long accountId) {
        this.accountId = accountId;
    }

    public long getRoleId() {
        return roleId;
    }

    public void setRoleId(long roleId) {
        this.roleId = roleId;
    }

    public long getJoinTs() {
        return joinTs;
    }

    public void setJoinTs(long joinTs) {
        this.joinTs = joinTs;
    }
}
