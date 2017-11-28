package cn.yunmiaopu.user.controller;

import cn.yunmiaopu.common.util.Response;
import cn.yunmiaopu.user.entity.Account;
import cn.yunmiaopu.user.entity.UserSession;
import cn.yunmiaopu.user.service.IAccountService;
import cn.yunmiaopu.user.util.Users;
import org.apache.logging.log4j.LogManager;
import org.apache.logging.log4j.Logger;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RequestMethod;
import org.springframework.web.bind.annotation.RestController;
import org.springframework.web.servlet.HandlerExecutionChain;
import org.springframework.web.servlet.HandlerInterceptor;
import org.springframework.web.servlet.ModelAndView;

import javax.servlet.*;
import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;
import javax.servlet.http.HttpSession;
import java.io.IOException;
import java.util.Date;
import java.util.HashMap;
import java.util.List;

/**
 * Created by macbookpro on 2017/9/27.
 */
@RestController
@RequestMapping("/user/account")
public class AccountController {

    @Autowired
    private IAccountService accountService;

    private Logger logger = LogManager.getLogger(AccountController.class);

    /**
     * 1) input the phone then request the captcha( if the phone exists already then return an error)
     * 2) submit the {phone, captcha} to the server
     * 3) check the captcha whether valid
     * 3) check the phone whether exists
     * 4) save a new Account
     * 5) do login
     * 3) continue to complete more basic account info or ignore
     * @param phone
     * @param captcha
     */
    public void reg(String phone,
                    int captcha)
    {

    }

    @RequestMapping(value="/reg", method = RequestMethod.GET)
    public Object reg(UserSession.Optional usess, String phone, String pwd){

        HashMap<String, String> error = new HashMap<String, String>();

        if(usess != null){
            return Response.error("user session already exists, logout first");
        }

        if(!Users.isValidPhone(phone)){
            error.put("phone", "invalid phone: "+phone);
        }
        if(null == pwd || 0 == (pwd = pwd.trim()).length() ){
            error.put("pwd", "empty pwd");
        }

        if(error.size() > 0){
            return Response.error(error);
        }

        Account ac = new Account();
        ac.setMobilePhone(phone);
        ac.setPassword(Users.genPassword(pwd.getBytes()));
        ac.setRegTs((new Date().getTime())/1000);
        try{
            accountService.save(ac);
        }catch (Exception e){
            logger.error(e);
            e.printStackTrace();
            error.put("phone", "phone already exists");
           return Response.error(error);
        }

        return Response.ok("reg success");
    }

    @RequestMapping(value = "/login", method = RequestMethod.GET)
    public Object login(String phone, String pwd, HttpServletRequest req) throws Exception{
        HashMap<String, String> error = new HashMap<String, String>();

        if(!Users.isValidPhone(phone)){
            error.put("phone", "invalid phone: "+phone);
        }
        if(null == pwd || 0 == (pwd = pwd.trim()).length() ){
            error.put("pwd", "empty pwd");
        }

        if(error.size() > 0){
            return Response.error(error);
        }

        Account ac = new Account();
        ac.setMobilePhone(phone);
        List<Account> found = accountService.find(ac);
        if(found != null && found.size() > 0){
           if(!Users.comparePassword(pwd.getBytes(), found.get(0).getPassword())){
               error.put("pwd", "incorrect pwd");
               return Response.error(error);
           }
           UserSession us = new UserSession(ac.getId());
           us.setUserSession(req.getSession());
           return true;
        }
        return false;
    }

    /**
     * save as reg but exclude the save
     * @param phone
     * @param captcha
     */
    public void login(String phone, int captcha){

    }


    public void modify(Account info){

    }

    public void logout(){

    }

}
