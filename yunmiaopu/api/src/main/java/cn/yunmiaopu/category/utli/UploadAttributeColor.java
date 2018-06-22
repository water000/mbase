package cn.yunmiaopu.category.utli;

import cn.yunmiaopu.category.entity.Attribute;
import cn.yunmiaopu.common.util.UploadJpg;
import org.springframework.stereotype.Component;

import java.io.InputStream;

/**
 * Created by a on 2018/6/21.
 */
@Component
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

        Attribute.NamedColor color = null;
        try{
            color = Attribute.NamedColor.valueOf(token);
        } catch (Exception e){
            color = null;
        }
        return color != null || token.startsWith("#") ? token : smallURL();
    }
}
