import React from 'react';
import {Tree, Row, Col,  Menu, Dropdown, Icon, Form, Input, Button, Switch, message, Table, Badge, Avatar, Upload, List} from 'antd';
import moment from 'moment';
import RestFetch from "../RestFetch"
import Global from "../Global"
const TreeNode = Tree.TreeNode;
const FormItem = Form.Item;

class CategoryForm extends React.Component{

  constructor(props){
    super(props);

    this.state = {
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

    var formData = new FormData(e.target);
    if(this.state.fields.iconUrl.value[0].originFileObj)
      formData.append('icon', this.state.fields.iconUrl.value[0].originFileObj);
    formData.append('closed', this.state.fields.closed.value);
    this.props.onSubmit(formData).then(rsp=>{
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
    });
  }

  componentWillReceiveProps(nextProps){
    if(!nextProps.initValue || undefined == nextProps.initValue.cnName)
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
      <h4 className="title">Category mounted within <i>{this.props.parentData.title}</i></h4>
      <Form layout="vertical" style={{margin:"12px 10px"}} onSubmit={this.handleSubmit}>
        <Input type="hidden" name="parentId" value={this.props.parentData.key} />
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
        <Button type="primary" htmlType="submit" style={{width:"100%"}}>Submit</Button>
      </Form>
    </div>
  }
}

class CategoryTable extends React.Component{

  render(){
    return (
      <List
        itemLayout="horizontal"
        size="small"
        dataSource={this.props.data}
        renderItem={item => (
          <List.Item actions={[<a onClick={()=>this.props.onEdit(item)}>edit</a>]}>
            <List.Item.Meta
              avatar={<Avatar src={Global.imgUrl(item.rawdata.iconUrl)} >{item.rawdata.enName}</Avatar>}
              title={<div><Badge text={item.rawdata.cnName} status={item.rawdata.closed ? "default" : "success"}/>
                <span style={{color:'#aaa', fontSize:'80%', float:'right'}}>create:{new Date(item.rawdata.createTs*1000).toLocaleDateString()}</span></div>}
              description={<div>{item.rawdata.desc} {item.rawdata.wikiUrl && <div><a href={item.rawdata.wikiUrl} style={{color:'#bbb'}}><i>{item.rawdata.wikiUrl}</i></a></div>}</div>}
            />
          </List.Item>
        )}
      />
    );
  }

}

class AttributeForm extends React.Component{

  constructor(props){
    super(props);
  }

  render(){
    return <div style={{display:this.props.display}}>
      <h4 className="title">Attribute mounted within <span className="ant-form-text">({this.props.parentData.title})</span></h4>
      <Form layout="vertical" style={{margin:"8px 5px"}}>
      </Form>
    </div>
  }

}

export default class CategoryTree extends React.Component{
	state = {
    curNodeData : null,
    draggable: false,
    treeData: [
      { title: 'All', key: '0', children:[], rawdata:null },
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
    var ret;
    this.setState(prevStates=>{
      ret = {title:category.cnName, key:category.id, children:[], rawdata:category};
      prevStates.curNodeData.children.push(ret);
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
    var ret;
    this.setState(prevStates=>{
      ret = prevStates.curNodeData.children.push({
        title:`${attribute.enName}(${attribute.cnName}`, key:attribute.id, isLeaf:true, rawdata:attribute});
      return prevStates;
    });
    return ret;
  }

  showCategoryForm = ()=>{
    this.setState(prevStates=>{
      prevStates.form.span = 9;
      prevStates.form.display.category = '';
      prevStates.form.display.attribute = 'none';
      prevStates.form.initValue = {};
      return prevStates;
    });
  }

  showAttributeForm = ()=>{
    this.setState(prevStates=>{
      prevStates.form.span = 9;
      prevStates.form.display.category = 'none';
      prevStates.form.display.attribute = '';
      prevStates.form.initValue = {};
      return prevStates;
    });
  }
  handleAttributeSubmit = (data)=>{
    
  }

  handleCategoryEdit = (category)=>{
    this.showCategoryForm();
    this.setState(prevStates=>{
      prevStates.form.initValue = category.rawdata;
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
              if(id == this.state.curNodeData.children[i].key){
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
          if( (children[i].isLeaf && node.props.dataRef.isLeaf)
            || (!children[i].isLeaf && !node.props.dataRef.isLeaf) )
          {
            found.siblings = children;
            found.index = i;
            found.parentKey = parentKey;
            break;
          }
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

  loadData(categoryId){
    this.restCgyParent.select(categoryId).then(rsp=>rsp.json())
      .then(list=>{
        list.map(cgy=>{
          this.addCategory(cgy);
        });
      });

    if(categoryId != '0'){
      this.restAttrList.select(categoryId).then(rsp=>rsp.json())
        .then(list=>{
          list.map(attr=>{
            this.addAttribute(attr);
          });
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
        this.loadData(treeNode.props.dataRef.key);
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
        <Menu.Item>
          <a rel="noopener noreferrer" href="#" onClick={this.showAttributeForm}>Attribute</a>
        </Menu.Item>
        <Menu.Item>
          <a rel="noopener noreferrer" href="#" onClick={()=>this.trunDragDrop(true)}>Drag-Drop-Node-Once</a>
        </Menu.Item>
      </Menu>
    );
    return (
      <Row gutter={16}>
        <Col span={6} >
          <Dropdown overlay={menu}>
            <a className="ant-dropdown-link" href="#"  style={{position:"absolute", right:"10px"}}><Icon type="plus" /></a>
          </Dropdown>
          <h4 className="title">Catalog</h4>
          <Tree loadData={this.onLoadData} 
                onSelect={this.onNodeSelect} 
                onExpand={this.onNodeExpand} 
                defaultExpandAll={true} 
                defaultSelectedKeys={'0'}
                onDrop={this.handleCategoryDrop}
                draggable={this.state.draggable}>
            {this.renderTreeNodes(this.state.treeData)}
          </Tree>
        </Col>
        <Col span={this.state.form.span}> 
          <a href="#" onClick={this.hideForm} style={{position:"absolute", right:"10px"}}><Icon type="close" /></a>
          <CategoryForm display={this.state.form.display.category} 
                        parentData={this.state.curNodeData} 
                        initValue={this.state.form.initValue}
                        onSubmit={this.handleCategorySubmit} />
          <AttributeForm display={this.state.form.display.attribute} 
                          parentData={this.state.curNodeData} 
                          initValue={this.state.form.display.attribute != 'none' ? this.state.form.initValue:{}}
                          onSubmit={this.handleAttributeSubmit} />
        </Col>
        <Col span={18-this.state.form.span} >
          <h4 className="title">Details of category and attribute within <i>{this.state.curNodeData.title}</i></h4>
          <CategoryTable data={this.state.curNodeData.children} onEdit={this.handleCategoryEdit} />
        </Col>
      </Row>
    );
  }
}