package cn.yunmiaopu.category.service.impl;

import cn.yunmiaopu.category.dao.IOptionDao;
import cn.yunmiaopu.category.entity.Option;
import cn.yunmiaopu.common.util.CrudServiceAdapter;
import org.springframework.beans.factory.annotation.Autowired;

/**
 * Created by macbookpro on 2018/5/28.
 */
public class IOptionService extends CrudServiceAdapter {

    @Autowired
    private IOptionDao repo;

    public Iterable<Option> findByAttributeId(long attributeId){
        return repo.findByAttributeId(attributeId);
    }
}
