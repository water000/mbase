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

import javax.servlet.http.HttpServletRequest;
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
                     String members,
                     String actions,
                     HttpServletRequest req,
                     HttpServletResponse rsp)
            throws Exception
    {
        if(null == members || 0 == (members=members.trim()).length()
                || null == actions || 0 == (actions=actions.trim()).length()){
            rsp.setStatus(HttpServletResponse.SC_BAD_REQUEST);
            return null;
        }
        String[] arrMembers = members.split(",");
        String[] arrActions = actions.split(",");


        int now = (int)(new Date().getTime()/1000);

        if(0 == r.getId()){
            r.setCreatorUid(sess.getAccountId());
            r.setCreateTs(now);
        }else if(r.getId() < 0){
            rsp.setStatus(HttpServletResponse.SC_BAD_REQUEST);
            return null;
        }else{
            List<Long> delete = new ArrayList();
            int idx = 0;

            Arrays.sort(arrMembers);
            Iterable<MemberMap> mms = mmServ.findByRoleId(r.getId());
            for(MemberMap mm: mms){
                if(-1 == (idx = Arrays.binarySearch(arrMembers, String.valueOf(mm.getAccountId()))))
                    delete.add(mm.getAccountId());
                else
                    arrMembers[idx] = null;
            }
            if(delete.size() > 0){
                mmServ.deleteAll(delete);
                delete.clear();
            }

            Arrays.sort(arrActions);
            Iterable<ActionMap> ams = acmServ.findByRoleId(r.getId());
            for(ActionMap ac : ams){
                if( -1 == (idx = Arrays.binarySearch(arrActions, String.valueOf(ac.getActionId()))))
                    delete.add(ac.getActionId());
                else
                    arrActions[idx] = null;
            }
            if(delete.size() > 0){
                acmServ.deleteAll(delete);
                delete.clear();
            }
        }
        r.setUpdateTs(now);
        r = (Role)serv.save(r);

        for(String m: arrMembers){
            if(m != null){
                MemberMap mm = new MemberMap();
                mm.setRoleId(r.getId());
                mm.setJoinTs(now);
                mm.setAccountId(Long.parseLong(m));
                mmServ.save(mm);
            }
        }

        for(String ac : arrActions){
            if(ac != null){
                ActionMap am = new ActionMap();
                am.setRoleId(r.getId());
                am.setJoinTs(now);
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
