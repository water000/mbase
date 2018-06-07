package cn.yunmiaopu.category.controller;

import cn.yunmiaopu.category.entity.Category;
import cn.yunmiaopu.category.service.ICategoryService;
import cn.yunmiaopu.category.utli.UploadJpg;
import cn.yunmiaopu.common.util.Response;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.web.bind.annotation.*;
import org.springframework.web.multipart.MultipartFile;

import javax.annotation.PostConstruct;
import javax.servlet.ServletContext;
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

    @Autowired
    private UploadJpg upj;

    @Autowired
    private ServletContext ctx;

    @PostConstruct
    public void init(){
        upj.setRealRoot(ctx.getRealPath("/"));
    }

    @GetMapping("category/{id}")
    public Category get(@PathVariable Long id){
        Optional<Category> opt = cgysrv.findById(id);
        return opt.isPresent() ? opt.get() : null;
    }

    @PostMapping("/category")
    public Response save(Category cgy, MultipartFile icon){
        if(null == icon)
            throw new IllegalArgumentException("$icon is empty");
        try {
            upj.resize(icon.getInputStream());
            cgy.setIconToken(upj.getToken());
        }catch (Exception e){
            e.printStackTrace();
            throw new IllegalArgumentException("$icon read failed");
        }

        if(0 == cgy.getId())
            cgy.setCreateTs(System.currentTimeMillis()/1000);
        cgy = (Category)cgysrv.save(cgy);
        return Response.ok(cgy);
    }

    @GetMapping("/categories/{parentId}")
    public Iterable<Category> children(@PathVariable Long parentId){
        return cgysrv.findByParentId(parentId);
    }




}
