import React from 'react';
import {Tree, Row, Col,  Menu, Dropdown, Icon, Form, Input, Button, Switch, message, Table, Badge} from 'antd';
import moment from 'moment';
import RestFetch from "../RestFetch"
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
      }
    };
  }

  cleanFileds(){
    this.setState(prevStates=>{
      for(var key in prevStates.fields){
          prevStates.fields[key].value = '';
      }
      prevStates.fields.closed.value = false;
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

  handleSubmit = (e)=>{
    e.preventDefault();

    if(!this.state.fields.cnName.value){
      this.setState(prevStates=>{
        prevStates.fields.cnName.validateStatus = 'error';
        prevStates.fields.cnName.help = 'CN-Name is required';
        return prevStates;
      });
    }
    if(!this.state.fields.enName.value){
      this.setState(prevStates=>{
        prevStates.fields.enName.validateStatus = 'error';
        prevStates.fields.enName.help = 'EN-Name is required';
        return prevStates;
      });
    }
    var data = {};
    for(var key in this.state.fields){
      data[key] = this.state.fields[key].value;
    }
    data.parentId = this.props.parentData.key;
    this.props.onSubmit(data).then(rsp=>{
      if('OK' == rsp.code){
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

  componentBeforeUpdate(){
    this.setState(prevStates=>{
      for(var k in this.props.initValue){
        prevStates.fields[k].value = this.props.initValue[k];
      }
      return prevStates;
    })
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
      <h4>Category mounted within ({this.props.parentData.title})</h4>
      <Form layout="vertical" style={{margin:"12px 10px"}} onSubmit={this.handleSubmit}>
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
          <Switch name="closed" checked={this.state.fields.closed.value} onChange={(b)=>this.setChecked('closed', b)}/> 
        </FormItem>
        <Button type="primary" htmlType="submit" style={{width:"100%"}}>Submit</Button>
      </Form>
    </div>
  }
}

class CategoryTable extends React.Component{

  render(){
    const columns = [
      {
        title: '#(Category)',
        dataIndex: 'id',
        key: 'id',
        render:(_1, record, index)=>index+1
      },
      {
        title: 'CN-Name',
        dataIndex:'cnName',
        key:'CN-Name',
        render:(_1, record, index)=>record.rawdata.cnName
      },
      {
        title : 'EN-name',
        dataIndex : 'enName',
        key:'enName',
        render:(_1, record, index)=>record.rawdata.enName
      },
      {
        title: 'Desc',
        dataIndex:'desc',
        key:'desc',
        render:(_1, record, index)=>record.rawdata.desc
      },
      {
        title: 'Wiki-URL',
        dataIndex:'wikiUrl',
        key:'wikiUrl',
        render:(_1, record, index)=>record.rawdata.wikiUrl
      },
      {
        title: 'Create-Datetime',
        dataIndex:'createTs',
        key:'createTs',
        render:(_1, record, index)=>moment(new Date(record.rawdata.createTs*1000), 'YYYY-MM-DD')
      },
      {
        title: 'Closed',
        dataIndex:'closed',
        key:'closed',
        render:(text, record)=><Badge status={0 == record.rawdata.closed ? "success" : "default"} />
      },
    ];

    <List />

    return <Table size='small' columns={columns} dataSource={this.props.data} pagination={false} />
  }
}

class AttributeForm extends React.Component{

  constructor(props){
    super(props);
  }

  render(){
    return <div style={{display:this.props.display}}>
      <h4>Attribute mounted within <span className="ant-form-text">({this.props.parentData.title})</span></h4>
      <Form layout="vertical" style={{margin:"8px 5px"}}>
      </Form>
    </div>
  }

}

export default class CategoryTree extends React.Component{
	state = {
    curNodeData : null,
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
    console.log("expanded: ", expanded, node.props);
    if(expanded)
      this.loadData(node.props.dataRef.key);
  }

  onNodeSelect = (selectedKeys, {selected, selectedNodes, node, event}) => {
    if(!node.props.dataRef.isLeaf)
      this.setCurrent(node.props.dataRef);
  }

  showCategoryForm = ()=>{
    this.setState({form:{span:9, display:{category:"", attribute:"none"}, initValue:{}}});
  }
  addCategory(category){
    var ret = this.state.curNodeData.children.push({
      title:category.cnName, key:category.id, children:[], rawdata:category});
    this.setState(prevStates=>prevStates);
    return ret;
  }
  addAttribute(attribute){
    var ret = this.state.curNodeData.children.push({
      title:`${attribute.enName}(${attribute.cnName}`, key:attribute.id, isLeaf:true, rawdata:attribute});
    this.setState(prevStates=>prevStates);
    return ret;
  }
  handleCategorySubmit = (data)=>{
    return new Promise((resolve, reject)=>{
      this.restCgy.create(data).then(res=>res.json()).then(json=>{
        if('OK' == json.code){
          var ret = this.addCategory(json.data);
          this.setCurrent(ret);
          message.success('Category saved. Attribute will be Next!');
          setTimeout(()=>{
            this.showAttributeForm(); 
          }, 3500);
        }
        resolve(json);
      });
    });
  }

  showAttributeForm = ()=>{
    this.setState({form:{span:9, display:{category:"none", attribute:""}, initValue:{}}});
  }
  handleAttributeSubmit = (data)=>{
    
  }

  hideForm = ()=>{
    this.setState(prevStates=>{prevStates.form.span=0;return prevStates;});
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
  		if (treeNode.props.children) {
  			resolve();
  			return;
  		}

      this.loadData(treeNode.props.dataRef.key);
      resolve();
    });
  }
  renderTreeNodes = (data) => {
    return data.map((item) => {
      if (item.children) {
        return (
          <TreeNode title={item.title} key={item.key} dataRef={item}>
            {this.renderTreeNodes(item.children)}
          </TreeNode>
        );
      }
      return <TreeNode {...item} dataRef={item} />;
    });
  }

  componentDidMount(){
    this.loadData(this.state.curNodeData.key);
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
      </Menu>
    );
    return (
      <Row gutter={16}>
        <Col span={6} style={{borderRight:"1px solid #ccc", height:"100%"}}>
          <Dropdown overlay={menu}>
            <a className="ant-dropdown-link" href="#"  style={{position:"absolute", right:"10px"}}><Icon type="plus" /></a>
          </Dropdown>
          <h4>Catalog</h4>
          <Tree loadData={this.onLoadData} onSelect={this.onNodeSelect} onExpand={this.onNodeExpand} defaultExpandAll={true}>
            {this.renderTreeNodes(this.state.treeData)}
          </Tree>
        </Col>
        <Col span={this.state.form.span} style={{borderRight:"1px solid #ccc", height:"100%"}}>
          <a href="#" onClick={this.hideForm} style={{position:"absolute", right:"10px"}}><Icon type="close" /></a>
          <CategoryForm display={this.state.form.display.category} 
                        parentData={this.state.curNodeData} 
                        initValue={this.state.form.display.category != 'none' ? this.state.form.initValue:{}}
                        onSubmit={this.handleCategorySubmit} />
          <AttributeForm display={this.state.form.display.attribute} 
                          parentData={this.state.curNodeData} 
                          initValue={this.state.form.display.attribute != 'none' ? this.state.form.initValue:{}}
                          onSubmit={this.handleAttributeSubmit} />
        </Col>
        <Col span={18-this.state.form.span} style={{height:"100%"}}>
          <h4>Details of category and attribute within <i>{this.state.curNodeData.title}</i></h4>
          <CategoryTable data={this.state.curNodeData.children} />
        </Col>
      </Row>
    );
  }
}