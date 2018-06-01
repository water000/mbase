package cn.yunmiaopu.category.controller;

import cn.yunmiaopu.category.entity.Attribute;
import cn.yunmiaopu.category.service.IAttributeService;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.web.bind.annotation.*;

import java.util.List;
import java.util.Optional;

/**
 * Created by macbookpro on 2018/5/29.
 */
@RestController
public class AttributeController {

    @Autowired
    private IAttributeService srv;

    @PostMapping("/category-attribute")
    public Attribute save(Attribute attr){
        return (Attribute)srv.save(attr);
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
