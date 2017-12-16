import React from 'react';
import { Table, Icon, Switch, Radio, Form, Button, Input , Alert} from 'antd';
import RestFetch from "../RestFetch"

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
					return a.url < b.url ? -1 :
						(a.url > b.url ? 1 : 0);
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
		fetch(WEB_ROOT + '/permission/action', 
			{mode:"cors", method:'post', headers:{'Content-Type':'application/json'}, body:JSON.stringify(this.state.selectedData)})
			.then(res => res.text())
			.then(updated=>{this.setResponse('OK', 'save successed');this.setState({saveLoading:false});this.props.onMarked(this.state.selectedData);})
			.catch(e=>{this.setResponse('FETCH_EXCEPTION', e);this.setState({saveLoading:true});});
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
	    var i=0, prev_group = null, group, idx, arr=[];
	    const macs = this.props.markedActions;
		return (
			<div>
		        <Form layout="horizontal">
		          <FormItem label="Name: " {...formItemLayout} >
			            <Input placeholder="input name" value={this.state.name} name="name" 
			            	onChange={e=>this.handleFormChange({name:e.target.value})}  />
		          </FormItem>
		          <FormItem label="Members: " {...formItemLayout} >
		            <UserRemoteSelect onChange={(value)=>this.handleFormChange({members:value})} value={this.state.members} />
		          </FormItem>
		          <FormItem label="Actions: " {...formItemLayout} >
		          {
					macs.map((i, elem)=>{
						group = (idx = elem.url.indexOf('/', 1)) > 0 ? 
							elem.url.substr(0, idx) : elem.url;
						if(prev_group != group){
							prev_group = group;
							<div style={{borderBottom:"1px solid #eee"}}><b>{prev_group}</b></div>
						}
						<Checkbox value={elem.id} style={{display:"inline-block", width:"80px"}}>{elem.name}</Checkbox>
					});
		          }
		          </FormItem>
		          <FormItem label="Props: " {...formItemLayout}>
		            <Input placeholder="input placeholder" />
		          </FormItem>
		          <FormItem {...buttonItemLayout}>
		            <Button type="primary" onClick={this.handleSubmit}>Submit</Button>
		            <Button type="danger" onClick={this.handleDelete}>Delete</Button>
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
				.then(json=>{this.setState({list:this.state.list.push(json)});resolve(json);})
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
				this.fetch.delete({id:role.id})
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
					>
				    <TabPane tab="+" key="-1" ><Role basicProps={null} onRemark={this.props.onRemark} /></TabPane>
				    {this.state.list.map((key, value)=>{
				    	<TabPane tab="{value.name}" key="{key}"><Role basicProps={value} onRemark={this.props.onRemark} /></TabPane>
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


import { Select, Spin } from 'antd';
import debounce from 'lodash.debounce';
const Option = Select.Option;

class UserRemoteSelect extends React.Component {
  constructor(props) {
    super(props);
    this.props = Object.assign({value:[], onChange:(v)=>console.log(v)}, props);
    this.lastFetchId = 0;
    this.fetchUser = debounce(this.fetchUser, 800);
  }
  state = {
    data: [],
    value: [],
    fetching: false,
  }
  isPhonePart = (str) => {
  	if(11 == str.length || 14 == str.length)
  		;
  	else
  		return false;
  	let i = 0, c;
  	for(; i<str.length; i++){
  		c = str.charAt(i);
  		if( c >= '0' && c <= '9' )
  			;
  		else
  			return false;
  	}
  	if(11 == str.length)
  		if('1' == str.charAt(0)){
  			str = "086" + str;
  		}else{
  			return false;
  		}

  	}
  	return str;
  }
  isEmail = (str)=>{
  	return /^[a-zA-Z0-9_-]+@[a-zA-Z0-9_-]+(\.[a-zA-Z0-9_-]+)+$/.test(str);
  }
  isComplete = (value) => {
  	if( (value = isPhonePart(value))) {
  		return {phone: value};
  	}else if(isEmail(value)){
  		return {email: value};
  	}else{
  		return null;
  	}
  }
  fetchUser = (value) => {
  	if(!(value = isComplete(value)))
  		return;
    console.log('fetching user', value);
    this.lastFetchId += 1;
    const fetchId = this.lastFetchId;
    this.setState({ data: [], fetching: true });
    new RestFetch('/user/account').select(value)
      .then(response => response.json())
      .then((body) => {
        if (fetchId !== this.lastFetchId) { // for fetch callback order
          return;
        }
        const data = body.results.map(user => ({
          text: user.name + (user.name ? '':user.phone), 
          value: user.id
        }));
        this.setState({ data, fetching: false });
      });
  }
  handleChange = (value) => {
    this.setState({
      value,
      data: [],
      fetching: false,
    });
    this.props.onChange(value);
  }
  componentDidMount(){
  	if(this.props.value.length > 0)
	    this.setState({ data: [], fetching: true });
  		new RestFetch("/user/account").select({id: this.props.value.join(',')})
  			.then(rsp=>rsp.json())
  			.then(json=>{
  				let value = [], data = [];
  				json.map(user=>{
  					value.push(user.id);
  					data.push({value:user.id, text:user.name+(user.name?'':user.phone)});
  				});
  				this.setState({value, data, fetching:false});
  			})
  			.catch(e=>{
  				console.error(e);
  				this.setState({fetching:false});
  			});

  }
  render() {
    const { fetching, data, value } = this.state;
    return (
      <Select
        mode="multiple"
        labelInValue
        value={value}
        placeholder="phone or email"
        notFoundContent={fetching ? <Spin size="small" /> : null}
        filterOption={false}
        onSearch={this.fetchUser}
        onChange={this.handleChange}
        style={{ width: '100%' }}
      >
        {data.map(d => <Option key={d.value}>{d.text}</Option>)}
      </Select>
    );
  }
}

ReactDOM.render(<UserRemoteSelect />, mountNode);