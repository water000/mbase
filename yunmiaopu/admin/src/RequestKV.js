import {Radio,Select} from 'antd';
import React from 'react';
import RestFetch from "./RestFetch"
const RadioButton = Radio.Button;
const RadioGroup = Radio.Group;
const Option = Select.Option;

let cache = {};
export class RequestKV{}
RequestKV.fetch = (url, key)=>{
  return new Promise((resolve, reject)=>{
    if(cache[url])
      resolve(cache[url][key]);
    else
      new RestFetch(url).select().then(rsp=>rsp.json())
        .then(json=>{
          cache[url] = json;
          resolve(json[key]);
        })
        .catch(err=>{
          (reject || console.error)(err);
        }) ;
  });
}

export class RequestKVRadioGroup extends React.Component{

  constructor(props){
    super(props);
    this.state = {
      radiosKV:{}
    };
    this.first = null;
  }

  componentDidMount(){
    this.loadKV();
  }

  componentDidUpdate(prevProps, prevStates){
    if(!this.props.defaultValue && this.first){
      this.props.onChange(this.first);
    }
  }

  loadKV(){
    RequestKV.fetch(this.props.url, this.props.id).then(rsp=>{
      this.setState(prevStates=>{
        prevStates.radiosKV = rsp;
        return prevStates;
      });
    });
  }

  render(){
    let radios = [];
    for(var k in this.state.radiosKV){
      radios.push(<RadioButton value={k}>{this.state.radiosKV[k]}</RadioButton>);
      this.first = this.first || k;
    }
    return (
      <RadioGroup 
        name={this.props.name}
        value={this.props.value}
        defaultValue={this.props.defaultValue || this.first} 
        onChange={(e)=>this.props.onChange(e.target.value)}>
      {radios}
      </RadioGroup>
    );
  }
}

export class RequestKVSelect extends React.Component{

  constructor(props){
    super(props);
    this.state = {
      kv:{}
    };
    this.first = null;
  }

  componentDidMount(){
    this.loadKV();
  }

  componentDidUpdate(prevProps, prevStates){
    if(!prevProps.emptyOption && prevStates.kv != this.state.kv && this.first){
      this.props.onChange(this.first);
    }
  }

  loadKV(){
    RequestKV.fetch(this.props.url, this.props.id).then(rsp=>{
      this.setState(prevStates=>{
        prevStates.kv = rsp;
        return prevStates;
      });
    });
  }

  render(){
    let opts = [];

    if(this.props.emptyOption !== undefined)
      opts.push(<Option value={this.props.emptyOption.value}>{this.props.emptyOption.label}</Option>);

    for(var k in this.state.kv){
      opts.push(<Option value={k}>{this.state.kv[k]}</Option>);
      this.first = this.first || k;
    }

    return (
      <Select 
        {...this.props}
        defaultValue={ this.props.defaultValue ||(this.props.emptyOption !== undefined ? this.props.emptyOption.value : this.first)} 
      >
      {opts}
      </Select>
    );
  }
}
