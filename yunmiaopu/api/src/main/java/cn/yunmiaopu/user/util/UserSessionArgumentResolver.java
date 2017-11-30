package cn.yunmiaopu.user.util;

import cn.yunmiaopu.common.util.HandlerExceptionResolverImpl;
import cn.yunmiaopu.user.entity.UserSession;
import org.springframework.core.MethodParameter;
import org.springframework.web.bind.support.WebDataBinderFactory;
import org.springframework.web.context.request.NativeWebRequest;
import org.springframework.web.method.support.HandlerMethodArgumentResolver;
import org.springframework.web.method.support.ModelAndViewContainer;

import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;
import java.util.Optional;

/**
 * Created by macbookpro on 2017/10/17.
 */
public class UserSessionArgumentResolver implements HandlerMethodArgumentResolver {

    public boolean supportsParameter(MethodParameter parameter) {
        return parameter.getParameterType().equals(UserSession.class)
                || parameter.getParameterType().equals(Optional.class);
    }

    public Object resolveArgument(MethodParameter parameter,
                                  ModelAndViewContainer mavContainer,
                                  NativeWebRequest webRequest,
                                  WebDataBinderFactory binderFactory) throws Exception
    {
        HttpServletRequest req = webRequest.getNativeRequest(HttpServletRequest.class);
        HttpServletResponse rsp = webRequest.getNativeResponse(HttpServletResponse.class);
        UserSession sess = UserSession.getUserSession(req.getSession());
        if(parameter.getParameterType().equals(UserSession.class)){
            if(null == sess){
                throw new UserUnauthorizeException();
            }
            return sess;
        }else{
            return Optional.ofNullable(sess);
        }

    }

    public static class UserUnauthorizeException extends HandlerExceptionResolverImpl.CustomException{
        public void handle(HttpServletRequest request,
                           HttpServletResponse response,
                           java.lang.Object handler)
        {
            response.setStatus(HttpServletResponse.SC_UNAUTHORIZED);
        }
    }
}
