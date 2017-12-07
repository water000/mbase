import React from 'react';
import { Table, Icon, Switch, Radio, Form, Button, Input , Alert} from 'antd';
import RestFetch from "../RestFetch"

const WEB_ROOT = 'http://localhost:8080';

export default class Permission extends React.Component{

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
		console.log(msg);
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
				this.setState({data:json, tableLoading:false});
				this.selected();
			})
			.catch(e=>{this.setResponse('FETCH_EXCEPTION', 'Unknown Exception');console.error(e);});
		/*fetch(WEB_ROOT + '/permission/action/scan',{mode:"cors"})
			.then(res => res.json())
			.then(json=>{
				this.setState({data:json, tableLoading:false});
				this.selected();
			})
			.catch(e=>this.setResponse('FETCH_EXCEPTION', e));*/
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
			.then(updated=>{this.setResponse('OK', 'save successed');this.setState({saveLoading:false});})
			.catch(e=>{this.setResponse('FETCH_EXCEPTION', e);this.setState({saveLoading:true});});
	}

	selected(){
		fetch(WEB_ROOT + '/permission/action',{mode:"cors", method:'get'})
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

		return <div>
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