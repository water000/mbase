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
    private String label;
    private String extra; // extra info like color(white, #fff);image token
    private byte order;

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

    public String getLabel() {
        return label;
    }

    public void setLabel(String label) {
        this.label = label;
    }

    public String getExtra() {
        return extra;
    }

    public void setExtra(String extra) {
        this.extra = extra;
    }

    public byte getOrder() {
        return order;
    }

    public void setOrder(byte order) {
        this.order = order;
    }
}
