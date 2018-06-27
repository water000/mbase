package cn.yunmiaopu.category.service;

import cn.yunmiaopu.category.entity.Option;
import cn.yunmiaopu.common.util.CrudServiceTemplete;

import java.util.List;

/**
 * Created by macbookpro on 2018/5/28.
 */
public interface IOptionService extends CrudServiceTemplete {
    Iterable<Option> findByAttributeId(long attributeId);

    int updateOrderById(byte order, long id);
}
