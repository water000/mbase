
import { Select, Spin,notification } from 'antd';
import RestFetch from "../RestFetch"
import debounce from 'lodash.debounce';
import React from 'react';
const Option = Select.Option;

export default class UserRemoteSelect extends React.Component {
  constructor(props) {
    super(props);
    //this.props = Object.assign({value:[], onChange:(v)=>console.log(v)}, props);
    console.log(this.props);
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
  	if(11 == str.length){
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
  	if( (value = this.isPhonePart(value))) {
  		return {phone: value};
  	}else if(this.isEmail(value)){
  		return {email: value};
  	}else{
  		return null;
  	}
  }
  fetchUser = (value) => {
  	if(!(value = this.isComplete(value)))
  		return;
    this.lastFetchId += 1;
    const fetchId = this.lastFetchId;
    this.setState({ data: [], fetching: true });
    new RestFetch('/user/account').select(value)
      .then(response => response.json())
      .then((user) => {
        if (fetchId !== this.lastFetchId) { // for fetch callback order
          return;
        }
        let data = [];
        if("OK" == user.code){
          user.data.map(u=>{
            data.push({text:u.name?u.name:u.mobilePhone, value:u.id});
          });
        }else{
            notification.error({
              message:'fetching user error',
              description:user.code
            });
          }
        this.setState({ data, fetching: false });
      });
  }
  fetchUserId=(ids)=>{
    this.setState({ data: [], fetching: true });
    new RestFetch("/user/account").select({id: ids.join(',')})
      .then(rsp=>rsp.json())
      .then(json=>{
        let value = [], data = [];
        if("OK" == json.code){
          json.data.map(user=>{
            value.push(user.id);
            data.push({value:user.id, text:user.name+(user.name?'':user.phone)});
          });
        }else{
          notification.error({
            message:'fetching user id error',
            description:json.code
          });
        }
        
        this.setState({value, data, fetching:false});
      })
      .catch(e=>{
        console.error(e);
        this.setState({fetching:false});
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
  	if(this.props.value.length > 0){
      //this.fetchUserId(this.props.value);
    }
  }
  render() {
    const { fetching, data, value } = this.state;
    const defvalue = this.props.value ? this.props.value.map(v=>{return {key:v+"", label:"ID:"+v};}) : [];
    return (
      <Select
        mode="multiple"
        labelInValue
        value={value.length > 0 ? value : defvalue}
        placeholder="phone or email"
        notFoundContent={fetching ? <Spin size="small" /> : "not found"}
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

