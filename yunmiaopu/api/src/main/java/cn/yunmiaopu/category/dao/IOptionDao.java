package cn.yunmiaopu.category.dao;

import cn.yunmiaopu.category.entity.Option;
import org.springframework.data.jpa.repository.Modifying;
import org.springframework.data.jpa.repository.Query;
import org.springframework.data.repository.CrudRepository;

import javax.transaction.Transactional;
import java.util.List;

/**
 * Created by macbookpro on 2018/5/28.
 */
public interface IOptionDao extends CrudRepository<Option, Long> {
    List<Option> findByAttributeIdOrderBySeq(long attributeId);

    @Modifying
    @Query("UPDATE category_attribute_option o SET o.seq=?1 WHERE o.id=?2")
    @Transactional
    int updateSeqById(byte order, Long id);
}
