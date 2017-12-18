package cn.yunmiaopu.permission.controller;

import cn.yunmiaopu.permission.entity.Action;
import cn.yunmiaopu.permission.service.IActionService;
import cn.yunmiaopu.user.entity.UserSession;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.web.bind.annotation.*;
import org.springframework.web.method.HandlerMethod;
import org.springframework.web.servlet.mvc.method.RequestMappingInfo;
import org.springframework.web.servlet.mvc.method.annotation.RequestMappingHandlerMapping;

import javax.annotation.PostConstruct;
import java.util.*;

/**
 * Created by macbookpro on 2017/8/27.
 */
@RestController
@RequestMapping("/permission/action")
public class ActionController {

    @Autowired
    private RequestMappingHandlerMapping reqMhm;

    @Autowired
    private IActionService pas;

    private List<Action> allActions = new ArrayList<Action>();

    @PostConstruct
    public void init(){
        Map<RequestMappingInfo, HandlerMethod> map = reqMhm.getHandlerMethods();
        for(Map.Entry<RequestMappingInfo, HandlerMethod> entry: map.entrySet()){
            Action ac = new Action();
            ac.setName(  entry.getKey().getName() );
            ac.setHandleMethod(entry.getValue().getMethod().toString());
            ac.setUrlPath(entry.getKey().getPatternsCondition().toString());
            ac.setMenuItem(false);
            allActions.add(ac);
        }
        Collections.sort(allActions);
    }

    @RequestMapping(name="scan", value="/scan")
    public List<Action> scan(UserSession sess){
        return allActions;
    }

    /**
     * mark the action as permission-action and save
     * @param list
     * @return
     */
    @RequestMapping(method = RequestMethod.POST)
    public int save(@RequestBody List<Action> list) throws Exception{
        List<Action> old = new ArrayList<Action>();
        List<Action> neu = new ArrayList<Action>();
        for(Action ac : list){
            if(Collections.binarySearch(allActions, ac) != -1){
                if(ac.getId() > 0){
                    old.add(ac);
                }else{
                    neu.add(ac);
                }
            }
        }
        if(old.size() > 0) {
            Collections.sort(old);
            Iterable<Action> src = pas.findAll();
            int idx = 0;
            for(Action ac : src){
                if( (idx = Collections.binarySearch(old, ac)) != -1){
                    pas.save(old.get(idx));
                }else{
                    pas.delete(ac);
                }
            }
        }
        if(neu.size() > 0)
            pas.saveAll(neu);

        return 1;
    }

    @RequestMapping(method = RequestMethod.GET)
    public Iterable<Action> list() throws Exception{
        return pas.findAll();
    }


}
