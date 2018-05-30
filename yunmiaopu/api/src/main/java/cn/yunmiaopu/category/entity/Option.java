package cn.yunmiaopu.category.entity;

import javax.persistence.Entity;
import javax.persistence.GeneratedValue;
import javax.persistence.GenerationType;
import javax.persistence.Id;

/**
 * Created by macbookpro on 2018/5/24.
 */
@Entity(name = "category_attribute_options")
public class Option {
    @Id
    @GeneratedValue(strategy= GenerationType.IDENTITY)
    private long id;
    private long attributeId;
    private String desc;
    private String image_token;
    private String color;

    public long getId() {
        return id;
    }

    public void setId(long id) {
        this.id = id;
    }

    public long getAttributeId() {
        return attributeId;
    }

    public void setAttributeId(long attributeId) {
        this.attributeId = attributeId;
    }

    public String getDesc() {
        return desc;
    }

    public void setDesc(String desc) {
        this.desc = desc;
    }

    public String getImage_token() {
        return image_token;
    }

    public void setImage_token(String image_token) {
        this.image_token = image_token;
    }

    public String getColor() {
        return color;
    }

    public void setColor(String color) {
        this.color = color;
    }
}
