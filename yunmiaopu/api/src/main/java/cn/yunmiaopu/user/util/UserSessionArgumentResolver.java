package cn.yunmiaopu.user.util;

import cn.yunmiaopu.common.util.CommonResponseEntityExceptionHandler;
import cn.yunmiaopu.user.entity.UserSession;
import org.springframework.core.MethodParameter;
import org.springframework.http.HttpHeaders;
import org.springframework.http.HttpStatus;
import org.springframework.http.ResponseEntity;
import org.springframework.lang.Nullable;
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

/**
 *     <h4>功能</h4>
 *     <p>使用spring参数解析功能实现用户会话的获取及检查</p>
 *     <h4>知识点</h4>
 *     <p>spring自定义参数解析， spring-controller统一异常处理</p>
 *     <h4>目标</h4>
 *     <p>在controller中定义的方法可以直接注入{@link cn.yunmiaopu.user.entity.UserSession UserSession}类；
 *     如果没有用户会话（未登录或者会话已经过期），handle-method直接打断且返回http-code（状态码）401，方法结束。
 *     如果需要方法不直接返回，使用{@link java.util.Optional}&lt;UserSession&gt;代替UserSession</p>
 *     <h4>实现步骤</h4>
 *     <p>1) 利用spring自定义参数解析机制，实现HandlerMethodArgumentResolver接口用来返回需要解析的方法参数(UserSession, Optional&lt;UserSession&gt;)</p>
 *     <p>2) 定义内部类UserSessionArgumentResolver.UnauthorizedException, 继承org.springframework.web.bind.MissingServletRequestParameterException。</p>
 *     <p>3) 如果需要注入UserSession参数且当前不存在用户会话，那么会抛出自定义的异常类UserSessionArgumentResolver.UnauthorizedException</p>
 *     <p>4) 利用spring统一异常处理机制，实现HandlerMethodArgumentResolver借口，修改返回http-code成401提示客户端当前会话尚未认证（登录）</p>
 *     <h4>示例（如果不存在用户会话）</h4>
 *     <p>直接返回给客户端401：public int @RequestMapping("/test") test(UserSession sess){ [不会进入方法体] } </p>
 *     <p>继续执行：public int @RequestMapping("/test") test(Optional&lt;UserSession&gt;){ [正常运行] }</p>
 */
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

    public static class UnauthorizedException extends MissingServletRequestParameterException implements CommonResponseEntityExceptionHandler.Customized {
        public UnauthorizedException() {
            super("session", "Session[expired or lost]");
        }

        public ResponseEntity<Object> handle(Exception ex, @Nullable Object body, HttpHeaders headers, HttpStatus status, WebRequest request){
            if(ex instanceof UserSessionArgumentResolver.UnauthorizedException)
                status = HttpStatus.UNAUTHORIZED;
            return  new ResponseEntity(null, headers, status);
        }
    }


}
