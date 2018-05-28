package cn.yunmiaopu.category.controller;

import cn.yunmiaopu.category.entity.Category;
import cn.yunmiaopu.category.service.ICategoryService;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.PathVariable;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RestController;

import java.util.Optional;

/**
 * Created by macbookpro on 2018/5/28.
 */
@RestController
@RequestMapping("/category")
public class CategoryController {

    @Autowired
    private ICategoryService cgysrv;

    @GetMapping("/{id}")
    public Category get(@PathVariable long id){
        Optional<Category> opt = cgysrv.findById(id);
        return opt.isPresent() ? opt.get() : null;
    }

    @GetMapping()
    public Iterable<Category> children(){
        return cgysrv.findByParentId(0);
    }


}
