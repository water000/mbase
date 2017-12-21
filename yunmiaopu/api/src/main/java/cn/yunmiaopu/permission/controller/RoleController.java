package cn.yunmiaopu.permission.controller;

import cn.yunmiaopu.permission.entity.ActionMap;
import cn.yunmiaopu.permission.entity.MemberMap;
import cn.yunmiaopu.permission.entity.Role;
import cn.yunmiaopu.permission.service.IActionMapService;
import cn.yunmiaopu.permission.service.IMemberMapService;
import cn.yunmiaopu.permission.service.IRoleService;
import cn.yunmiaopu.user.entity.UserSession;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.web.bind.annotation.*;

import java.util.ArrayList;
import java.util.Date;
import java.util.HashMap;
import java.util.List;

/**
 * Created by macbookpro on 2017/8/23.
 */
@RestController
@RequestMapping("/permission/role")
public class RoleController {

    @Autowired
    private IRoleService serv;

    @Autowired
    private IActionMapService acmServ;

    @Autowired
    private IMemberMapService mmServ;

    @RequestMapping(method = RequestMethod.POST)
    public Role save(UserSession sess,
                     Role r,
                     @RequestParam(name="members[]")long[] members,
                     @RequestParam(name="actions[]")long[] actions)
            throws Exception
    {
        int now = (int)(new Date().getTime()/1000);

        if(0 == r.getId()){
            r.setCreatorUid(sess.getAccountId());
            r.setCreateTs(now);
        }
        r.setUpdateTs(now);
        r = (Role)serv.save(r);

        MemberMap mm = new MemberMap();
        mm.setRoleId(r.getId());
        mm.setJoinTs(now);
        for(long m: members){
            mm.setAccountId(m);
            mmServ.save(mm);
        }

        ActionMap am = new ActionMap();
        am.setRoleId(r.getId());
        am.setJoinTs(now);
        for(long ac : actions){
            am.setActionId(ac);
            acmServ.save(am);
        }

        return r;
    }

    @RequestMapping(method=RequestMethod.GET)
    public Iterable<Role> list(){
        return serv.findAll();
    }

    @RequestMapping("/members")
    public Object members(long id){
        HashMap<String, Iterable> res = new HashMap(2);
        res.put("members", mmServ.findByRoleId(id));
        res.put("actions", acmServ.findByRoleId(id));
        return res;
    }

}
