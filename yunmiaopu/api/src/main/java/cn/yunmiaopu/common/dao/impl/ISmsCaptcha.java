package cn.yunmiaopu.common.dao.impl;

import cn.yunmiaopu.common.entity.SmsCaptcha;

/**
 * Created by macbookpro on 2017/9/29.
 */
public interface ISmsCaptcha {

   int save(String phone, String captcha);

   boolean verify(String phone, String captcha);

}
