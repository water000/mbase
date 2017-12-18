import React from 'react';
import { Table, Icon, Switch, Radio, Form, Button, Checkbox, Input , Alert, Tabs} from 'antd';
import RestFetch from "../RestFetch"
import UserRemoteSelect from "./UserRemoteSelect"
const FormItem = Form.Item;
const TabPane = Tabs.TabPane;

const WEB_ROOT = 'http://localhost:8080';


class MarkAction extends React.Component{

	constructor(props){
		super(props);
		this.state = {
			data : [],
			selectedData:[],
			selectedRowKeys: [],
			tableLoading:true,
			saveLoading:false,
			response:{
				code:'',
				msg:''
			}
		};
	}

	setResponse(code, msg){
		this.setState({response:{code, msg}});
	}

	resetResponse(){
		this.setState({response:{code:'', msg:''}});
	}

	scan(){
		new RestFetch({path:"/permission/action/scan"})
			.select()
			.then(res => res.json())
			.then(json=>{
				json.sort((a, b)=>{
					return a.urlPath < b.urlPath ? -1 :
						(a.urlPath > b.urlPath ? 1 : 0);
				});
				this.setState({data:json, tableLoading:false});
				this.selected();
			})
			.catch(e=>{this.setResponse('FETCH_EXCEPTION', 'Unknown Exception');console.error(e);});
	}

	save = ()=>{
		let ret = false, that = this;
		this.state.selectedData.map(function(item, i){
			item.name = (item.name||"").trim();
			if(0 == item.name.length){
				that.setResponse('INVALID_PARAM', 'name can not be null');
				ret = true;
				return;
			}
		});
		if(ret) return;
		this.setState({saveLoading:true});
		new RestFetch('/permission/action').create(JSON.stringify(this.state.selectedData), {'Content-Type':'application/json'}) 
			.then(res => res.text())
			.then(updated=>{this.setResponse('OK', 'save successed');this.setState({saveLoading:false});this.props.onMarked(this.state.selectedData);})
			.catch(e=>{this.setResponse('FETCH_EXCEPTION', `"${e.statusText}"(${e.status})`);this.setState({saveLoading:true});});
	}

	selected(){
		new RestFetch('/permission/action').select()
			.then(res => res.json())
			.then(json=>{
				let i,j;
				for(i=0; i<json.length; i++){
					for(j=0; j<this.state.data.length; j++){
						if(json[i].handleMethod === this.state.data[j].handleMethod){
							this.state.data[j] = json[i];
							this.state.selectedRowKeys.push(j);
							break;
						}
					}
				}
				this.setState({data: this.state.data, selectedRowKeys:this.state.selectedRowKeys, selectedData:json, tableLoading:false});
				this.props.onReady(json);
			})
			.catch(e=>console.log(e));
	}

	componentDidMount() {
		this.scan();
	}

	render(){
		const columns = [
			{
				title: '#',
				dataIndex: 'id',
				key: 'id',
				render:(_1, _2, index)=><span>{index+1}</span>
			},
			{
				title: 'url',
				dataIndex:'urlPath',
				key:'URL',
				render:(text, record, index)=><span style={{color:'#03c'}}>{text}</span>
			},
			{
				title : 'name',
				dataIndex : 'name',
				key:'name',
				render:(text, record, index)=><Input size="large" defaultValue={text} required
					value={text} onChange={e=>{record.name=e.target.value;this.setState({data:this.state.data});}} />
			},
			{
				title: 'method',
				dataIndex:'handleMethod',
				key:'method',
				render:text=><span title={text}>{text && text.length>50?text.substr(0, 20)+'...'+text.substr(-30):text}</span>
			},
			{
				title: 'is-menu-item',
				dataIndex:'menuItem',
				key:'isMenuItem',
				render:(text, record, index)=><Switch 
					defaultChecked={text} 
					checked={record.menuItem} 
					checkedChildren={<Icon type="check" />} 
					unCheckedChildren={<Icon type="cross" />} 
					onChange={checked=>{record.menuItem=checked;this.setState({data:this.state.data});}} />
			}
		];

		const rowSelection = {
		  onChange: (selectedRowKeys, selectedRows) => {
		    this.setState({selectedData:selectedRows, selectedRowKeys});
		  },
		  selectedRowKeys: this.state.selectedRowKeys
		};

		return <div style={{display:this.props.display}}>
			{this.state.response.code && <Alert onClose={()=>this.resetResponse()} closable={true} style={{marginBottom:'15px'}} type={'OK' == this.state.response.code ? 'success' : 'error'} message={this.state.response.msg} showIcon /> }
			<h4 style={{marginBottom:'15px'}} >Select action(s) for access control</h4>
			<Table
			rowSelection={rowSelection}
			pagination = {false}
			rowKey = {(record, index)=>index}
			size = 'small'
			loading={this.state.tableLoading}
			columns={columns}
			footer={()=><Button type="primary" loading={this.state.saveLoading} onClick={this.save}>Save</Button>}
			dataSource={this.state.data}
			onChange={this.scan} />
			</div>
	}
}

class Role extends React.Component{
	constructor(props){
		super(props);
		this.props = Object.assign({
			init : false, // indicate whether to init the role from remote resource
			markedActions:{}, // {user:[{Action1}, {Action2}, ...], model:[{Action1, ...}]}
			basicProps:null,
		}, props);
		this.state = {
			name: '',
			members:[],
			checkedActions:[],
		};
		this.extra = new RestFetch("/permission/role/extra");
	}
	handleSubmit=()=>{

	}
	handleDelete=()=>{

	}
	handleMemberChange=()=>{

	}
	handleFormChange=(kv)=>{
		this.setState(kv);
	}
	render(){
		const formItemLayout =  {
	      labelCol: { span: 4 },
	      wrapperCol: { span: 14 },
	    };
	    let i=0, prev_group = null, group, idx, arr=[], elem;
	    const acs = this.props.markedActions || [];
	    for(i=0; i<acs.length; i++){
      		elem = acs[i];
      		group = (idx = elem.urlPath.indexOf('/', 2)) > 0 ? // urlPath:"[/path/...]"
				elem.urlPath.substr(0, idx) : elem.urlPath;
			if(prev_group != group){
				prev_group = group;
				arr.push(<div style={{borderBottom:"1px solid #eee"}}><b>{prev_group}</b></div>);
			}
			arr.push(<Checkbox value={elem.id} style={{display:"inline-block", width:"80px"}}>{elem.name}</Checkbox>);
        }
		return (
			<div>
				<h4 style={{marginBottom:'15px'}} >Role Info</h4>
		        <Form layout="horizontal">
		          <FormItem label="Name: " {...formItemLayout} >
			            <Input placeholder="input name" value={this.state.name} name="name" 
			            	onChange={e=>this.handleFormChange({name:e.target.value})}  />
		          </FormItem>
		          <FormItem label="Members: " {...formItemLayout} >
		            <UserRemoteSelect onChange={(value)=>this.handleFormChange({members:value})} value={this.state.members} />
		          </FormItem>
		          <FormItem label="Actions: " {...formItemLayout} >
		          	{arr}
		          	<div style={{textAlign:"right",paddingRight:"5px"}}><a href="javascript:;" onClick={(e)=>this.props.onRemark()}>Expected not found? Go to Mark !</a></div>
		          </FormItem>
		          {this.props.basicProps && <FormItem label="Props: " {...formItemLayout}>
		            <Input placeholder="input placeholder" />
		          </FormItem>}
		          <FormItem label=" " {...formItemLayout}>
		            <Button type="primary" onClick={this.handleSubmit} style={{marginRight:"5%"}}>Submit</Button>
		            <Button type="danger" onClick={this.handleDelete} style={{border:"0px"}}>Delete</Button>
		          </FormItem>
		        </Form>
		    </div>
		)
	}
}

class RoleList extends React.Component{
	constructor(props){
		super(props);
		this.fetch = new RestFetch("/permission/role");
	}
	state = {
		list:null,
		activeTabKey:"-1"
	}
	create(role){
		return new Promise((resolve, reject)=>{
			reject = reject || console.error;
			this.fetch.create(role)
				.then(rsp=>rsp.json())
				.then(json=>{this.state.list.push(json);this.setState({list:this.state.list});resolve(json);})
				.catch(e=>reject(e));
		});
	}
	select(id){
		this.fetch.select().then(rsp=>rsp.json())
			.then(json=>{
				this.setState({list:json, activeTabKey:json!=null && json.length > 0 ? "0" : "-1"});
			});
	}
	delete(i){
		return new Promise((resolve, reject)=>{
			reject = reject || console.error;
			if(i >= this.state.list.length || i< 0)
				reject("role not found");
			else
				this.fetch.delete({id:this.state.list[i].id})
					.then(rsp=>{this.setState({list:this.state.list.splice(i, 1)});resolve(rsp);})
					.catch(e=>reject(e));
		});
	}
	update(role){
		let i=0;
		for(; i<this.state.list.length; i++){
			if(this.state.list[i].id == role.id)
				break;
		}
		return new Promise((resolve, reject)=>{
			reject = reject || console.error;
			if(i == this.state.list.length)
				reject("role not found");
			else
				this.fetch.update(role)
					.then(rsp=>rsp.json())
					.then(json=>{this.setState({list:this.state.list.splice(i, 1, json)});resolve(json);})
					.catch(e=>reject(e));
		});
	}
	componentDidMount(){
		this.select();
	}
	handleTabChange=(key)=>{
		this.setState({activeTabKey:key});
	}
	render(){
		return (
			<div>
				<Tabs activeKey={this.state.activeTabKey} 
					onChange={this.handleTabChange} 
					tabPosition="right"
					>
				    <TabPane tab={<span style={{color:"green"}}>+New</span>} key="-1" ><Role basicProps={null} markedActions={this.props.markedActions} onRemark={this.props.onRemark} /></TabPane>
				    {this.state.list && this.state.list.map((key, value)=>{
				    	<TabPane tab="{value.name}" key="{key}">
				    		<Role basicProps={value} markedActions={this.props.markedActions} onRemark={this.props.onRemark} />
				    	</TabPane>
				    })}
				</Tabs>
			</div>
		)
	}
}
 
export default class Permission extends React.Component{
	constructor(props){
		super(props);
	}
	state = {
		markedActions:null,
		markActionDisplay:"none"
	}
	handleMarked=(markedActions)=>{
		this.setState({markedActions, markActionDisplay: markedActions != null && markedActions.length>0 ? "none" : ""});
	}
	handleRemark=()=>{
		this.setState({markedActions:null, markActionDisplay:""});//hide RoleList and show MarkAction
	}
	render(){
		return (
			<div>
				<MarkAction display={this.state.markActionDisplay} onMarked={this.handleMarked} onReady={this.handleMarked} />
				{this.state.markedActions && <RoleList markedActions={this.state.markedActions} onRemark={this.handleRemark}  /> }
			</div>	
		);
	}
}

