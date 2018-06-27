package cn.yunmiaopu.category.controller;

import cn.yunmiaopu.category.entity.Attribute;
import cn.yunmiaopu.category.entity.Option;
import cn.yunmiaopu.category.service.IAttributeService;
import cn.yunmiaopu.category.service.IOptionService;
import cn.yunmiaopu.category.utli.UploadAttributeColor;
import cn.yunmiaopu.common.util.Response;
import com.alibaba.fastjson.JSON;
import com.alibaba.fastjson.JSONArray;
import com.alibaba.fastjson.JSONObject;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.web.bind.annotation.*;
import org.springframework.web.multipart.MultipartFile;

import javax.annotation.PostConstruct;
import javax.servlet.ServletContext;
import java.util.*;

/**
 * Created by macbookpro on 2018/5/29.
 */
@RestController
public class AttributeController {

    @Autowired
    private IAttributeService srv;

    @Autowired
    private IOptionService optsrv;

    @Autowired
    private UploadAttributeColor uac;

    @Autowired
    private ServletContext ctx;

    @PostConstruct
    public void init(){
        uac.setRealRoot(ctx.getRealPath("/"));
    }

    private void _delImg(String token){
        Attribute.NamedColor color = null;
        try{
            color = Attribute.NamedColor.valueOf(token);
        } catch (Exception e){
            color = null;
        }
        if(null == color && !token.startsWith("#")){
            uac.setToken(token);
            uac.delete();
        }
    }

    @PostMapping("/category-attribute")
    public Response save(Attribute attr,
                           String options,
                           @RequestParam("colorImg[]")MultipartFile[] colorImg){
        byte optionsCounter = 0;

        List<Option> opts = null;
        List<Option> ignore = new ArrayList<Option>();

        if(attr.getType() == Attribute.Type.COLOR || attr.getType() == Attribute.Type.ENUM){
            if(options != null && options.length() > 0)
                opts = JSON.parseArray(options, Option.class);
            else
                opts = new ArrayList<Option>();

            if( 0 == attr.getId() ){
                optionsCounter = (byte)opts.size();
            }else{
                Iterator<Option> srcitr = optsrv.findByAttributeId(attr.getId()).iterator();

                for(; srcitr.hasNext(); ){
                    Option found = null;
                    Option cmp = srcitr.next();

                    for(Option next : opts){
                        if(next.getId() > 0 && next.getId() == cmp.getId()){
                            found = next;
                            break;
                        }
                    }

                    if(found != null){
                        if(cmp.getExtra() != null){
                            String extra = found.getExtra();
                            if(null == extra || 0 == extra.length()) {
                                _delImg(cmp.getExtra());
                            }else{
                                found.setExtra(cmp.getExtra());
                                if(found.equals(cmp)){// no need to update if absolutely equals
                                    ignore.add(found);
                                }
                            }
                        }
                        optionsCounter++;
                    }else{
                        if(cmp.getExtra() != null)
                            _delImg(cmp.getExtra());
                        srcitr.remove();
                        optsrv.deleteById(cmp.getId());
                    }
                }

                for(Option opt : opts ){
                    if(0 == opt.getId())
                        optionsCounter++;
                }
            }
        }


        attr.setOptionsCounter(optionsCounter);
        attr.setEditTs(System.currentTimeMillis()/1000);
        attr = (Attribute)srv.save(attr);

        if(opts != null && opts.size() > 0){
            int i=0;
            for(Option o : opts){
                if(ignore.contains(o))
                    continue;
                if(Attribute.Type.COLOR == attr.getType()
                        && colorImg != null
                        && colorImg.length > 0
                        && (null == o.getExtra() || 0 == o.getExtra().length())) {
                    try {
                        uac.resize(colorImg[i++].getInputStream());
                        o.setExtra(uac.getToken());
                    } catch (Exception e) {
                        continue;
                    }
                }
                o.setAttributeId(attr.getId());
                optsrv.save(o);
            }
        }

        JSONObject json = (JSONObject)JSON.toJSON(attr);
        json.put("options", opts);
        return Response.ok(json);
    }

    @GetMapping("/category-attribute/{categoryId}")
    public JSONObject get(@PathVariable long attributeId){
        Optional<Attribute> opt = srv.findById(new Long(attributeId));
        if(!opt.isPresent())
            throw new IllegalArgumentException("$attributeId not found");
        Attribute attr = opt.get();
        JSONObject json = (JSONObject)JSONObject.toJSON(attr);
        json.put("options", optsrv.findByAttributeId(attr.getId()));

        return json;
    }

    @GetMapping("/category-attributes/{categoryId}")
    public JSONArray list(@PathVariable long categoryId){
        JSONArray ret = new JSONArray();

        for(Attribute attr : srv.findByCategoryId(categoryId) ){
            JSONObject item = (JSONObject)JSON.toJSON(attr);
            item.put("options", optsrv.findByAttributeId(attr.getId()));
            ret.add(item);
        }

        return ret;
    }

    @DeleteMapping("/{attributeId}")
    public int delete(@PathVariable long categoryId){
        Optional<Attribute> opt = srv.findById(new Long(categoryId));
        if(!opt.isPresent()){
            throw new IllegalArgumentException("$categoryId not found");
        }
        srv.delete(opt.get());
        return 0;
    }

    @PutMapping("/category-attribute")
    public int reorder(String json){
        int res = 0;
        JSONObject obj = JSON.parseObject(json);
        if(obj.size() > 0){
            for(Map.Entry<String, Object> entry : obj.entrySet()){
                byte order = 0;
                try {
                    order = Byte.parseByte(entry.getKey());
                }catch (Exception e){

                }
                if(entry.getKey().endsWith("attr")) {
                    res += srv.updateOrderById(order > 0 ? order : 0, (Integer)entry.getValue());
                }else{
                    res += optsrv.updateOrderById(order > 0 ? order : 0, (Integer)entry.getValue());
                }
            }
        }

        return res;
    }

}
