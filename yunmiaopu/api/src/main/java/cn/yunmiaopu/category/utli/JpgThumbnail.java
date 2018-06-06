package cn.yunmiaopu.category.utli;

import cn.yunmiaopu.common.util.UploadUtil;

import java.io.File;
import java.io.InputStream;

/**
 * Created by a on 2018/6/6.
 */
public class JpgThumbnail extends UploadUtil.JpgThumbnail {
    public JpgThumbnail(){
        super("category");
    }

    @Override
    public String resize(InputStream src) throws Exception{
        return resizeS(src);
    }

    @Override
    public String serializeUrl(String token){
        return smallURL(token);
    }
}
