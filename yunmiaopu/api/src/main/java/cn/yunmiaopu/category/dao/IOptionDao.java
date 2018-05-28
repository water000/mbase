package cn.yunmiaopu.category.dao;

import cn.yunmiaopu.category.entity.Option;
import org.springframework.data.repository.CrudRepository;

import java.util.List;

/**
 * Created by macbookpro on 2018/5/28.
 */
public interface IOptionDao extends CrudRepository<Option, Long> {
    List<Option> findByAttributeId(long attributeId);
}
