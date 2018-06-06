package cn.yunmiaopu.common.util;

import com.alibaba.fastjson.serializer.JSONSerializer;
import com.alibaba.fastjson.serializer.ObjectSerializer;
import org.springframework.beans.factory.annotation.Autowired;

import javax.servlet.ServletContext;
import java.io.File;
import java.io.IOException;
import java.lang.reflect.Type;

/**
 * Created by a on 2018/6/6.
 */
public class UploadUtil {

    public static final String root = "/upload/";

    @Autowired
    private ServletContext ctx;

    static boolean mkdir(String relatePath){

        return true;
    }

    public static class JpgThumbnail implements ObjectSerializer {

        private static final String suffix = ".jpg";

        private static final char SMALL  = 's';
        private static final char MEDIUM = 'm';
        private static final char LARGE  = 'l';
        private static int[][] resizeWH = {
                {65, 65},
                {300, 180},
                {1080, 1960}
        };

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
        public String resize(File src){

            return null;
        }

        private String url(String token, char c){
            StringBuffer buf = new StringBuffer();
            buf.append(root)
                    .append(context)
                    .append('/')
                    .append(token)
                    .append(c)
                    .append(suffix);
            return buf.toString();
        }

        protected String smallURL(String token){
            return url(token, SMALL);
        }
        protected String mediumURL(String token){
            return url(token, MEDIUM);
        }
        protected String largeURL(String token){
            return url(token, LARGE);
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
