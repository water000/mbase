package cn.yunmiaopu.common.util;

import com.alibaba.fastjson.serializer.JSONSerializer;
import com.alibaba.fastjson.serializer.ObjectSerializer;
import net.coobird.thumbnailator.Thumbnails;

import java.io.File;
import java.io.IOException;
import java.io.InputStream;
import java.lang.reflect.Type;
import java.util.UUID;

/**
 * Created by a on 2018/6/7.
 */
public class UploadJpg extends UploadFile implements ObjectSerializer{

    private static final String suffix = ".jpg";

    public static final int SMALL  = 0;
    public static final int MEDIUM = 1;
    public static final int LARGE  = 2;
    protected static int[][] resizeWH = {
            {65, 65},
            {300, 180},
            {1080, 1960}
    };
    private static final char[] size = {'s', 'm', 'l'};

    public UploadJpg(String context) {
        super(context);
    }

    public long move(InputStream in) throws Exception{
        resize(in);
        return 0;
    }

    private void resize(InputStream src, int[] idx) throws Exception{
        genToken();
        if( !new File(getRealDir()).mkdirs()){
            throw new Exception("mkdirs failed");
        }
        Thumbnails.Builder<? extends InputStream> tb = Thumbnails.of(src);
        for(int i : idx){
            tb.size(resizeWH[i][0], resizeWH[i][1])
                    .toFile(path(size[i]));
        }
    }

    public void resizeS(InputStream src) throws Exception{
        resize(src, new int[]{SMALL});
    }
    public void resizeSM(InputStream src) throws Exception{
        resize(src, new int[]{SMALL, MEDIUM});
    }
    public void resizeSL(InputStream src) throws Exception{
        resize(src, new int[]{SMALL, LARGE});
    }
    public void resizeML(InputStream src) throws Exception{
        resize(src, new int[]{MEDIUM, LARGE});
    }
    public void resize(InputStream src) throws Exception{
        resize(src, new int[]{SMALL, MEDIUM, LARGE});
    }

    private String url(char c){
        return new StringBuilder(getWebPath())
                .append(c)
                .append(suffix)
                .toString();
    }

    private String path(char c){
        return new StringBuilder(getRealPath())
                .append(c)
                .append(suffix)
                .toString();
    }

    protected String smallURL() {
        return url(size[SMALL]);
    }
    protected String mediumURL(){
        return url(size[MEDIUM]);
    }
    protected String largeURL(){
        return url(size[LARGE]);
    }

    protected String serializeUrl(){
        return mediumURL();
    }

    public void write(JSONSerializer serializer, Object object, Object fieldName, Type fieldType,
                      int features) throws IOException {
        String token = (String)object;
        if(token != null && token.length() > 0) {
            setToken(token);
            serializer.write(serializeUrl());
        }else{
            serializer.write("");
        }
    }
}
