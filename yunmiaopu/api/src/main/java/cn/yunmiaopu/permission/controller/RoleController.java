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

import javax.servlet.http.HttpServletResponse;
import java.util.*;

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
                     String sMembers,
                     String sActions,
                     HttpServletResponse rsp)
            throws Exception
    {
        if(null == sMembers || 0 == (sMembers=sMembers.trim()).length()
                || null == sActions || 0 == (sActions=sActions.trim()).length()){
            rsp.setStatus(HttpServletResponse.SC_BAD_REQUEST);
            return null;
        }
        String[] members = sMembers.split(",");
        String[] actions = sActions.split(",");


        int now = (int)(new Date().getTime()/1000);

        if(0 == r.getId()){
            r.setCreatorUid(sess.getAccountId());
            r.setCreateTs(now);
        }else{
            List<Long> delete = new ArrayList();
            int idx = 0;

            Arrays.sort(members);
            Iterable<MemberMap> mms = mmServ.findByRoleId(r.getId());
            for(MemberMap mm: mms){
                if(-1 == (idx = Arrays.binarySearch(members, mm.getAccountId())))
                    delete.add(mm.getAccountId());
                else
                    members[idx] = null;
            }
            if(delete.size() > 0){
                mmServ.deleteAll(delete);
                delete.clear();
            }

            Arrays.sort(actions);
            Iterable<ActionMap> ams = acmServ.findByRoleId(r.getId());
            for(ActionMap ac : ams){
                if( -1 == (idx = Arrays.binarySearch(actions, ac.getActionId())))
                    delete.add(ac.getActionId());
                else
                    actions[idx] = null;
            }
            if(delete.size() > 0){
                acmServ.deleteAll(delete);
                delete.clear();
            }
        }
        r.setUpdateTs(now);
        r = (Role)serv.save(r);

        MemberMap mm = new MemberMap();
        mm.setRoleId(r.getId());
        mm.setJoinTs(now);
        for(String m: members){
            if(m != null){
                mm.setAccountId(Long.parseLong(m));
                mmServ.save(mm);
            }
        }

        ActionMap am = new ActionMap();
        am.setRoleId(r.getId());
        am.setJoinTs(now);
        for(String ac : actions){
            if(ac != null){
                am.setActionId(Long.parseLong(ac));
                acmServ.save(am);
            }
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

    @RequestMapping(method=RequestMethod.DELETE)
    public void delete(long id){
        serv.deleteById(id);
        mmServ.deleteByRoleId(id);
        acmServ.deleteByRoleId(id);
    }

}
