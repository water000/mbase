package cn.yunmiaopu.category.dao;

import cn.yunmiaopu.category.entity.Attribute;
import org.springframework.data.repository.CrudRepository;

import java.util.List;

/**
 * Created by macbookpro on 2018/5/28.
 */
public interface IAttributeDao extends CrudRepository<Attribute, Long> {
    List<Attribute> findByCategoryId(long categoryId);


}
