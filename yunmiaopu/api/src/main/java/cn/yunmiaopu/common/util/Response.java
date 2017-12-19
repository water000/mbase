package cn.yunmiaopu.common.util;


/**
 * Created by macbookpro on 2017/10/18.
 */
public class Response {

    private String code = Code.OK.toString();
    private Object data = null;

    public static Response error(Object err){
        return err instanceof Enum ? error(null, (Enum)err) : error(err, Code.INVALID_INPUT_VARS);
    }

    public static Response error(Object err, Enum code){
        Response rsp = new Response();
        rsp.code = code.toString();
        rsp.data = err;
        return rsp;
    }

    public static Response ok(Object data){
        Response rsp = new Response();
        rsp.data = data;
        return rsp;
    }

    public static Response ok(){
        Response rsp = new Response();
        return rsp;
    }


    public String getCode() {
        return code;
    }

    public void setCode(String code) {
        this.code = code;
    }

    public Object getData() {
        return data;
    }

    public void setData(Object data) {
        this.data = data;
    }

    public enum Code{
        OK,
        INVALID_INPUT_VARS,
    }
}
