
import { Select, Spin } from 'antd';
import RestFetch from "../RestFetch"
import debounce from 'lodash.debounce';
import React from 'react';
const Option = Select.Option;

export default class UserRemoteSelect extends React.Component {
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

