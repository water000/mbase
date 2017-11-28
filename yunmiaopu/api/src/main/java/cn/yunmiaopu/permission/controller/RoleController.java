package cn.yunmiaopu.permission.controller;

import cn.yunmiaopu.permission.entity.Role;
import cn.yunmiaopu.permission.service.IRoleService;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.web.bind.annotation.CrossOrigin;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RequestMethod;
import org.springframework.web.bind.annotation.RestController;

import java.util.List;

/**
 * Created by macbookpro on 2017/8/23.
 */
@RestController
@RequestMapping("/permission/role")
@CrossOrigin("http://localhost:3000")
public class RoleController {

    @Autowired
    private IRoleService serv;

    @RequestMapping(method = RequestMethod.POST)
    public Role save(Role r){
        return serv.save(r);
    }

    @RequestMapping(method=RequestMethod.GET)
    public List<Role> list(){
        return null;
    }

}
