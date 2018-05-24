package cn.yunmiaopu.category.controller;

import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RestController;

import java.util.HashMap;

/**
 * Created by macbookpro on 2017/8/18.
 */
@RestController
@RequestMapping("/model")
public class ModelController {

    @RequestMapping("/test")
    public Object test(){
        HashMap<String, String> map = new HashMap();
        map.put("test", "This is test ");
        return map;
    }
}
