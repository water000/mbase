package cn.yunmiaopu.user.entity;

import javax.servlet.http.HttpSession;
import java.io.Serializable;
import java.util.Date;

/**
 * Created by macbookpro on 2017/10/17.
 */
public class UserSession implements Serializable {
    private static final long serialVersionUID = -8865323105913151247L;
    private long accountId;
    private long loginTs;

    private static final String SESS_KEY = "User.SessionObject";

    public UserSession(){

    }
    public UserSession(long accountId){
        this.accountId = accountId;
        loginTs = new Date().getTime() / 1000;
    }

    public UserSession setUserSession(HttpSession sess){
        sess.setAttribute(SESS_KEY, this);
        return this;
    }

    public static UserSession getUserSession(HttpSession sess){
        return (UserSession)sess.getAttribute(SESS_KEY);
    }

    public void remove(HttpSession sess){
        sess.removeAttribute(SESS_KEY);
    }

    public long getAccountId() {
        return accountId;
    }

    public void setAccountId(long accountId) {
        this.accountId = accountId;
    }

    public long getLoginTs() {
        return loginTs;
    }

    public void setLoginTs(long loginTs) {
        this.loginTs = loginTs;
    }
}
