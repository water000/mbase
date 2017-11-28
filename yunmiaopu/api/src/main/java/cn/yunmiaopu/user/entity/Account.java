package cn.yunmiaopu.user.entity;

import javax.persistence.*;

/**
 * Created by macbookpro on 2017/9/26.
 */

@Entity(name="user_account")
public class Account {

    @Id
    @GeneratedValue(strategy = GenerationType.IDENTITY)
    private long id;

    private String name;

    private String mobilePhone;

    private String password;

    private long regTs;

    private String regIp;

    private byte status;

    public long getId() {
        return id;
    }

    public String getName() {
        return name;
    }

    public String getMobilePhone() {
        return mobilePhone;
    }

    public String getPassword() {
        return password;
    }

    public long getRegTs() {
        return regTs;
    }

    public String getRegIp() {
        return regIp;
    }

    public byte getStatus() {
        return status;
    }

    public void setId(long id) {
        this.id = id;
    }

    public void setName(String name) {
        this.name = name;
    }

    public void setMobilePhone(String mobilePhone) {
        this.mobilePhone = mobilePhone;
    }

    public void setPassword(String password) {
        this.password = password;
    }

    public void setRegTs(long regTs) {
        this.regTs = regTs;
    }

    public void setRegIp(String regIp) {
        this.regIp = regIp;
    }

    public void setStatus(byte status) {
        this.status = status;
    }
}
