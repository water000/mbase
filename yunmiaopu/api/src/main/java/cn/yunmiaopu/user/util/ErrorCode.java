package cn.yunmiaopu.user.util;

/**
 * Created by a on 2017/11/30.
 */
public enum ErrorCode {
    SESSION_ALREADY_EXISTS, // user had already login
    PHONE_ALREADY_EXISTS,   // phone had been register by others
    PASSWORD_INCORRECT,
}
