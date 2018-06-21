package cn.yunmiaopu.category.utli;

import cn.yunmiaopu.common.util.UploadJpg;

import java.io.InputStream;

/**
 * Created by a on 2018/6/21.
 */
public class UploadAttributeColor extends UploadJpg {
    public UploadAttributeColor(){
        super("attribute");
    }

    @Override
    public void resize(InputStream src) throws Exception{
        resizeS(src);
    }

    @Override
    public void delete(){
        deleteS();
    }

    @Override
    public String serializeUrl(){
        String token = getToken();
        return token.startsWith("#") ? token : smallURL();
    }
}
