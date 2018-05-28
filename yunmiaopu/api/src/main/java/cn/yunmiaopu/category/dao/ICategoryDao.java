package cn.yunmiaopu.category.dao;

import cn.yunmiaopu.category.entity.Category;
import org.springframework.data.repository.CrudRepository;

/**
 * Created by macbookpro on 2018/5/28.
 */
public interface ICategoryDao extends CrudRepository<Category, Long> {
    Iterable<Category> findByParentId(long parentId);
}
