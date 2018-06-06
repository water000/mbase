package cn.yunmiaopu.common.util;

import com.alibaba.fastjson.serializer.JSONSerializer;
import com.alibaba.fastjson.serializer.ObjectSerializer;
import net.coobird.thumbnailator.Thumbnails;
import org.springframework.beans.factory.annotation.Autowired;

import javax.annotation.PostConstruct;
import javax.servlet.ServletContext;
import java.io.File;
import java.io.InputStream;
import java.io.IOException;
import java.lang.reflect.Type;
import java.util.UUID;

/**
 * Created by a on 2018/6/6.
 */
public class UploadUtil {

    public static final String webRoot = "/upload/";
    public static String realRoot;

    @Autowired
    private ServletContext ctx;

    @PostConstruct
    public void init(){
        realRoot = ctx.getRealPath("/");
    }

    private static TokenDir genDir(String context){
        TokenDir tokenDir = new TokenDir(context);
        String dir = realRoot + tokenDir.getWebDir();
        if(! new File(dir).mkdir())
            throw new RuntimeException("Failed to create dir: " + dir);
        return tokenDir;
    }

    static class TokenDir{
        private String token;
        private String context;
        private String webDir;

        public TokenDir(String t, String contenxt){
            this.token = null == t ? UUID.randomUUID().toString() : t;
            this.context = context;
            StringBuilder buf = new StringBuilder();
            buf.append(webRoot)
                    .append(context)
                    .append('/')
                    .append(token.substring(0, 2))
                    .append('/');
            webDir = buf.toString();
        }

        public TokenDir(String context){
            this(null, context);
        }

        public String getWebDir(){
            return webDir;
        }

        public String getToken(){
            return token;
        }

        public String getContext(){
            return context;
        }
    }

    public static class JpgThumbnail implements ObjectSerializer {

        private static final String suffix = ".jpg";

        public static final int SMALL  = 0;
        public static final int MEDIUM = 1;
        public static final int LARGE  = 2;
        public static int[][] resizeWH = {
                {65, 65},
                {300, 180},
                {1080, 1960}
        };
        private static final char[] size = {'s', 'm', 'l'};

        private String context;

        protected JpgThumbnail(String context){
            this.context = context;
        }
        public JpgThumbnail(){

        }

        public void setContext(String context){
            this.context = context;
        }
        public String getContext(){
            return this.context;
        }

        /**
         * resize the @src to pre-defined @resizeWH
         * @param src
         * @return the token
         */
        private String resize(InputStream src, int[] idx) throws Exception{
            TokenDir td = genDir(context);
            Thumbnails.Builder<? extends InputStream> tb = Thumbnails.of(src);
            for(int i : idx){
                tb.size(resizeWH[i][0], resizeWH[i][1])
                    .toFile(realRoot + url(td, size[i]));
            }
            return td.getToken();
        }

        public String resizeS(InputStream src) throws Exception{
            return resize(src, new int[]{SMALL});
        }
        public String resizeSM(InputStream src) throws Exception{
            return resize(src, new int[]{SMALL, MEDIUM});
        }
        public String resizeSL(InputStream src) throws Exception{
            return resize(src, new int[]{SMALL, LARGE});
        }
        public String resizeML(InputStream src) throws Exception{
            return resize(src, new int[]{MEDIUM, LARGE});
        }
        public String resize(InputStream src) throws Exception{
            return resize(src, new int[]{SMALL, MEDIUM, LARGE});
        }

        private String url(TokenDir td, char c){
             return new StringBuilder(td.getWebDir())
                    .append(c)
                    .append(suffix)
                     .toString();
        }

        protected String smallURL(String token) {
            return url(new TokenDir(token, context), size[SMALL]);
        }
        protected String mediumURL(String token){
            return url(new TokenDir(token, context), size[MEDIUM]);
        }
        protected String largeURL(String token){
            return url(new TokenDir(token, context), size[LARGE]);
        }

        protected String serializeUrl(String token){
            return mediumURL(token);
        }

        public void write(JSONSerializer serializer, Object object, Object fieldName, Type fieldType,
                          int features) throws IOException {
            String token = (String)object;
            serializer.write(serializeUrl(token));
        }

    }
}
