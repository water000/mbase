package cn.yunmiaopu.category.entity;

import cn.yunmiaopu.category.utli.UploadJpg;
import com.alibaba.fastjson.annotation.JSONField;

import javax.persistence.*;

/**
 * Created by macbookpro on 2018/5/24.
 */
@Entity(name = "category_basic")
public class Category {
    @Id
    @GeneratedValue(strategy= GenerationType.IDENTITY)
    private long id;
    private String enName;
    private String cnName;
    @Column(name="[desc]")
    private String desc;
    private long parentId;
    private String wikiUrl;
    private long createTs;
    private boolean closed;
    private String iconToken;


    public long getId() {
        return id;
    }

    public void setId(long id) {
        this.id = id;
    }

    public boolean isClosed() {
        return closed;
    }

    public void setClosed(boolean closed) {
        this.closed = closed;
    }

    public String getEnName() {
        return enName;
    }

    public void setEnName(String enName) {
        this.enName = enName;
    }

    @JSONField(name="iconUrl", serializeUsing = UploadJpg.class)
    public String getIconToken() {
        return iconToken;
    }

    public void setIconToken(String iconToken) {
        this.iconToken = iconToken;
    }

    public String getCnName() {
        return cnName;
    }

    public void setCnName(String cnName) {
        this.cnName = cnName;
    }

    public String getDesc() {
        return desc;
    }

    public void setDesc(String desc) {
        this.desc = desc;
    }

    public long getParentId() {
        return parentId;
    }

    public void setParentId(long parentId) {
        this.parentId = parentId;
    }

    public String getWikiUrl() {
        return wikiUrl;
    }

    public void setWikiUrl(String wikiUrl) {
        this.wikiUrl = wikiUrl;
    }

    public long getCreateTs() {
        return createTs;
    }

    public void setCreateTs(long createTs) {
        this.createTs = createTs;
    }
}
