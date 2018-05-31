import React from 'react';
import {Tree, Row, Col,  Menu, Dropdown, Icon, Form, Input, Button, Switch} from 'antd';
import RestFetch from "../RestFetch"
const TreeNode = Tree.TreeNode;
const FormItem = Form.Item;

class CategoryForm extends React.Component{

  state = {
    fields : [
      {
        name:'enName',
        options:{},
        control:<Input />
        value:'',
      },
      {
        name:'cnName',
        options:{},
        control:<Input />
        value:'',
      },
      {
        name:'desc',
        options:{},
        control:<Input />
        value:'',
      },
      {
        name:'wikiUrl',
        options:{},
        control:<Input />
        value:'',
      },
      {
        name:'closed',
        options:{},
        control:<Switch />
        value:false,
      },
    }

  }


  constructor(props){
    super(props);
  }

  setValue(input){
    this.setState(prevStates=>{
      prevStates.value[input.name] = input.value;
      console.log(prevStates);
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
      <h4>Category within <span className="ant-form-text">({this.props.parentData.title})</span></h4>
      <Form layout="vertical" style={{margin:"12px 5px"}}>
        {
          this.state.fields.map(field=>{
            return <FormItem>
              {getFieldDecorator(field.name, field.options)(field.control)}
              </FormItem>
                });
        }
      </Form>
    </div>
  }
}

class AttributeForm extends React.Component{

  constructor(props){
    super(props);
  }

  render(){
    return <div style={{display:this.props.display}}>
      <h4>Attribute within <span className="ant-form-text">({this.props.parentData.title})</span></h4>
      <Form layout="vertical" style={{margin:"8px 5px"}}>
      </Form>
    </div>
  }

}

export default class CategoryTree extends React.Component{
	state = {
    curNodeData : null,
    treeData: [
      { title: 'All', key: '0' },
      //{ title: 'Tree Node', key: '2', isLeaf: true },
    ],
    form:{
      span:0,
      display:{
        category:"none",
        attribute:"none"
      }
    }
  }
  
  constructor(props){
  	super(props);
  	this.restCgy  = new RestFetch("/categories");
  	this.restAttr = new RestFetch("/category-attribute");
  	this.restOpt  = new RestFetch("/category-attribute-option");
  }

  setCurrentKey = (node)=>{
    this.setState({curNodeData: node.props.dataRef});
  }

  onNodeExpand = (expandedKeys, {expanded, node}) => {
    if(!node.props.dataRef.isLeaf)
      this.setCurrentKey(node);
  }

  onNodeSelect = (selectedKeys, {selected, selectedNodes, node, event}) => {
    if(!node.props.dataRef.isLeaf)
      this.setCurrentKey(node);
  }

  showCategoryForm = ()=>{
    this.setState({form:{span:9, display:{category:"", attribute:"none"}}});
  }

  showAttributeForm = ()=>{
    this.setState({form:{span:9, display:{category:"none", attribute:""}}});
  }

  hideForm = ()=>{
    this.setState(prevStates=>{prevStates.form.span=0;return prevStates;});
  }

  onLoadData = (treeNode) => {
    return new Promise((resolve) => {
  		if (treeNode.props.children) {
  			resolve();
  			return;
  		}

  		treeNode.props.dataRef.children = [];

    	this.restCgy.select(treeNode.props.dataRef.key).then(rsp=>rsp.json())
    		.then(list=>{
    			list.map(cgy=>{
    				treeNode.props.dataRef.children.push({title: cgy.cn_name+'('+cgy.en_name+')', key:cgy.id});
    			});
    		});

    	if(treeNode.props.dataRef.key != '0'){
  	  	this.restAttr.select(treeNode.props.dataRef.key).then(rsp=>rsp.json())
  	  		.then(list=>{
  	  			list.map(attr=>{
  	  				treeNode.props.dataRef.children.push({title: attr.cn_name+'('+attr.en_name+')', key:attr.id, isLeaf:true});
  	  			});
  	  		});
  		}

  		this.setState({
            treeData: [...this.state.treeData],
          });
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
      <Row gutter={12}>
        <Col span={6} style={{borderRight:"1px solid #ccc", height:"100%"}}>
          <Dropdown overlay={menu}>
            <a className="ant-dropdown-link" href="#"  style={{position:"absolute", right:"10px"}}><Icon type="plus" /></a>
          </Dropdown>
          <Tree loadData={this.onLoadData} onSelect={this.onNodeSelect} onExpand={this.onNodeExpand}>
            {this.renderTreeNodes(this.state.treeData)}
          </Tree>
        </Col>
        <Col span={this.state.form.span} style={{borderRight:"1px solid #ccc", height:"100%"}}>
          <a href="#" onClick={this.hideForm} style={{position:"absolute", right:"10px"}}><Icon type="close" /></a>
          <CategoryForm display={this.state.form.display.category} parentData={this.state.curNodeData || this.state.treeData[0]} />
          <AttributeForm display={this.state.form.display.attribute} parentData={this.state.curNodeData || this.state.treeData[0]} />
        </Col>
        <Col span={18-this.state.form.span} style={{height:"100%"}}>
          <h3>Detail</h3>
        </Col>
      </Row>
    );
  }
}