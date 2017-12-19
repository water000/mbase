package cn.yunmiaopu.common.util;

import org.apache.logging.log4j.LogManager;
import org.springframework.http.HttpHeaders;
import org.springframework.http.HttpStatus;
import org.springframework.http.ResponseEntity;
import org.springframework.lang.Nullable;
import org.springframework.web.bind.annotation.ControllerAdvice;
import org.springframework.web.context.request.WebRequest;
import org.springframework.web.servlet.mvc.method.annotation.ResponseEntityExceptionHandler;

/**
 * Created by a on 2017/12/19.
 */
@ControllerAdvice
public class CommonResponseEntityExceptionHandler extends ResponseEntityExceptionHandler {
    private static org.apache.logging.log4j.Logger logger = LogManager.getLogger(CommonResponseEntityExceptionHandler.class);

    @Override
    protected ResponseEntity<Object> handleExceptionInternal(Exception ex, @Nullable Object body, HttpHeaders headers, HttpStatus status, WebRequest request) {
        if(HttpStatus.INTERNAL_SERVER_ERROR.equals(status)) {
            request.setAttribute("javax.servlet.error.exception", ex, 0);
        }
        logger.error("internal exception", ex);
        return ex instanceof Customized ? ((Customized) ex).handle(ex, body, headers, status, request) :
                new ResponseEntity(status.is5xxServerError() ? "internal exception:"+ex.getMessage() : null, headers, status);
    }

    public static interface Customized{
        ResponseEntity<Object> handle(Exception ex, @Nullable Object body, HttpHeaders headers, HttpStatus status, WebRequest request) ;
    }
}
