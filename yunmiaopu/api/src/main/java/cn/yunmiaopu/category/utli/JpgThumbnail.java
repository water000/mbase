package cn.yunmiaopu.category.utli;

import cn.yunmiaopu.common.util.UploadUtil;

/**
 * Created by a on 2018/6/6.
 */
public class JpgThumbnail extends UploadUtil.JpgThumbnail {
    JpgThumbnail(){
        super("category");
    }

    @Override
    public String serializeUrl(String token){
        return smallURL(token);
    }
}
