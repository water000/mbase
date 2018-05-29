package cn.yunmiaopu.category.service.impl;

import cn.yunmiaopu.category.dao.ICategoryDao;
import cn.yunmiaopu.category.entity.Category;
import cn.yunmiaopu.category.service.ICategoryService;
import cn.yunmiaopu.common.util.CrudServiceAdapter;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.stereotype.Service;

import javax.annotation.PostConstruct;

/**
 * Created by macbookpro on 2018/5/28.
 */
@Service
public class CategoryService extends CrudServiceAdapter implements ICategoryService {
    @Autowired
    private ICategoryDao repo;

    public Iterable<Category> findByParentId(long parentId){
        return repo.findByParentId(parentId);
    }

}
