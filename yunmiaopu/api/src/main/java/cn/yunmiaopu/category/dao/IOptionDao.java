package cn.yunmiaopu.category.dao;

import cn.yunmiaopu.category.entity.Option;
import org.springframework.data.jpa.repository.Modifying;
import org.springframework.data.jpa.repository.Query;
import org.springframework.data.repository.CrudRepository;

import java.util.List;

/**
 * Created by macbookpro on 2018/5/28.
 */
public interface IOptionDao extends CrudRepository<Option, Long> {
    List<Option> findByAttributeId(long attributeId);

    @Modifying
    @Query("UPDATE category_attribute_option o SET o.order=?1 WHERE o.id=?2")
    int updateOrderById(byte order, Long id);
}
