package cn.yunmiaopu.category.service;

import cn.yunmiaopu.category.entity.Category;
import cn.yunmiaopu.common.util.CrudServiceTemplete;

/**
 * Created by macbookpro on 2018/5/28.
 */
public interface ICategoryService extends CrudServiceTemplete {
    Iterable<Category> findByParentId(long parentId);

}
