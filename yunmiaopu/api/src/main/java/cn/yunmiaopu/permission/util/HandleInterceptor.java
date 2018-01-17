package cn.yunmiaopu.permission.util;

import cn.yunmiaopu.common.util.Cors;
import cn.yunmiaopu.permission.controller.ActionController;
import cn.yunmiaopu.permission.entity.Action;
import cn.yunmiaopu.permission.entity.ActionMap;
import cn.yunmiaopu.permission.entity.MemberMap;
import cn.yunmiaopu.permission.service.IActionMapService;
import cn.yunmiaopu.permission.service.IActionService;
import cn.yunmiaopu.permission.service.IMemberMapService;
import cn.yunmiaopu.permission.service.IRoleService;
import cn.yunmiaopu.user.entity.UserSession;
import cn.yunmiaopu.user.util.UserSessionArgumentResolver;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.web.method.HandlerMethod;
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
    public boolean preHandle(HttpServletRequest request, HttpServletResponse response, Object handler) throws Exception{
        if(handler instanceof HandlerMethod)
            ;
        else
            return true;
        Iterable<Action> foundActions = acsrv.findByHandleMethod(ActionController.handlerString(handler));
        if(!foundActions.iterator().hasNext())
            return true;
        Action dest = foundActions.iterator().next();

        UserSession us = UserSessionArgumentResolver.filter(request, response);
        if(null == us) {
            Cors.handle(request, response);
            return false;
        }

        Iterable<MemberMap> mm = mbsrv.findByAccountId(us.getAccountId());
        if(!mm.iterator().hasNext()){
            response.setStatus(HttpServletResponse.SC_FORBIDDEN);
            Cors.handle(request, response);
            return false;
        }
        MemberMap m = mm.iterator().next();

        Iterable<ActionMap> acmap = amsrv.findByRoleId(m.getRoleId());
        for(ActionMap am : acmap){
            if(am.getActionId() == dest.getId())
                return true;
        }

        response.setStatus(HttpServletResponse.SC_FORBIDDEN);
        Cors.handle(request, response);
        return false;
    }
}
