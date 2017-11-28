package cn.yunmiaopu.permission.entity;

import org.hibernate.annotations.Table;

import javax.persistence.Entity;
import javax.persistence.GeneratedValue;
import javax.persistence.GenerationType;
import javax.persistence.Id;

/**
 * Created by macbookpro on 2017/8/21.
 */
@Entity(name = "permission_action")
public class Action implements Comparable<Action>{

    @Id
    @GeneratedValue(strategy = GenerationType.IDENTITY)
    private  long id;

    private String name;

    private String urlPath;

    private String handleMethod;

    private boolean isMenuItem;

    public long getId() {
        return id;
    }

    public String getName() {
        return name;
    }

    public void setName(String name) {
        this.name = name;
    }

    public String getUrlPath() {
        return urlPath;
    }

    public void setUrlPath(String urlPath) {
        this.urlPath = urlPath;
    }

    public String getHandleMethod() {
        return handleMethod;
    }

    public void setHandleMethod(String handleMethod) {
        this.handleMethod = handleMethod;
    }

    public boolean isMenuItem() {
        return isMenuItem;
    }

    public void setMenuItem(boolean menuItem) {
        isMenuItem = menuItem;
    }

    public Action copy(Action src){
        isMenuItem = src.isMenuItem;
        name       = src.name;
        return this;
    }

    public int compareTo(Action ac){
        return ac.handleMethod.compareTo(handleMethod);
    }

}
