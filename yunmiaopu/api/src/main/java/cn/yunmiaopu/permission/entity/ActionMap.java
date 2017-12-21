package cn.yunmiaopu.permission.entity;

import javax.persistence.Entity;

/**
 * Created by a on 2017/12/20.
 */
@Entity(name = "permission_role_action_map")
public class ActionMap {
    private long actionId;
    private long roleId;
    private long joinTs;

    public long getActionId() {
        return actionId;
    }

    public void setActionId(long actionId) {
        this.actionId = actionId;
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
