[ Account Authorized ]
1) implements the spring argument resolver interface HandlerMethodArgumentResolver using UserSessionArgumentResolver.
2) class UserSession(break by throwing UnauthorizedException if no session found) or Optional<UserSession> was returned by UserSessionArgumentResolver.resolveArgument().
3) class UserSessionArgumentResolver extends ResponseEntityExceptionHandler which response to set the httpcode to Unauthorized(401) when the UnauthorizedException coming.
3) called method in controller may be like this: public void @RequestMapping("/test")test(UserSession/Optional<UserSession> sess)
