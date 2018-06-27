package cn.yunmiaopu.category.dao;

import cn.yunmiaopu.category.entity.Attribute;
import org.springframework.data.annotation.LastModifiedBy;
import org.springframework.data.jpa.repository.Modifying;
import org.springframework.data.jpa.repository.Query;
import org.springframework.data.repository.CrudRepository;

import java.util.List;

/**
 * Created by macbookpro on 2018/5/28.
 */
public interface IAttributeDao extends CrudRepository<Attribute, Long> {
    List<Attribute> findByCategoryId(long categoryId);

    @Modifying
    @Query("UPDATE category_attribute a SET a.order=?1 WHERE a.id=?2")
    int updateOrderById(byte order, Long id);

}
