package cn.yunmiaopu.user.util;

import cn.yunmiaopu.user.entity.UserSession;
import org.springframework.core.MethodParameter;
import org.springframework.web.bind.support.WebDataBinderFactory;
import org.springframework.web.context.request.NativeWebRequest;
import org.springframework.web.method.support.HandlerMethodArgumentResolver;
import org.springframework.web.method.support.ModelAndViewContainer;

import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;

/**
 * Created by macbookpro on 2017/10/17.
 */
public class UserSessionArgumentResolver implements HandlerMethodArgumentResolver {

    public boolean supportsParameter(MethodParameter parameter) {
        return parameter.getParameterType().equals(UserSession.class)
                || parameter.getParameterType().equals(UserSession.Optional.class);
    }

    public Object resolveArgument(MethodParameter parameter,
                                  ModelAndViewContainer mavContainer,
                                  NativeWebRequest webRequest,
                                  WebDataBinderFactory binderFactory) throws Exception
    {
        HttpServletRequest req = webRequest.getNativeRequest(HttpServletRequest.class);
        HttpServletResponse rsp = webRequest.getNativeResponse(HttpServletResponse.class);
        UserSession sess = UserSession.getUserSession(req.getSession());
        if(null == sess && !parameter.getParameterType().equals(UserSession.Optional.class)){
            rsp.setStatus(HttpServletResponse.SC_UNAUTHORIZED);
            throw new Exception("User Unauthorized");
        }
        return sess;
    }
}
