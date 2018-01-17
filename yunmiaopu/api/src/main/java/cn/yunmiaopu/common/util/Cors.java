package cn.yunmiaopu.common.util;

import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;

/**
 * Created by a on 2018/1/16.
 */
public class Cors {
    public static void handle(HttpServletRequest req, HttpServletResponse rsp){
        if(req.getHeader("Origin") != null){
            rsp.addHeader("Access-Control-Allow-Origin", "http://localhost:3000");
            rsp.addHeader("Access-Control-Allow-Credentials", "true");
            rsp.addHeader("Access-Control-Allow-Methods", "POST, GET, OPTIONS, DELETE, PUT, HEAD");
            //rsp.addHeader("Access-Control-Allow-Headers", "Content-Type");
            rsp.addHeader("Access-Control-Max-Age", "1800");
        }
    }
}
