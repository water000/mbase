package cn.yunmiaopu.common.util;

/**
 * Created by macbookpro on 2017/10/18.
 */
public class Response {
    private int status = 1;
    private Object error = null;
    private Object data = null;

    public static Response error(Object err){
        Response rsp = new Response();
        rsp.setError(err);
        return rsp;
    }

    public static Response ok(Object data){
        Response rsp = new Response();
        rsp.setOk(data);
        return rsp;
    }

    public void setError(Object err){
        this.status = 0;
        this.error = err;
    }

    public void setOk(Object data){
        this.status = 1;
        this.data = data;
    }

    public int getStatus() {
        return status;
    }

    public Object getError() {
        return error;
    }

    public Object getData() {
        return data;
    }
}
