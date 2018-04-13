
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
					console.log("user", user);
					this.setState(user)
				});
		}
	}

	render(){
		const DropdownList = (
	      <Menu>
	        <Menu.Item>
	          <table>
	          	<tr><th>Name:</th><td></td></tr>
	          	<tr><th>Phone</th><td></td></tr>
	          	<tr><th>ID</th><td></td></tr>
	          	<tr><th>Email</th><td></td></tr>
	          </table>
	        </Menu.Item>
	      </Menu>
	    );
		return (
			<Dropdown overlay={DropdownList}>
		      <a style={{ verticalAlign:'middle'}}>
		        <Avatar src={this.state.url}  {...this.props.avatarProps} />{this.props.fieldOfProfile && this.state[this.props.fieldOfProfile]}<Icon type="down" />
		      </a>
		    </Dropdown>
		);
	}
}

function _fetch(){
	var rest = new RestFetch("/user/account"),
		idle = true,
		timer = 0,
		queue = [];
	
	function produce(attr, resolve, reject){
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

	return function(attr){
		return new Promise((resolve, reject)=>{
			produce(attr, resolve, reject);
		});
	}
}

export default class User{};

User.AvatarProfile = AvatarProfile;
User.fetch = new _fetch();