package cn.yunmiaopu.category.entity;

import cn.yunmiaopu.category.utli.UploadAttributeColor;
import com.alibaba.fastjson.annotation.JSONField;

import javax.persistence.*;

/**
 * Created by macbookpro on 2018/5/24.
 */
@Entity(name = "category_attribute_option")
public class Option {
    @Id
    @GeneratedValue(strategy= GenerationType.IDENTITY)
    private long id;
    private long attributeId;
    private String label;
    private String extra; // extra info like color(white, #fff);image token
    @Column(name="[order]")
    private byte order;

    @Override
    public boolean equals(Object o){
        if(!(o instanceof Option))
            return false;
        Option that = (Option) o;
        if(this == that)
            return true;
        return id == that.id
               && attributeId == that.attributeId
               && (label == that.label || (label != null && that.label != null && label.equals(that.label)))
               && (extra == that.extra || (extra != null && that.extra != null && extra.equals(that.extra)))
               && order == that.order;
    }

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

    @JSONField(serializeUsing = UploadAttributeColor.class)
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
