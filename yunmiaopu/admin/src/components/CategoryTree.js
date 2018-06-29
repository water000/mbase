import React from 'react';
import {Tree, Row, Col,  Menu, Dropdown, Icon, Form, Input, Button, Switch, message, Table, Badge, Avatar, Upload, List, Checkbox, Modal, Tag} from 'antd';
import moment from 'moment';
import RestFetch from "../RestFetch"
import ColPicker from "../ColPicker"
import {RequestKVRadioGroup, RequestKVSelect, RequestKVSelectGroup} from "../RequestKV"
import Global from "../Global"
const TreeNode = Tree.TreeNode;
const FormItem = Form.Item;

class CategoryForm extends React.Component{

  constructor(props){
    super(props);

    this.state = {
      loading:false,
      fields:{
        id:{
          value:0,
        },
        cnName:{
          value:'',
          validateStatus:''
        },
        enName:{
          value:'',
          validateStatus:''
        },
        desc:{
          value:'',
          validateStatus:''
        },
        wikiUrl:{
          value:'',
          validateStatus:''
        },
        closed:{
          value:false,
          validateStatus:''
        },
        iconUrl:{
          value:[/*{
            url:'',
            status : 'done',
          }*/],
          validateStatus:''
        },
      }
    };
  }

  cleanFileds(){
    this.setState(prevStates=>{
      for(var key in prevStates.fields){
          prevStates.fields[key].value = '';
      }
      prevStates.fields.closed.value = false;
      prevStates.fields.iconUrl.value = [];
      return prevStates;
    });

  }

  setValue(input){
    this.setState(prevStates=>{
      prevStates.fields[input.name].value = input.value.replace(/^\s*|\s*$/g, "");
      return prevStates;
    });
  }
  setChecked(key, bool){
    this.setState(prevStates=>{
      prevStates.fields[key].value = bool;
      return prevStates;
    });
  }

  handleUploadChange = ({fileList})=>{
    this.setState(prevStates=>{
      prevStates.fields.iconUrl.value = fileList;
      return prevStates;
    });
  }
  handleBeforeUpload = (file) => {
    this.setState(prevStates=>{
      prevStates.fields.iconUrl.value.push(file);
      return prevStates;
    });
    return false;
  }
  handleRemoveUpload = (file) => {
    this.setState(prevStates=>{
      let idx = prevStates.fields.iconUrl.value.indexOf(file);
      if(idx >= 0 ){
        prevStates.fields.iconUrl.value.splice(idx, 1);
      }
      return prevStates;
    });
  }

  handleSubmit = (e)=>{
    e.preventDefault();

    if(!this.state.fields.cnName.value){
      this.setState(prevStates=>{
        prevStates.fields.cnName.validateStatus = 'error';
        prevStates.fields.cnName.help = 'CN-Name is required';
        return prevStates;
      });
      return;
    }
    if(!this.state.fields.enName.value){
      this.setState(prevStates=>{
        prevStates.fields.enName.validateStatus = 'error';
        prevStates.fields.enName.help = 'EN-Name is required';
        return prevStates;
      });
      return;
    }
    if(0 == this.state.fields.iconUrl.value.length){
        this.setState(prevStates=>{
        prevStates.fields.iconUrl.validateStatus = 'error';
        prevStates.fields.iconUrl.help = 'Icon is required';
        return prevStates;
      });
      return;
    }

    this.setState(prevStates=>{
      prevStates.loading = true;
      return prevStates;
    });

    var formData = new FormData(e.target);
    if(this.state.fields.iconUrl.value[0].originFileObj)
      formData.append('icon', this.state.fields.iconUrl.value[0].originFileObj);
    formData.append('closed', this.state.fields.closed.value);
    this.props.onSubmit(formData).then(rsp=>{
      this.setState(prevStates=>{
        prevStates.loading = false;
        return prevStates;
      });

      if('OK' == rsp.code){
        if(0 == formData.get('id'))
          this.cleanFileds();
        return;
      }
      this.setState(prevStates=>{
        for(var k in rsp.data){
          if(prevStates.fields[k]){
            prevStates.fields[k].validateStatus = 'error';
            prevStates.fields[k].help = rsp.data[k];
          }
        }
        return prevStates;
      });
    }).catch(e=>{
      this.setState(prevStates=>{
        prevStates.loading = false;
        return prevStates;
      });
    });
  }

  componentDidUpdate(prevProps, prevStates){
    if(this.props.parentData.key !== prevProps.parentData.key || this.props.initValue.id !== prevProps.initValue.id){
      if(!this.props.initValue || undefined === this.props.initValue.id)
        this.cleanFileds();
      else
        this.setState(prevStates=>{
          for(var k in this.props.initValue){
            if(this.props.initValue[k] !== undefined && prevStates.fields[k] !== undefined)
              prevStates.fields[k].value = this.props.initValue[k];
          }
          if(prevStates.fields['iconUrl'].value != null && prevStates.fields['iconUrl'].value.length != 0){
            prevStates.fields['iconUrl'].value = [{url:Global.imgUrl(this.props.initValue['iconUrl']), status:'done', uid:-1, name:""}];
          }
          return prevStates;
        });
    }
  }

  /*componentWillReceiveProps(nextProps){
    if(!nextProps.initValue || undefined === nextProps.initValue.cnName)
      this.cleanFileds();
    else
      this.setState(prevStates=>{
        for(var k in nextProps.initValue){
          if(nextProps.initValue[k] !== undefined && prevStates.fields[k] !== undefined)
            prevStates.fields[k].value = nextProps.initValue[k];
        }
        if(prevStates.fields['iconUrl'].value != null && prevStates.fields['iconUrl'].value.length != 0){
          prevStates.fields['iconUrl'].value = [{url:Global.imgUrl(nextProps.initValue['iconUrl']), status:'done', uid:-1, name:""}];
        }
        return prevStates;
      });
  }*/

  render(){
    const formItemLayout = {
      labelCol: {
        xs: { span: 24 },
        sm: { span: 5 },
      },
      wrapperCol: {
        xs: { span: 24 },
        sm: { span: 19 },
      },
    };
    return <div style={{display:this.props.display}}>
      <h4 className="title">Category mounted within <i>{this.props.parentData.title}</i></h4>
      <Form layout="vertical" onSubmit={this.handleSubmit} style={{padding:"12px 10px", background:'#fff'}} >
        <Input type="hidden" name="parentId" value={this.props.parentData.rawdata.id||0} />
        <Input type="hidden" name="id" value={this.props.initValue.id||0} />
        <FormItem {...formItemLayout} {...this.state.fields.cnName} label="CN-Name" >
          <Input name="cnName" value={this.state.fields.cnName.value} onChange={(e)=>this.setValue(e.target)} required /> 
        </FormItem>
        <FormItem {...formItemLayout} {...this.state.fields.enName} label="EN-Name">
          <Input name="enName" value={this.state.fields.enName.value} onChange={(e)=>this.setValue(e.target)} required /> 
        </FormItem>
        <FormItem {...formItemLayout} {...this.state.fields.desc} label="Describe">
          <Input name="desc" value={this.state.fields.desc.value} onChange={(e)=>this.setValue(e.target)}/> 
        </FormItem>
        <FormItem {...formItemLayout} {...this.state.fields.wikiUrl} label="Wiki-URL">
          <Input name="wikiUrl" value={this.state.fields.wikiUrl.value} onChange={(e)=>this.setValue(e.target)}/> 
        </FormItem>
        <FormItem {...formItemLayout} {...this.state.fields.closed} label="Closed">
          <Switch checked={this.state.fields.closed.value} onChange={(b)=>this.setChecked('closed', b)}/> 
        </FormItem>
        <FormItem {...formItemLayout} {...this.state.fields.iconUrl} label="Icon">
          <Upload
            name="icon"
            action=""
            listType="picture-card"
            fileList={this.state.fields.iconUrl.value}
            beforeUpload={this.handleBeforeUpload}
            onChange={this.handleUploadChange} >
            { (null == this.state.fields.iconUrl.value || 0 == this.state.fields.iconUrl.value.length) &&
            <div>
              <Icon type="plus" />
              <div className="ant-upload-text">Upload</div>
            </div> }
          </Upload>
        </FormItem>
        <Button loading={this.state.loading} type="primary" htmlType="submit" style={{width:"100%"}}>Submit</Button>
      </Form>
    </div>
  }
}

class CategoryTable extends React.Component{

  constructor(props){
    super(props);
    this.state = {
      previewVisible: false,
      previewImage: '',
    }
  }

  handleZoomOutImg = (img)=>{
    this.setState(prevStates=>{
      prevStates.previewVisible = true;
      prevStates.previewImage = img.src;
      return prevStates;
    });
  }
  handleCancel = ()=>{
    this.setState(prevStates=>{
      prevStates.previewVisible = false;
      return prevStates;
    });
  }

  render(){
    var header = true;
    var fn=(item, idx, length)=>{
      var desc;
      var actions = this.props.orderable ? [] : [<a onClick={()=>this.props.onEdit(item)}><Icon type="edit" /></a>];
      if(item.isLeaf){
        if('CONSTANT' == item.rawdata.type)
          desc = 'value: ' + item.rawdata.value;
        else if('INPUT' == item.rawdata.type)
          desc = 'InputType: ' + item.rawdata.inputType;
        else if(item.rawdata.optionsCounter > 0){
          desc = item.rawdata.options.map((opt, idx)=>{
            var extra, style={};
            if(opt.extra instanceof File)
              extra = <img className='color-bg' onClick={(e)=>this.handleZoomOutImg(e.target)} 
                src={window[window.webkitURL ? 'webkitURL' : 'URL']['createObjectURL'](opt.extra)} />;
            else if(opt.extra != null && opt.extra.length > 0){
              if(opt.extra.indexOf('.jpg') > 0)
                extra = <img src={Global.imgUrl(opt.extra)} onClick={(e)=>this.handleZoomOutImg(e.target)} 
                  style={{width:'12px', height:'12px'}} />
              else
                style={background:opt.extra};
            }
            return <Tag style={style}>
              {this.props.orderable && idx>0 && <a onClick={e=>this.props.onAttributeOrderChange(item, 'forward', opt)}><Icon type='arrow-left'/></a> } 
                {opt.label}{extra}
              {this.props.orderable && idx != item.rawdata.options.length-1 && <a onClick={e=>this.props.onAttributeOrderChange(item, 'backward', opt)}><Icon type='arrow-right' /></a>}
            </Tag>;
          });
          desc = <span>Enums:{desc}</span>
        }

        if(this.props.orderable){
          if(!header)
            actions.push(<a onClick={e=>this.props.onAttributeOrderChange(item, 'forward')}><Icon type="arrow-up" /></a>);
          else
            header = false;
          if( idx != length-1)
            actions.push(<a onClick={e=>this.props.onAttributeOrderChange(item, 'backward')}><Icon type="arrow-down" /></a>);
        }
      }

      return item.isLeaf ?
        <List.Item actions={actions}>
          <List.Item.Meta
            avatar={<Avatar style={{backgroundColor:'#00a2ae'}}>{item.rawdata.type.substr(0,3)}</Avatar>}
            title={<div>{item.rawdata.name}</div>}
            description={<div>
              {desc}&nbsp;&nbsp;Options: {item.rawdata.isPartOfSKU && <Tag>Part-Of-SKU</Tag>}
              {item.rawdata.isRequired && <Tag>Required</Tag>}
              {item.rawdata.allowSearch && <Tag>Allow Search</Tag>}
              {item.rawdata.allowOverride && <Tag>Allow Override</Tag>} 
              &nbsp;edit:{new Date(item.rawdata.editTs*1000).toLocaleDateString()};</div>}
          />
        </List.Item>
        :
        <List.Item actions={actions}>
          <List.Item.Meta
            avatar={<Avatar src={Global.imgUrl(item.rawdata.iconUrl)} >{item.rawdata.enName}</Avatar>}
            title={<div><Badge text={item.rawdata.cnName} status={item.rawdata.closed ? "default" : "success"}/></div>}
            description={<div>{item.rawdata.desc}<div> create:{new Date(item.rawdata.createTs*1000).toLocaleDateString()}&nbsp;&nbsp;{item.rawdata.wikiUrl && <a href={item.rawdata.wikiUrl} style={{color:'#bbb'}}>wiki: <i>{item.rawdata.wikiUrl}</i></a>}</div></div>}
          />
        </List.Item>;
    }
    return (
      <div>
        <List
          itemLayout="horizontal"
          size="small"
          style={{background:'#fff'}}
          dataSource={this.props.data}
          renderItem={(item, idx) => fn(item ,idx, this.props.data.length)}
        />
        <Modal visible={this.state.previewVisible} footer={null} onCancel={this.handleCancel}>
          <img style={{ width: '100%' }} src={this.state.previewImage} />
        </Modal>
      </div>
    );
  }

}

class AttributeForm extends React.Component{

  constructor(props){
    super(props);
    this.state = {
      loading:false,
      fields:{
        id:{
          value:0,
        },
        name:{
          value:'',
          validateStatus:''
        },
        value:{
          value:'',
          validateStatus:''
        },
        type:{
          value:'',
        },
        inputType:{
          value:'',
        },
        inputUnit:{
          value:'',
        },
        isPartOfSKU:{
          value:false,
        },
        isRequired:{
          value:false,
        },
        allowSearch:{
          value:false,
        },
        allowOverride:{
          value:false,
        },
        options:{
          value:[]
        }
      }
    };
  }

  isValueEmpty(){
    return !this.state.fields.value.value 
      && !this.state.fields.inputType.value
      && 0 == this.state.fields.options.value.length
  }

  cleanValue(){
    this.state.fields.value.value = '';
    this.state.fields.inputType.value = '';
    this.state.fields.inputUnit.value = '';
    this.state.fields.options.value = [];
  }

  cleanFileds(){
    this.setState(prevStates=>{
      for(var k in prevStates.fields){
        switch(typeof prevStates.fields[k].value){
          case 'string':
            prevStates.fields[k].value = '';
            break;
          case 'number':
            prevStates.fields[k].value = 0;
            break;
          case 'boolean':
            prevStates.fields[k].value = false;
            break;
          default:
            if(prevStates.fields[k].value instanceof Array){
              prevStates.fields[k].value = [];
            }
          break;
        }
      }
      return prevStates;
    });
  }

  handleValueChange=(target)=>{
    this.setState(prevStates=>{
      prevStates.fields[target.name].value = target.value;
      return prevStates;
    });
  }

  handleTypeChange=(value)=>{
    if(!this.isValueEmpty()){
      Modal.confirm({
        title:'Are you sure to swith the radio even if the value of previous type is not empty?',
        content:'operation can not be revert',
        okType: 'danger',
        onOk:()=>{
          this.setState(prevStates=>{
              prevStates.fields.type.value = value;
            return prevStates;
          });
          this.cleanValue();
        }
      });
    }else{
      this.setState(prevStates=>{
          prevStates.fields.type.value = value;
        return prevStates;
      });
    }
  }

  handleInputTypeChange=(value)=>{
    this.setState(prevStates=>{
      prevStates.fields.inputType.value = value;
      return prevStates;
    });
  }

  handleInputUnitChange=(value)=>{
    this.setState(prevStates=>{
      prevStates.fields.inputUnit.value = value;
      return prevStates;
    });
  }

  handleSwitch=(target)=>{
    this.setState(prevStates=>{
      prevStates.fields[target.name].value = !prevStates.fields[target.name].value;
      return prevStates;
    });
  }

  handleChangeEnum=(target, item)=>{
    this.setState(prevStates=>{
      for(var i=0; i<this.state.fields.options.value.length; i++){
        if(item === this.state.fields.options.value[i]){
          if(0 == target.value.length){
            this.state.fields.options.value.splice(i, 1);
          }else{
            item.label = target.value;
          }
          break;
        }
      }
      return prevStates;
    });
  }

  handleAppendEnum=(target)=>{
    if(target.value.length > 0){
      this.setState(prevStates=>{
        prevStates.fields.options.value.push({label:target.value});
        target.value = '';
        return prevStates;
      });
    }
  }

  handleColorChange=(checked, {label, extra, dataRef})=>{
    this.setState(prevStates=>{
      if(checked){
        prevStates.fields.options.value.push({label, extra});
      }else{
        for(var i=0; i<prevStates.fields.options.value.length; i++){
          if(dataRef === prevStates.fields.options.value[i]){
            prevStates.fields.options.value.splice(i, 1);
            break;
          }
        }
      }
      return prevStates;
    });
  }

  handleColorSubmit=({label, extra, dataRef})=>{
    this.setState(prevStates=>{
      if(null == dataRef){
        prevStates.fields.options.value.push({label, extra});
      }else{
        for(var i=0; i<prevStates.fields.options.value.length; i++){
          if(dataRef === prevStates.fields.options.value[i]){
            prevStates.fields.options.value[i].label = label;
            prevStates.fields.options.value[i].extra = extra;
            break;
          }
        }
      }
      return prevStates;
    });
  }

  handleSubmit = (e)=>{
    e.preventDefault();

    this.setState(prevStates=>{
      prevStates.loading = true;
      return prevStates;
    });

    var form = new FormData();
    form.append('categoryId', this.props.category.rawdata.id);
    for(var k in this.state.fields){
      if( k != 'options'){
        form.append(k, this.state.fields[k].value);
      }
    }
    for(var i=0; i<this.state.fields.options.value.length; i++){
      if(this.state.fields.options.value[i].extra instanceof File){
        form.append('colorImg[]', this.state.fields.options.value[i].extra);
        this.state.fields.options.value[i].extra = '';
      }
    }
    form.append('options', JSON.stringify(this.state.fields.options.value));
    var imgs = form.getAll('colorImg[]');
    if(imgs && imgs.length > 0){
      var c=0;
      for(var i=0; i<this.state.fields.options.value.length; i++){
        if('' == this.state.fields.options.value[i].extra){
          this.state.fields.options.value[i].extra = imgs[c++];
        }
      }
    }
    this.props.onSubmit(form).then(rsp=>{
      this.setState(prevStates=>{
        prevStates.loading = false;
        return prevStates;
      });

      if('OK' == rsp.code){
        if(0 == form.get('id'))
          this.cleanFileds();
        return;
      }
      this.setState(prevStates=>{
        for(var k in rsp.data){
          if(prevStates.fields[k]){
            prevStates.fields[k].validateStatus = 'error';
            prevStates.fields[k].help = rsp.data[k];
          }
        }
        return prevStates;
      });
    }).catch(e=>{
      this.setState(prevStates=>{
        prevStates.loading = false;
        return prevStates;
      });
    });
  }

  componentDidUpdate(prevProps, prevStates){
    if(this.props.initValue.id !== prevProps.initValue.id){
      if(!this.props.initValue || undefined === this.props.initValue.id)
        this.cleanFileds();
      else
        this.setState(prevStates=>{
          for(var k in this.props.initValue){
            if(this.props.initValue[k] !== undefined && prevStates.fields[k] !== undefined)
              prevStates.fields[k].value = this.props.initValue[k];
          }
          return prevStates;
        });
    }
  }

  render(){
    const formItemLayout = {
      labelCol: {
        xs: { span: 24 },
        sm: { span: 5 },
      },
      wrapperCol: {
        xs: { span: 24 },
        sm: { span: 19 },
      },
    };
    return <div style={{display:this.props.display}}>
      <h4 className="title">Attribute mounted within <span className="ant-form-text"><i>{this.props.category.title}</i></span></h4>
      <Form layout="vertical" style={{padding:"12px 10px", background:'#fff'}} onSubmit={this.handleSubmit}>
        <Input type="hidden" name="categoryId" value={this.props.initValue.categoryId||0} />
        <Input type="hidden" name="id" value={this.props.initValue.id||0} />
        <FormItem {...formItemLayout} {...this.state.fields.name} label="Name" >
          <Input name="name" value={this.state.fields.name.value} onChange={(e)=>this.handleValueChange(e.target)} required /> 
        </FormItem>
        <FormItem {...formItemLayout} {...this.state.fields.type} label="Type">
          <RequestKVRadioGroup
            value={this.state.fields.type.value} 
            defaultValue={this.state.fields.type.value} 
            onChange={this.handleTypeChange} 
            name='type'
            url='/category/enums'
            id='Attribute.Type' />
        </FormItem>
        <FormItem {...formItemLayout} {...this.state.fields.value} label="Value" >
          {
            'CONSTANT' == this.state.fields.type.value && 
              <Input name="value" value={this.state.fields.value.value} onChange={(e)=>this.handleValueChange(e.target)} required /> 
          }
          {
            'COLOR' == this.state.fields.type.value && 
              <ColPicker checkedColor={this.state.fields.options.value}
                onChange={this.handleColorChange}
                onSubmit={this.handleColorSubmit} />
          }
          {
            'ENUM' == this.state.fields.type.value &&
              <span>
                <Input name="enum" id='0' 
                  placeholder={'Entry to create; Back until empty to Delete'}
                  onPressEnter={(e)=>{e.preventDefault();this.handleAppendEnum(e.target)}} 
                  required={0==this.state.fields.options.value.length} />
                {
                  this.state.fields.options.value.map((item,idx)=>
                     <span style={{display:'inline-block',width:'50%', borderBottom:'1px dashed #ccc'}}>
                        {idx+1}.<Input name="enum" id={idx} value={item.label} 
                        style={{border:'0',width:'85%'}}
                        onChange={(event)=>this.handleChangeEnum(event.target, item)} 
                        required />
                      </span>     
                  )
                }
              </span>
          }
          {
            'INPUT' == this.state.fields.type.value && 
              <span><RequestKVSelect 
                name='inputType'
                defaultValue={this.state.fields.inputType.value} 
                value={this.state.fields.inputType.value} 
                style={{width:'60%'}}
                emptyOption={{value:'', label:'--select input type--'}}
                url='/category/enums'
                id='Attribute.InputType'
                onChange={this.handleInputTypeChange} />
              <RequestKVSelectGroup 
                name='inputUnit'
                defaultValue={this.state.fields.inputUnit.value} 
                value={this.state.fields.inputUnit.value} 
                style={{width:'40%'}}
                emptyOption={{value:'', label:'--select input unit--'}}
                url='/category/enums'
                id='Attribute.UnitFamily'
                onChange={this.handleInputUnitChange} /></span>}
        </FormItem>
        <FormItem {...formItemLayout} {...this.state.fields.isPartOfSKU} label="Option" >
          <Checkbox 
            name='isPartOfSKU'
            defaultChecked={this.state.fields.isPartOfSKU.value}
            checked={this.state.fields.isPartOfSKU.value}
            onChange={(e)=>this.handleSwitch(e.target)}
          >
            SKU Part 
          </Checkbox>
        </FormItem>
        <FormItem {...formItemLayout} {...this.state.fields.isRequired} label="Option" >
          <Checkbox 
            name='isRequired'
            defaultChecked={this.state.fields.isRequired.value}
            checked={this.state.fields.isRequired.value}
            onChange={(e)=>this.handleSwitch(e.target)}
          >
            Required
          </Checkbox>
        </FormItem>
        <FormItem {...formItemLayout} {...this.state.fields.allowSearch} label="Option" >
          <Checkbox 
            name='allowSearch'
            defaultChecked={this.state.fields.allowSearch.value}
            checked={this.state.fields.allowSearch.value}
            onChange={(e)=>this.handleSwitch(e.target)}
          >
            Allow Search
          </Checkbox>
        </FormItem>
        <FormItem {...formItemLayout} {...this.state.fields.allowOverride} label="Option" >
          <Checkbox 
            name='allowOverride'
            defaultChecked={this.state.fields.allowOverride.value}
            checked={this.state.fields.allowOverride.value}
            onChange={(e)=>this.handleSwitch(e.target)}
          >
            Allow Override
          </Checkbox>
        </FormItem>
        <Button loading={this.state.loading} type="primary" htmlType="submit" style={{width:"100%"}}>Submit</Button>
      </Form>
    </div>
  }

}

export default class CategoryTree extends React.Component{
	state = {
    curNodeData : null,
    draggable: false,
    treeData: [
      { title: 'All', key: '0', children:[], rawdata:{} },
      //{ title: 'Tree Node', key: '2', isLeaf: true },
    ],
    form:{
      span:0,
      display:{
        category:"none",
        attribute:"none"
      },
      initValue:{

      }
    },
    reorder:{
      steps:['reorder', 'save', 'loading...'],
      cursor:0,
      undoStack:[]
    }
  }
  
  constructor(props){
  	super(props);
    this.state.curNodeData = this.state.treeData[0];
  	this.restCgyParent  = new RestFetch("/categories");
    this.restCgy  = new RestFetch("/category");
  	this.restAttrList = new RestFetch("/category-attributes");
    this.restAttr = new RestFetch("/category-attribute");
  	this.restOpt  = new RestFetch("/category-attribute-option");
  }

  setCurrent = (data)=>{
    this.setState({curNodeData: data});
  }

  onNodeExpand = (expandedKeys, {expanded, node}) => {
    if(!node.props.dataRef.isLeaf)
      this.setCurrent(node.props.dataRef);
  }

  onNodeSelect = (selectedKeys, {selected, selectedNodes, node, event}) => {
    if(!node.props.dataRef.isLeaf)
      this.setCurrent(node.props.dataRef);
  }

  addCategory(category){
    var ret, list = category instanceof Array ? category : [category];
    this.setState(prevStates=>{
      for( var i=0; i<list.length; i++ ){
        var category = list[i];
        ret = {title:category.cnName, key:`${category.id}C`, children:[], rawdata:category};
        prevStates.curNodeData.children.push(ret);
      }
      return prevStates;
    });
    return ret;
  }
  setCategory(category, data){
    category.title = data['cnName'];
    for(var k in category.rawdata){
      if( data[k] !== undefined )
        category.rawdata[k] = data[k] ;
    }
    this.setState(prevStates=>prevStates);
  }
  addAttribute(attribute){
    var ret, list = attribute instanceof Array ? attribute : [attribute];
    this.setState(prevStates=>{
      for(var i=0; i<list.length; i++){
        var attribute = list[i];
        ret = prevStates.curNodeData.children.push({
          title:attribute.name, key:`${attribute.id}A`, isLeaf:true, rawdata:attribute});
      }
      return prevStates;
    });
    return ret;
  }
  setAttribute(attribute, data){
    attribute.title = data.name;
    for(var k in attribute.rawdata){
      if( data[k] !== undefined )
        attribute.rawdata[k] = data[k] ;
    }
    this.setState(prevStates=>prevStates);
  }

  showCategoryForm = ()=>{
    this.setState(prevStates=>{
      prevStates.form.span = 10;
      prevStates.form.display.category = '';
      prevStates.form.display.attribute = 'none';
      prevStates.form.initValue = {};
      return prevStates;
    });
  }

  showAttributeForm = ()=>{
    if(this.state.curNodeData.key != '0'){
      this.setState(prevStates=>{
        prevStates.form.span = 10;
        prevStates.form.display.category = 'none';
        prevStates.form.display.attribute = '';
        prevStates.form.initValue = {};
        return prevStates;
      });
    }
  }

  handleAttributeSubmit = (formData)=>{
    return new Promise((resolve, reject)=>{
      this.restAttr.create(formData).then(res=>res.json()).then(json=>{
        if('OK' == json.code){
          let id = 0;
          if(formData instanceof FormData)
            id = formData.get('id');
          else if(formData instanceof Object)
            id = formData.id || 0;

          if(0 == id){
            var ret = this.addAttribute(json.data);
          }else{
            for(var i=0; i<this.state.curNodeData.children.length; i++){
              if(id == this.state.curNodeData.children[i].rawdata.id){
                this.setAttribute(this.state.curNodeData.children[i], json.data);
                break;
              }
            }
          }
          message.success('Attribute saved!');
        }
        resolve(json);
      })
      .catch(e=>{
        (reject || console.error)(e);
      });
    });
  }

  handleEdit = (node)=>{
    if(node.isLeaf)
      this.showAttributeForm();
    else
      this.showCategoryForm();
    this.setState(prevStates=>{
      prevStates.form.initValue = node.rawdata;
      return prevStates;
    });
  }

  hideForm = ()=>{
    this.setState(prevStates=>{prevStates.form.span=0;return prevStates;});
  }

  handleCategorySubmit = (formData)=>{
    return new Promise((resolve, reject)=>{
      this.restCgy.create(formData).then(res=>res.json()).then(json=>{
        if('OK' == json.code){
          let id = 0;
          if(formData instanceof FormData)
            id = formData.get('id');
          else if(formData instanceof Object)
            id = formData.id || 0;

          if(0 == id){
            message.success('Category saved! Attribute will be next!');
            var ret = this.addCategory(json.data);
            this.setCurrent(ret);
            setTimeout(()=>{
              this.showAttributeForm(); 
            }, 3500);
          }else{
            for(var i=0; i<this.state.curNodeData.children.length; i++){
              if(id == this.state.curNodeData.children[i].rawdata.id){
                this.setCategory(this.state.curNodeData.children[i], json.data);
                break;
              }
            }
            message.success('Category saved!');
          }
        }
        resolve(json);
      });
    });
  }

  recursiveSearch(node){
    let stack = [ {children:this.state.treeData[0].children, parentKey:this.state.treeData[0].key} ];
    let found = {siblings:null, index:-1, parentKey:null};

    for(; stack.length>0 && null == found.siblings; ){
      let {children, parentKey} = stack.pop();
      for(let i=0; i<children.length; i++){
        if(children[i].key == node.props.dataRef.key){
          found.siblings = children;
          found.index = i;
          found.parentKey = parentKey;
          break;
        }

        if(!children[i].isLeaf){
          stack.push( {children: children[i].children, parentKey: children[i].key} );
        }

      }
    }

    return found;
  }

  handleCategoryDrop = (info)=>{
    const dragFromNode = info.dragNode;
    const dropToNode   = info.node;
    this.setState(prevStates=>{
      let dragStateRef = this.recursiveSearch(dragFromNode); 
      let dropStateRef = this.recursiveSearch(dropToNode)
      if(dragStateRef.siblings != null && dropStateRef.siblings != null){
        dragStateRef.siblings[dragStateRef.index].rawdata.parentId = dropStateRef.siblings[dropStateRef.index].key;
        this.handleCategorySubmit(dragStateRef.siblings[dragStateRef.index].rawdata).then(rsp=>{
          if('OK' == rsp.status){
            let dragObj = dragStateRef.siblings.splice(dragStateRef.index, 1);
            dropStateRef.siblings.push(dragObj);
            prevStates.draggable = false;
          }
        });
      }
      return prevStates;
    });
  }

  checkReorderDisabled(){
    for(var ch=this.state.curNodeData.children,i=0; i < ch.length && !ch[i].isLeaf; i++) ;
    return i==ch.length
      || 'loading...' == this.state.reorder.steps[this.state.reorder.cursor]
      || ( 'save' == this.state.reorder.steps[this.state.reorder.cursor] && 0 == this.state.reorder.undoStack.length);
  }
  handleAttributeOrderCancel = ()=>{
    this.setState(prevStates=>{
      prevStates.reorder.cursor = 0;
      return prevStates;
    });
  }
  handleReorder = (link)=>{
    if('save' == this.state.reorder.steps[this.state.reorder.cursor]){
      //[category-id, children] 
      //children=[[attribute-id, seq, children], ...]
      //children=[[option-id, seq], ...]
      var args = [this.state.curNodeData.rawdata.id, []], attr=[], opt=[];
      while( this.state.reorder.undoStack.length > 0 ){
        var {attribute, arrow, option, order} = this.state.reorder.undoStack.pop();
        if(undefined == option){
          if(-1 == attr.indexOf(attribute)) attr.push(attribute);
        }
        else{
          if(-1 == opt.indexOf(attribute)) opt.push(attribute);
        }
      }

      for(var j=0; j<attr.length; j++){
        for(var i=0, ch=this.state.curNodeData.children; i<ch.length; i++){
          if(ch[i].isLeaf) args[1].push([ch[i].rawdata.id, ch[i].rawdata.seq, []]);
        }
      }

      for(var j=0; j<opt.length; j++){
        for(var k=0; k<args[1].length && opt[j].rawdata.id != args[1][k][0]; k++)
          ;
        if(k == args[1].length)
          args[1].push([opt[j].rawdata.id, -99, []]);

        for(var i=0; i<opt[j].rawdata.options.length; i++){
          args[1][k][2].push([opt[j].rawdata.options[i].id, opt[j].rawdata.options[i].seq]);
        }
      }

      console.log(args);
      new RestFetch('/category-attribute-reorder').create({json: JSON.stringify(args)})
        .then(res=>this.handleReorder()); 
    }

    this.setState(prevStates=>{
      prevStates.reorder.cursor++;
      if(prevStates.reorder.cursor == prevStates.reorder.steps.length){
        prevStates.reorder.cursor = 0;
      }
      return prevStates;
    });
  }
  _attr_reorder(attribute, arrow){
    var ch = this.state.curNodeData.children, i=0, dst, temp;
    for(; i<ch.length; i++){
      if(ch[i] == attribute){
        dst = 'forward' == arrow ? i-1 : i+1;
        ch[i].rawdata.seq -= i-dst;
        ch[dst].rawdata.seq += i-dst;
        temp = ch[i];
        ch[i] = ch[dst];
        ch[dst] = temp;
        return ch[i];
      }
    }
    return null;
  }
  _opt_reorder(attribute, arrow, opt){
    var ch = attribute.rawdata.options||[], i=0, dst, temp;
    for(; i<ch.length; i++){
      if(ch[i] == opt){
        dst = 'forward' == arrow ? i-1 : i+1;
        ch[i].seq -= i-dst;
        ch[dst].seq += i-dst;
        temp = ch[i];
        ch[i] = ch[dst];
        ch[dst] = temp;
        return ch[i];
      }
    }
    return null;
  }
  handleAttributeOrderChange = (attribute, arrow, option)=>{
    var swap;
    if((swap = undefined==option ? this._attr_reorder(attribute, arrow) : this._opt_reorder(attribute, arrow, option))!=null){
      if(this.state.reorder.undoStack.length > 0){
        var prev = this.state.reorder.undoStack.pop();
        if((undefined == option && attribute == prev.swap && arrow == prev.arrow) 
          || (option != undefined && attribute == prev.attribute && option == prev.swap && arrow == prev.arrow))
        {
          this.setState(this.state);
          return;
        }
        this.state.reorder.undoStack.push(prev);
      }
      this.state.reorder.undoStack.push({attribute, arrow, option, swap});
      this.setState(this.state);
    }
  }
  handleAttributeOrderUndo = ()=>{
    var {attribute, arrow, option, order} = this.state.reorder.undoStack.pop();
    var arrow = 'forward' == arrow ? 'backward' : 'forward';
    if((order = undefined==option ? this._attr_reorder(attribute, arrow) : this._opt_reorder(attribute, arrow, option))!=null){
      this.setState(this.state);
    }
  }

  loadData(categoryId){
    this.restCgyParent.select(categoryId).then(rsp=>rsp.json())
      .then(list=>{
        this.addCategory(list);
      });

    if(categoryId != '0'){
      this.restAttrList.select(categoryId).then(rsp=>rsp.json())
        .then(list=>{
          this.addAttribute(list);
        });
    }
  }

  onLoadData = (treeNode) => {
    return new Promise((resolve) => {
  		if (treeNode.props.children.length > 0) {
  			resolve();
  			return;
  		}
      setTimeout(() => {
        this.loadData(treeNode.props.dataRef.rawdata.id||0);
        resolve();
      }, 1000);
    });
  }
  renderTreeNodes = (data) => {
    return data.map((item) => {
      return (
          <TreeNode title={item.title} key={item.key} dataRef={item} 
              isLeaf={item.isLeaf||false}
              disabled={(item.rawdata||{}).closed} >
            { this.renderTreeNodes(item.children || []) }
          </TreeNode>
      );
    });
  }

  trunDragDrop = (onOrOff)=>{
    this.setState(prevStates=>{
      prevStates.draggable = onOrOff;
      return prevStates;
    });
  }

  render() {
    const menu = (
      <Menu>
        <Menu.Item>
          <a rel="noopener noreferrer" href="#" onClick={this.showCategoryForm}>Category</a>
        </Menu.Item>
        <Menu.Item disabled={this.state.curNodeData.key == '0'}>
          <a rel="noopener noreferrer" href="#" onClick={this.showAttributeForm}>Attribute</a>
        </Menu.Item>
        <Menu.Item>
          <a rel="noopener noreferrer" href="#" onClick={()=>this.trunDragDrop(true)}>Drag-Drop-Node-Once</a>
        </Menu.Item>
      </Menu>
    );
    return (
      <Row gutter={10} style={{paddingBottom:'8px'}}>
        <Col span={5} >
          <Dropdown overlay={menu}>
            <a className="ant-dropdown-link" href="#"  style={{position:"absolute", right:"10px"}}><Icon type="plus" /></a>
          </Dropdown>
          <h4 className="title">Catalog</h4>
          <div style={{background:'#fff'}}><Tree loadData={this.onLoadData} 
                onSelect={this.onNodeSelect} 
                onExpand={this.onNodeExpand} 
                defaultExpandAll={true} 
                defaultSelectedKeys={[this.state.curNodeData.key]}
                selectedKeys={[this.state.curNodeData.key]}
                onDrop={this.handleCategoryDrop}
                draggable={this.state.draggable}>
            {this.renderTreeNodes(this.state.treeData)}
          </Tree></div>
        </Col>
        <Col span={this.state.form.span}> 
          <a href="#" onClick={this.hideForm} style={{position:"absolute", right:"10px"}}><Icon type="close" /></a>
          <CategoryForm display={this.state.form.display.category} 
                        parentData={this.state.curNodeData} 
                        initValue={this.state.form.initValue}
                        onSubmit={this.handleCategorySubmit} />
          <AttributeForm display={this.state.form.display.attribute} 
                          category={this.state.curNodeData} 
                          initValue={this.state.form.initValue}
                          onSubmit={this.handleAttributeSubmit} />
        </Col>
        <Col span={19-this.state.form.span} >
          <a className="ant-dropdown-link" href="#"  
            style={{position:"absolute", right:"10px"}}
            disabled={this.checkReorderDisabled()}
            onClick={e=>this.handleReorder(e.target)} >
            {this.state.reorder.steps[this.state.reorder.cursor]}
          </a>
          {this.state.reorder.undoStack.length > 0 && <a className="ant-dropdown-link" href="#"  
            style={{position:"absolute", right:"43px"}}
            onClick={e=>this.handleAttributeOrderUndo()} >
            undo<i style={{color:'#aaa'}}> | </i>
          </a>}
          {'save' == this.state.reorder.steps[this.state.reorder.cursor]  && 0 == this.state.reorder.undoStack.length && <a className="ant-dropdown-link" href="#"  
            style={{position:"absolute", right:"43px"}}
            onClick={e=>this.handleAttributeOrderCancel()} >
            cancel<i style={{color:'#aaa'}}> | </i>
          </a>}
          <h4 className="title">Details of category and attribute within <i>{this.state.curNodeData.title}</i></h4>
          <div style={{background:'#fff', padding:'0 3px'}}>
            <CategoryTable 
              data={this.state.curNodeData.children} 
              onEdit={this.handleEdit} 
              orderable={this.state.reorder.cursor > 0}
              onAttributeOrderChange={this.handleAttributeOrderChange} />
          </div>
        </Col>
      </Row>
    );
  }
}