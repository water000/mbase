package cn.yunmiaopu.category.service.impl;

import cn.yunmiaopu.category.dao.ICategoryDao;
import cn.yunmiaopu.category.entity.Category;
import cn.yunmiaopu.common.util.CrudServiceAdapter;
import org.springframework.beans.factory.annotation.Autowired;

import javax.annotation.PostConstruct;

/**
 * Created by macbookpro on 2018/5/28.
 */
public class CategoryService extends CrudServiceAdapter {
    @Autowired
    private ICategoryDao repo;

    public Iterable<Category> findByParentId(long parentId){
        return repo.findByParentId(parentId);
    }

}
