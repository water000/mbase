package cn.yunmiaopu.category.service;

import cn.yunmiaopu.category.entity.Attribute;
import cn.yunmiaopu.category.entity.Category;
import cn.yunmiaopu.common.util.CrudServiceTemplete;

import java.util.LinkedHashMap;
import java.util.List;

/**
 * Created by macbookpro on 2018/5/28.
 */
public interface IAttributeService extends CrudServiceTemplete {
    Iterable<Attribute> findByCategoryId(long categoryId);

    LinkedHashMap<Category, Iterable<Attribute>> ancestors(long categoryId);

    int updateSeqById(byte order, long id, long timestamp);
}
