package cn.yunmiaopu.category.entity;

import javax.persistence.Entity;

/**
 * Created by macbookpro on 2018/5/24.
 */
@Entity(name = "category_attribute")
public class Attribute {
    public enum ValueType{STRING, TEXT, BYTE, INT, LONG, FLOAT, DOUBLE, TIMESTAMP, OTHER_OPTIONS};
    public enum Type{NORMAL, SALE, IMPORTANT}


    private long id;
    private long categoryId;
    private String name;
    private String value;
    private ValueType valueType;
    private int txtMaxLength;
    private Type type;
    private boolean optionsExists;
    private boolean optionsExtendable;

    public boolean isOptionsExtendable() {
        return optionsExtendable;
    }

    public void setOptionsExtendable(boolean optionsExtendable) {
        this.optionsExtendable = optionsExtendable;
    }

    public long getId() {
        return id;
    }

    public void setId(long id) {
        this.id = id;
    }

    public long getCategoryId() {
        return categoryId;
    }

    public void setCategoryId(long categoryId) {
        this.categoryId = categoryId;
    }

    public String getName() {
        return name;
    }

    public void setName(String name) {
        this.name = name;
    }

    public String getValue() {
        return value;
    }

    public void setValue(String value) {
        this.value = value;
    }

    public ValueType getValueType() {
        return valueType;
    }

    public void setValueType(ValueType valueType) {
        this.valueType = valueType;
    }

    public int getTxtMaxLength() {
        return txtMaxLength;
    }

    public void setTxtMaxLength(int txtMaxLength) {
        this.txtMaxLength = txtMaxLength;
    }

    public Type getType() {
        return type;
    }

    public void setType(Type type) {
        this.type = type;
    }

    public boolean isOptionsExists() {
        return optionsExists;
    }

    public void setOptionsExists(boolean optionsExists) {
        this.optionsExists = optionsExists;
    }
}
