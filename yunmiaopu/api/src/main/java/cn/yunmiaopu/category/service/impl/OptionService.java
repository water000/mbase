package cn.yunmiaopu.category.service.impl;

import cn.yunmiaopu.category.dao.IOptionDao;
import cn.yunmiaopu.category.entity.Option;
import cn.yunmiaopu.category.service.IOptionService;
import cn.yunmiaopu.common.util.CrudServiceAdapter;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.stereotype.Service;

import javax.annotation.PostConstruct;

/**
 * Created by macbookpro on 2018/5/28.
 */
@Service
public class OptionService extends CrudServiceAdapter implements IOptionService {

    @Autowired
    private IOptionDao dao;

    @PostConstruct
    public void init(){
        super.setRepository(dao);
    }

    public Iterable<Option> findByAttributeId(long attributeId){
        return dao.findByAttributeIdOrderBySeq(attributeId);
    }

    public int updateSeqById(byte order, long id){
        return dao.updateSeqById(order, id);
    }
}
