package cn.yunmiaopu.permission.dao;

import cn.yunmiaopu.permission.entity.Action;
import org.springframework.data.repository.CrudRepository;

import java.util.List;

/**
 * Created by macbookpro on 2017/9/7.
 */
public interface IActionDao extends CrudRepository<Action, Long> {

}
