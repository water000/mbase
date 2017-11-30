package cn.yunmiaopu.user.controller;

import cn.yunmiaopu.common.util.Response;
import cn.yunmiaopu.user.entity.Account;
import cn.yunmiaopu.user.entity.UserSession;
import cn.yunmiaopu.user.service.IAccountService;
import cn.yunmiaopu.user.util.ErrorCode;
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
import java.util.Optional;

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
    public Object reg(Optional<UserSession> usess, String phone, String pwd, HttpServletRequest req){

        HashMap<String, String> error = new HashMap<String, String>();

        if(usess.isPresent()){
            return Response.error(ErrorCode.SESSION_ALREADY_EXISTS);
        }

        if(!Users.isValidPhone(phone)){
            error.put("phone", phone);
        }
        if(null == pwd || 0 == (pwd = pwd.trim()).length() ){
            error.put("pwd", "");
        }

        if(error.size() > 0){
            return Response.error(error);
        }

        Account ac = new Account();
        ac.setMobilePhone(phone);
        ac.setPassword(Users.genPassword(pwd.getBytes()));
        ac.setRegTs((new Date().getTime()) / 1000);
        ac.setRegIp(req.getRemoteAddr());
        try{
            accountService.save(ac);
        }catch (Exception e){
            logger.error(e);
           return Response.error(ErrorCode.PHONE_ALREADY_EXISTS);
        }

        return Response.ok();
    }

    @RequestMapping(value = "/login", method = RequestMethod.GET)
    public Object login(Optional<UserSession> usess, String phone, String pwd, HttpServletRequest req) throws Exception{
        HashMap<String, String> error = new HashMap<String, String>();

        if(usess.isPresent()){
            return Response.error(ErrorCode.SESSION_ALREADY_EXISTS);
        }

        if(!Users.isValidPhone(phone)){
            error.put("phone", phone);
        }
        if(null == pwd || 0 == (pwd = pwd.trim()).length() ){
            error.put("pwd", "");
        }

        if(error.size() > 0){
            return Response.error(error);
        }

        Account ac = new Account();
        ac.setMobilePhone(phone);
        List<Account> found = accountService.find(ac);
        if(found != null && found.size() > 0){
           if(!Users.comparePassword(pwd.getBytes(), found.get(0).getPassword())){
               error.put("pwd", pwd);
               return Response.error(error, ErrorCode.PASSWORD_INCORRECT.toString());
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

    @RequestMapping(value = "/logout", method = RequestMethod.GET)
    public void logout(UserSession sess, HttpServletRequest req){
        sess.remove(req.getSession());
    }

}
