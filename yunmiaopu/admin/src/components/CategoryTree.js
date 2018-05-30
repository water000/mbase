import React from 'react';
import {Tree} from 'antd';
import RestFetch from "../RestFetch"
const TreeNode = Tree.TreeNode;

export default class CategoryTree extends React.Component{
	state = {
    treeData: [
      { title: 'All', key: '0' },
      { title: 'Expand to load', key: '1' },
      { title: 'Tree Node', key: '2', isLeaf: true },
    ],
  }
  constructor(props){
  	super(props);
  	this.restCgy  = new RestFetch("/categories");
  	this.restAttr = new RestFetch("/category-attribute");
  	this.restOpt  = new RestFetch("/category-attribute-option");
  }

  onLoadData = (treeNode) => {
    return new Promise((resolve) => {
		if (treeNode.props.children) {
			resolve();
			return;
		}

console.log(treeNode);

		treeNode.props.dataRef.children = [];

	  	this.restCgy.select(treeNode.props.dataRef.key).then(rsp=>rsp.json())
	  		.then(list=>{
	  			list.map(cgy=>{
	  				treeNode.props.dataRef.children.push({title: cgy.cn_name+'('+cgy.en_name+')', key:cgy.id});
	  			});
	  		});

	  	if(treeNode.props.key != '0'){
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
    return (
      <Tree loadData={this.onLoadData}>
        {this.renderTreeNodes(this.state.treeData)}
      </Tree>
    );
  }
}