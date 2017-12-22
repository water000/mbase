package cn.yunmiaopu.user.util;

import cn.yunmiaopu.user.entity.Account;
import org.apache.logging.log4j.LogManager;
import java.security.MessageDigest;
import java.util.List;

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
        for(byte b:pwd){// convert dec to hex
            res[i] = (byte)((short)(b&0xff) >> 4); // convert signed(negative) to unsigned
            res[i] += res[i] > 9 ? 55 : 48;

            res[i+1] = (byte)(b & 0x0F);
            res[i+1] += res[i+1] > 9 ? 55 : 48;

            i+=2;
        }

        return new String(res);
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

    private static void _hide(Account ac){
        if(ac != null){
            ac.setPassword("");
            ac.setMobilePhone(ac.getMobilePhone().substring(0, 6)
                    + "*****" + ac.getMobilePhone().substring(11, 14));
        }
    }
    public static void hidePartial(List<Account> list){
        for(Account ac : list){
            if(ac != null) {
                _hide(ac);
            }
        }
    }
    public static void hidePartial(Account[] list){
        for(Account ac : list){
            if(ac != null) {
                _hide(ac);
            }
        }
    }

}
