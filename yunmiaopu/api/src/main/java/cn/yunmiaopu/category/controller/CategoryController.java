package cn.yunmiaopu.category.controller;

import cn.yunmiaopu.category.entity.Category;
import cn.yunmiaopu.category.service.ICategoryService;
import cn.yunmiaopu.common.util.Response;
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
    public Category get(@PathVariable Long id){
        Optional<Category> opt = cgysrv.findById(id);
        return opt.isPresent() ? opt.get() : null;
    }

    @PostMapping("/category")
    public Response save(Category cgy){
        cgy = (Category)cgysrv.save(cgy);
        return Response.ok(cgy);
    }

    @GetMapping("/categories/{parentId}")
    public Iterable<Category> children(@PathVariable Long parentId){
        return cgysrv.findByParentId(parentId);
    }




}
