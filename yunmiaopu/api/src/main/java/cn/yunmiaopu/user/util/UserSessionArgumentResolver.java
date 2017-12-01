package cn.yunmiaopu.user.util;

import cn.yunmiaopu.user.entity.UserSession;
import org.springframework.core.MethodParameter;
import org.springframework.http.HttpHeaders;
import org.springframework.http.HttpStatus;
import org.springframework.http.ResponseEntity;
import org.springframework.web.bind.MissingServletRequestParameterException;
import org.springframework.web.bind.annotation.ControllerAdvice;
import org.springframework.web.bind.support.WebDataBinderFactory;
import org.springframework.web.context.request.NativeWebRequest;
import org.springframework.web.context.request.WebRequest;
import org.springframework.web.method.support.HandlerMethodArgumentResolver;
import org.springframework.web.method.support.ModelAndViewContainer;
import org.springframework.web.servlet.mvc.method.annotation.ResponseEntityExceptionHandler;

import javax.servlet.http.HttpServletRequest;
import java.util.Optional;

@ControllerAdvice
public class UserSessionArgumentResolver extends ResponseEntityExceptionHandler implements HandlerMethodArgumentResolver {

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
        UserSession sess = UserSession.getUserSession(req.getSession());
        if(parameter.getParameterType().equals(UserSession.class)){
            if(null == sess){
                throw new UnauthorizedException();
            }
            return sess;
        }else{
            return Optional.ofNullable(sess);
        }

    }

    public static class UnauthorizedException extends MissingServletRequestParameterException {
        public UnauthorizedException() {
            super("session", "Session[expired or lost]");
        }
    }

    @Override
    protected ResponseEntity<Object> handleMissingServletRequestParameter(MissingServletRequestParameterException ex, HttpHeaders headers, HttpStatus status, WebRequest request) {
        if(ex instanceof UserSessionArgumentResolver.UnauthorizedException)
            status = HttpStatus.UNAUTHORIZED;
        return this.handleExceptionInternal(ex, (Object)null, headers, status, request);
    }
}
