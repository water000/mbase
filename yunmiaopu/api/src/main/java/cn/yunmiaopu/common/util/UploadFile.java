package cn.yunmiaopu.common.util;

import javax.annotation.Resource;
import java.io.File;
import java.io.InputStream;
import java.nio.file.Files;
import java.nio.file.StandardCopyOption;
import java.util.UUID;

/**
 * Created by a on 2018/6/7.
 */
@Resource
public class UploadFile {

    public static final String webRoot = "/upload/";
    public String realRoot;

    private String token;
    private String context;
    private String webDir;

    public UploadFile(String context) {
        this.context = context;
    }

    private void buildWebDir(){
        StringBuilder buf = new StringBuilder();
        buf.append(webRoot)
                .append(context)
                .append('/')
                .append(token.substring(0, 2))
                .append('/');
        webDir = buf.toString();
    }

    protected String genToken(){
        token = UUID.randomUUID().toString();
        buildWebDir();
        return token;
    }

    public void setRealRoot(String root){
        realRoot = root;
    }

    public String getRealDir(){
        return new StringBuilder(realRoot)
                .append(webDir)
                .toString();
    }

    public String getWebDir() {
        return webDir;
    }

    public String getWebPath(){
        return new StringBuilder()
                .append(webDir)
                .append(token)
                .toString();
    }

    public String getRealPath(){
        return new StringBuilder(realRoot)
                .append(webDir)
                .append(token)
                .toString();
    }

    public String getToken() {
        return token;
    }

    public void setToken(String t){
        token = t;
        buildWebDir();
    }

    public String getContext() {
        return context;
    }

    public long move(InputStream src) throws Exception{
        genToken();
        if( !new File(getRealDir()).mkdirs()){
            throw new Exception("mkdirs failed");
        }
        return Files.copy(src,
                new File(getRealPath()).toPath(),
                StandardCopyOption.REPLACE_EXISTING);
    }

}
