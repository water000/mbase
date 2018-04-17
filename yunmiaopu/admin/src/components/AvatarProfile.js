
import React from 'react';
import { Avatar, Select, Dropdown, Button, Icon, Menu  } from 'antd';
import RestFetch from "../RestFetch"

class AvatarProfile extends React.Component{

	static defaultProps = {
		id: 0,
		fieldOfProfile: 'mobilePhone', // empty to indicate that no field to show
		avatarProps : {size:'small'},
		url: 'https://zos.alipayobjects.com/rmsportal/ODTLcjxAfvqbxHnVXCYX.png'
	}

	constructor(props){
		super(props);
	}

	state = { // user basic info
	}

	componentDidMount(){
		if(this.props.id > 0){
			User.fetch({id:this.props.id})
				.then((user)=>{
					if("OK" == user.code)
						this.setState(user.data[0] || {})
				});
		}
	}

	render(){
		const DropdownList = (
	      <Menu>
	        <Menu.Item>
	          <table className="link-user-avatar-profile-tb">
	          	<tr><th>ID</th><td>{this.state.id}</td></tr>
	          	<tr><th>Name</th><td>{this.state.name}</td></tr>
	          	<tr><th>Phone</th><td>{this.state.mobilePhone}</td></tr>
	          	<tr><th>Email</th><td>{this.state.email}</td></tr>
	          </table>
	        </Menu.Item>
	      </Menu>
	    );
		return (
			<Dropdown overlay={DropdownList}>
		      <a className="link-user-avatar-profile">
		        <Avatar src={this.state.url}  {...this.props.avatarProps} />{this.props.fieldOfProfile && this.state[this.props.fieldOfProfile]}<Icon type="down" />
		      </a>
		    </Dropdown>
		);
	}
}

function _fetch(){
	var rest = null,
		idle = true,
		timer = 0,
		queue = [];
	
	function produce(attr, resolve, reject){
		if(null == rest)
			rest = new RestFetch("/user/account");
		queue.push({attr, resolve, reject});
		if(0 == timer){
			timer = setInterval(()=>{
				if(0 == queue.length){
					clearInterval(timer);
					timer = 0;
					return;
				}
				if(idle){
					idle = false;
					consume();
				}
			}, 10);
		}
	}
	function consume(){
		const {attr, resolve, reject} = queue.shift();
		rest.select(attr)
			.then(rsp=>rsp.json())
			.then(json=>{
				resolve(json);
				idle = true;
			}).catch(rsp=>{
				idle = true;
				console.error(rsp);
			});
	}

	//attr = number | {[id|name|...]:value}
	return function(attr){
		attr = "object" == typeof attr ? attr : {id:attr};
		return new Promise((resolve, reject)=>{
			produce(attr, resolve, reject);
		});
	}
}

export default class User{};

User.AvatarProfile = AvatarProfile;
User.fetch = new _fetch();