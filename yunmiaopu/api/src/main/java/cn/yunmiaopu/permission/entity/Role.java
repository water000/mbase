package cn.yunmiaopu.permission.entity;

import javax.persistence.*;

/**
 * Created by macbookpro on 2017/8/21.
 */
@Entity(name="permission_role")
public class Role {

    @Id
    @GeneratedValue(strategy = GenerationType.IDENTITY)
    private  long id;

    private String name;

    private long creatorUid;

    private long createTs;

    private long updateTs;

    private static final int MAX_PREDEFINED_ROLEID = 10;


    public long getId() {
        return id;
    }

    public void setId(long id) {
        this.id = id;
    }

    public String getName() {
        return name;
    }

    public void setName(String name) {
        this.name = name;
    }

    public long getCreatorUid() {
        return creatorUid;
    }

    public void setCreatorUid(long creatorUid) {
        this.creatorUid = creatorUid;
    }

    public long getCreateTs() {
        return createTs;
    }

    public void setCreateTs(long createTs) {
        this.createTs = createTs;
    }

    public long getUpdateTs() {
        return updateTs;
    }

    public void setUpdateTs(long updateTs) {
        this.updateTs = updateTs;
    }

    public static boolean isPredefinedRole(long id){
        return id <= MAX_PREDEFINED_ROLEID;
    }

}
