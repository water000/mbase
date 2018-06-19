package cn.yunmiaopu.category.entity;

import javax.persistence.Entity;
import javax.persistence.GeneratedValue;
import javax.persistence.GenerationType;
import javax.persistence.Id;

/**
 * Created by macbookpro on 2018/5/24.
 */

@Entity(name = "category_attribute")
public class Attribute {

    public enum InputType {STRING, BYTE, INT, LONG, FLOAT, DOUBLE, TIMESTAMP, TIME}

    public enum Type {INPUT, ENUM, COLOR, CONSTANT}

    public enum NamedColor{AQUA, BLACK, BLUE, FUCHSIA, GRAY, GREEN, LIME, MAROON, NAVY, OLIVE, PURPLE, RED, SILVER, TEAL, WHITE, YELLOW}

    @Id
    @GeneratedValue(strategy = GenerationType.IDENTITY)
    private long id;
    private long categoryId;
    private String name;
    private String value; // constant value, non-constant saved to class Option
    private InputType inputType;
    private Type type;
    private byte optionsCounter; // counter for type[enum, color]
    private long overrideAttributeId; /* true to override the id(not appear) */
    private boolean allowOverride;
    private boolean isPartOfSKU;
    private boolean isRequired;
    private boolean allowSearch;

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

    public InputType getInputType() {
        return inputType;
    }

    public void setInputType(InputType inputType) {
        this.inputType = inputType;
    }

    public Type getType() {
        return type;
    }

    public void setType(Type type) {
        this.type = type;
    }

    public long getOverrideAttributeId() {
        return overrideAttributeId;
    }

    public void setOverrideAttributeId(long overrideAttributeId) {
        this.overrideAttributeId = overrideAttributeId;
    }

    public boolean isPartOfSKU() {
        return isPartOfSKU;
    }

    public void setIsPartOfSKU(boolean isPartOfSKU) {
        this.isPartOfSKU = isPartOfSKU;
    }

    public boolean isRequired() {
        return isRequired;
    }

    public void setIsRequired(boolean isRequired) {
        this.isRequired = isRequired;
    }

   public byte getOptionsCounter() {
        return optionsCounter;
    }

    public void setOptionsCounter(byte optionsCounter) {
        this.optionsCounter = optionsCounter;
    }

    public boolean isAllowOverride() {
        return allowOverride;
    }

    public void setAllowOverride(boolean allowOverride) {
        this.allowOverride = allowOverride;
    }

    public boolean isAllowSearch() {
        return allowSearch;
    }

    public void setAllowSearch(boolean allowSearch) {
        this.allowSearch = allowSearch;
    }
}
