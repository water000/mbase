package cn.yunmiaopu.common.util;

import org.apache.logging.log4j.LogManager;
import org.apache.logging.log4j.Logger;
import org.springframework.web.servlet.HandlerExceptionResolver;
import org.springframework.web.servlet.ModelAndView;

import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;

/**
 * Created by a on 2017/11/30.
 */
public class HandlerExceptionResolverImpl implements HandlerExceptionResolver {

    private Logger logger = LogManager.getLogger(HandlerExceptionResolverImpl.class);

    public static abstract class CustomException extends RuntimeException {
        public abstract void handle(HttpServletRequest request,
                    HttpServletResponse response,
                    java.lang.Object handler);
    }

    public ModelAndView resolveException(HttpServletRequest request,
                                  HttpServletResponse response,
                                  java.lang.Object handler,
                                  java.lang.Exception ex)
    {
        if(ex instanceof CustomException)
            ((CustomException) ex).handle(request, response, handler);
        else{
            logger.error(ex);
            response.setStatus(HttpServletResponse.SC_INTERNAL_SERVER_ERROR);
            try {
                response.getWriter().append(ex.getMessage());
            }catch (Exception e){
                logger.error(e);
            }
        }
        return null;
    }
}
