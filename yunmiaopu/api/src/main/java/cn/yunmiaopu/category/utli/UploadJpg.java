package cn.yunmiaopu.category.utli;

import org.springframework.stereotype.Component;

import java.io.InputStream;

/**
 * Created by a on 2018/6/6.
 */
@Component
public class UploadJpg extends cn.yunmiaopu.common.util.UploadJpg {

    public UploadJpg(){
        super("category");
    }

    @Override
    public void resize(InputStream src) throws Exception{
        resizeS(src);
    }

    @Override
    public String serializeUrl(){
        return smallURL();
    }
}
