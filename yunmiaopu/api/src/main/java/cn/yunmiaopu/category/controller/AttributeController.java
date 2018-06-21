package cn.yunmiaopu.category.controller;

import cn.yunmiaopu.category.entity.Attribute;
import cn.yunmiaopu.category.entity.Option;
import cn.yunmiaopu.category.service.IAttributeService;
import cn.yunmiaopu.category.service.IOptionService;
import com.alibaba.fastjson.JSONObject;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.web.bind.annotation.*;

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
    public JSONObject save(Attribute attr, @RequestBody List<Option> opts){
        byte optionsCounter = 0;

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
        attr = (Attribute)srv.save(attr);
        if(opts != null && opts.size() > 0){
            for(Option opt : opts){
                opt.setAttributeId(attr.getId());
                optsrv.save(opt);
            }
        }

        JSONObject json = new JSONObject();
        json.put("attribute", attr);
        json.put("options", opts);
        return json;
    }

    @GetMapping("/category-attribute/{categoryId}")
    public Attribute get(@PathVariable long attributeId){
        Optional<Attribute> opt = srv.findById(new Long(attributeId));
        if(!opt.isPresent())
            throw new IllegalArgumentException("$attributeId not found");
        return opt.get();
    }

    @GetMapping("/category-attributes/{categoryId}")
    public Iterable<Attribute> list(@PathVariable long categoryId){
        return srv.findByCategoryId(categoryId);
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
