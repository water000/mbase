package cn.yunmiaopu.category.controller;

import cn.yunmiaopu.category.entity.Attribute;
import cn.yunmiaopu.category.entity.Option;
import cn.yunmiaopu.category.service.IAttributeService;
import cn.yunmiaopu.category.service.IOptionService;
import com.alibaba.fastjson.JSON;
import com.alibaba.fastjson.JSONArray;
import com.alibaba.fastjson.JSONObject;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.web.bind.annotation.*;
import org.springframework.web.multipart.MultipartFile;

import java.util.Iterator;
import java.util.List;
import java.util.Optional;

/**
 * Created by macbookpro on 2018/5/29.
 */
@RestController
public class AttributeController {

    @Autowired
    private IAttributeService srv;

    @Autowired
    private IOptionService optsrv;

    @PostMapping("/category-attribute")
    public JSONObject save(Attribute attr,
                           String options,
                           @RequestParam("colorImg[]")MultipartFile[] colorImg){
        byte optionsCounter = 0;

        List<Option> opts = null;
        if(options != null && options.length() > 0)
            opts = JSON.parseArray(options, Option.class);

        if (opts != null && opts.size() > 0) {
            if( 0 == attr.getId() ){
                optionsCounter = (byte)opts.size();
            }else{
                Iterator<Option> srcitr = optsrv.findByAttributeId(attr.getId()).iterator();
                Iterator<Option> dstitr = opts.iterator();

                for(; srcitr.hasNext(); ){
                    Option found = null;
                    Option cmp = srcitr.next();

                    for(; dstitr.hasNext(); ){
                        Option next = dstitr.next();
                        if(next.getId() > 0 && next.getId() == cmp.getId()){
                            found = srcitr.next();
                            break;
                        }
                    }

                    if(found != null){
                        if(found.equals(cmp)){// no need to update if absolutely equals
                            dstitr.remove();
                        }
                        optionsCounter++;
                    }else{
                        srcitr.remove();
                        optionsCounter--;
                        optsrv.deleteById(cmp.getId());
                    }
                }

                for(; dstitr.hasNext(); ){
                    if(0 == dstitr.next().getId())
                        optionsCounter++;
                }
            }
        }


        attr.setOptionsCounter(optionsCounter);
        attr.setEditTs(System.currentTimeMillis()/1000);
        attr = (Attribute)srv.save(attr);
        if(opts != null && opts.size() > 0){
            for(Option o : opts){
                o.setAttributeId(attr.getId());
                optsrv.save(o);
            }
        }

        JSONObject json = (JSONObject)JSON.toJSON(attr);
        json.put("options", opts);
        return json;
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

}
