package cn.yunmiaopu.category.service.impl;

import cn.yunmiaopu.category.dao.IAttributeDao;
import cn.yunmiaopu.category.entity.Attribute;
import cn.yunmiaopu.category.entity.Category;
import cn.yunmiaopu.category.service.ICategoryService;
import cn.yunmiaopu.common.util.CrudServiceAdapter;
import org.springframework.beans.factory.annotation.Autowired;

import java.util.LinkedHashMap;
import java.util.Optional;

/**
 * Created by macbookpro on 2018/5/28.
 */
public class AttributeService extends CrudServiceAdapter {

    @Autowired
    private IAttributeDao repo;

    @Autowired
    private ICategoryService cgysrv;

    public Iterable<Attribute> findByCategoryId(long categoryId){
        return repo.findByCategoryId(categoryId);
    }

    public LinkedHashMap<Category, Iterable<Attribute>> ancestors(long categoryId){
        LinkedHashMap<Category, Iterable<Attribute>> map = new LinkedHashMap<Category, Iterable<Attribute>>();

        while( categoryId != 0 ){
            Optional<Category> cgy = cgysrv.findById(categoryId);
            if(cgy.isPresent()){
                map.put(cgy.get(), findByCategoryId(cgy.get().getId()));
                categoryId = cgy.get().getParentId();
            }else {
                break;
            }
        }

        return map;
    }

}