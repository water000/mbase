package cn.yunmiaopu.permission.util;

import cn.yunmiaopu.permission.entity.Action;
import cn.yunmiaopu.permission.entity.ActionMap;
import cn.yunmiaopu.permission.entity.MemberMap;
import cn.yunmiaopu.permission.service.IActionMapService;
import cn.yunmiaopu.permission.service.IActionService;
import cn.yunmiaopu.permission.service.IMemberMapService;
import cn.yunmiaopu.permission.service.IRoleService;
import cn.yunmiaopu.user.entity.UserSession;
import cn.yunmiaopu.user.service.IAccountService;
import com.sun.tools.internal.xjc.reader.xmlschema.bindinfo.BIConversion;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.web.servlet.handler.HandlerInterceptorAdapter;

import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;

/**
 * Created by macbookpro on 2018/1/8.
 */
public class HandleInterceptor extends HandlerInterceptorAdapter {

    @Autowired
    private IActionService acsrv;

    @Autowired
    private IRoleService rosrv;

    @Autowired
    private IMemberMapService mbsrv;

    @Autowired
    private IActionMapService amsrv;

    @Override
    public boolean perHandle(HttpServletRequest request, HttpServletResponse response, Object handler){
        Iterable<Action> foundActions = acsrv.findByHandleMethod(handler.toString());
        if(!foundActions.iterator().hasNext())
            return false;
        Action dest = foundActions.iterator().next();

        UserSession us = UserSession.current(request.getSession());
        if(null == us){
            response.setStatus(HttpServletResponse.SC_UNAUTHORIZED);
            return false;
        }

        Iterable<MemberMap> mm = mbsrv.findByAccountId(us.getAccountId());
        if(!mm.iterator().hasNext()){
            response.setStatus(HttpServletResponse.SC_FORBIDDEN);
            return false;
        }
        MemberMap m = mm.iterator().next();

        Iterable<ActionMap> acmap = amsrv.findByRoleId(m.getRoleId());
        for(ActionMap am : acmap){
            if(am.getActionId() == dest.getId())
                return true;
        }

        return false;
    }
}
