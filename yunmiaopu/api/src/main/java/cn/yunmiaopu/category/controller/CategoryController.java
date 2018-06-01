package cn.yunmiaopu.category.controller;

import cn.yunmiaopu.category.entity.Category;
import cn.yunmiaopu.category.service.ICategoryService;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.web.bind.annotation.*;

import java.util.Optional;

/**
 * Created by macbookpro on 2018/5/28.
 * /categories/{parent_id}
 * /categories = /categories/0
 * /category/{id}
 * /category-attributes/{category_id}
 */
@RestController
public class CategoryController {

    @Autowired
    private ICategoryService cgysrv;

    @GetMapping("category/{id}")
    public Category get(@PathVariable long id){
        Optional<Category> opt = cgysrv.findById(id);
        return opt.isPresent() ? opt.get() : null;
    }

    @PostMapping("/category")
    public Category save(Category cgy){
        cgy = (Category)cgysrv.save(cgy);
        return cgy;
    }

    @GetMapping("/categories/{parentId}")
    public Iterable<Category> children(long parentId){
        return cgysrv.findByParentId(parentId);
    }




}
