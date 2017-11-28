package cn.yunmiaopu.user.entity;

import javax.servlet.http.HttpSession;
import java.util.Date;

/**
 * Created by macbookpro on 2017/10/17.
 */
public class UserSession {
    private long accountId;
    private long loginTs;
    private long lastAccessTs;

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

    public static class Optional extends UserSession{

    }
}
