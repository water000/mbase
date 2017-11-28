package cn.yunmiaopu.user.util;

import org.apache.logging.log4j.LogManager;
import java.security.MessageDigest;

/**
 * Created by macbookpro on 2017/9/26.
 */
public class Users {


    private static MessageDigest md = null;
    private static org.apache.logging.log4j.Logger logger = LogManager.getLogger(Users.class);

    static {
        try {
            md = MessageDigest.getInstance("SHA-256");
        } catch (Exception e) {
            logger.error(e);
        }
    }

    public static String genPassword(byte[] txt){
        md.update(txt);
        byte[] pwd = md.digest();

        byte[] res = new byte[pwd.length*2];
        int i = 0;
        for(byte b:pwd){
            res[i] = (byte)(b >> 4);
            res[i] += res[i] > 9 ? 55 : 48;

            res[i+1] = (byte)(b & 0x7F);
            res[i+1] += res[i+1] > 9 ? 55 : 48;

            i+=2;
        }

        return new String(pwd);
    }

    public static boolean comparePassword(byte[] txt, String password){
        return genPassword(txt).equals(password);
    }

    public static boolean isValidPhone(String phone){
        if(14 == phone.length()){ // 14 = 3(country-no) + 11(phone-no)
            char c = 0;
            for(int i=0; i<phone.length(); i++){
               c = phone.charAt(i);
               if(!Character.isDigit(c))
                   return false;
            }
            if('1' != phone.charAt(3))
                return false;
            return true;
        }
        return false;
    }


}
