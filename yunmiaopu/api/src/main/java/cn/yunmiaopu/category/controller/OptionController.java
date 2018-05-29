package cn.yunmiaopu.category.controller;

import cn.yunmiaopu.category.entity.Attribute;
import cn.yunmiaopu.category.entity.Option;
import cn.yunmiaopu.category.service.IOptionService;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.web.bind.annotation.*;

import java.util.Optional;

/**
 * Created by macbookpro on 2018/5/29.
 */
@RestController
@RequestMapping("/category-attribute-option")
public class OptionController {

    @Autowired
    private IOptionService srv;

    @PutMapping
    public Option save(Option opt){
        return (Option)srv.save(opt);
    }

    @GetMapping("/{attributeId}")
    public Iterable<Option> list(@PathVariable long attributeId){
        return srv.findByAttributeId(attributeId);
    }

    @DeleteMapping("/{optionId}")
    public int delete(@PathVariable long optionId){
        Optional<Option> opt = srv.findById(new Long(optionId));
        if(!opt.isPresent()){
            throw new IllegalArgumentException("$attribute not found");
        }
        srv.delete(opt.get());
        return 0;
    }
}
