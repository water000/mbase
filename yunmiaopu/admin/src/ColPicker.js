
import React from 'react';
import {Checkbox, Button, Input, Upload, Dropdown, Icon} from 'antd';
//npm install react-color-picker
import ColorPicker from 'react-color-picker';
import 'react-color-picker/index.css';

import {RequestKV} from "./RequestKV"
import Global from "./Global"

export default class ColPicker extends React.Component{

	constructor(props){
		super(props);
    this.state = {
      named_color:{},
      color:[],
      custom:{name:'', arg:''}
    };
	}

  encode(name, arg){
    return name + '$' + arg;
  }
  decode(value){
    return value.split('$');
  }
  replace({name,arg}, value){
    if(name)
      return name + '$' + value.substr(value.indexOf('$')+1);
    else
      return value.substr(0, value.indexOf('$')) + '$' + arg;
  }

  _searchAndChange({name, arg}){
    this.setState(prevStates=>{
      for(var i=0; i<prevStates.color.length; i++){
        if(0 == prevStates.color[i].indexOf(prevStates.custom.name)){
          prevStates.color[i] = this.replace({name, arg}, prevStates.color[i]);
          break;
        }
      }
      if(i == prevStates.color.length){
        prevStates.color.push(this.encode(prevStates.custom.name, prevStates.custom.arg));
      }else{
        if(name)
          prevStates.custom.name = name;
        else if(arg)
          prevStates.custom.arg = arg;
      }
      return prevStates;
    });
  }

  handleCustomNameChanged=(v)=>{
    this.setState(prevStates=>{
      prevStates.custom.name = v;
      return prevStates;
    });
  }

  handleCustomColorChanged=(v)=>{
    this.setState(prevStates=>{
      prevStates.custom.arg = v;
      return prevStates;
    });
  }

  handleSubmit=()=>{
    this._searchAndChange(this.state.custom);
    this.setState(prevStates=>{
      prevStates.custom.name = '';
      prevStates.custom.arg = '';
      return prevStates;
    });
  }

  handleCheckboxChange(checkbox){
    //check, uncheck
    //custom_new, custom_edit
  }

  checked(value){
    for(var i=0; i<this.props.checkedColor.length; i++){
      if(value === this.props.checkedColor[i].value){
        return true;
      }
    }
    return false;
  }

  componentDidMount(){
    RequestKV.fetch('/category/enums', 'Attribute.NamedColor').then(json=>{
      this.setState(prevStates=>{
        prevStates.named_color = json;
        return prevStates;
      });
    });
  }

  render(){
    var named_color = [], custom_color = [];
    for(var k in this.state.named_color){
      named_color.push(<span className='color-box'>
        <Checkbox 
          name='color' 
          style={{width:'75px'}} 
          value={k} 
          defaultChecked={this.checked(k)}>
          {this.state.named_color[k]}
        </Checkbox>
        <span className='color-bg' style={{background:k}}></span></span>);
    }
    for(var i=0; i<this.props.checkedColor.length; i++){
      if(0 == this.props.checkedColor[i].value.indexOf('#')){
        custom_color.push(<span className='color-box'>
          <Checkbox 
            name='color' 
            style={{width:'75px'}} 
            value={this.props.checkedColor[i].value}
            defaultChecked='true'>
            <a>{this.props.checkedColor[i].label}</a>
          </Checkbox>
          <span className='color-bg' style={{background:this.props.checkedColor[i].value}}></span></span>); 
      }
      else if(this.props.checkedColor[i].vaue.indexOf('.jpg') != -1){
        custom_color.push(<span className='color-box'>
          <Checkbox 
            name='color' 
            style={{width:'75px'}} 
            value={this.props.checkedColor[i].value}
            defaultChecked='true'>
            {this.props.checkedColor[i].label}
          </Checkbox>
          <span className='color-bg'><img src={Global.imgUrl(this.props.checkedColor[i].value)} /></span></span>); 
      }
    }
    return (
      <div>
        <div className='color-picker'>
          <div>{named_color}</div>
          <div>{custom_color}</div>
        </div>
        <div style={{marginTop:'10px', position:'relative'}}>
          <Input 
            name='name' 
            style={{width:'100px', margin:'0 5px'}} 
            size='small' 
            placeholder='custom name' 
            value={this.state.custom.name}
            onChange={(e)=>this.handleCustomNameChanged(e.target.value)}
            required />
          {
            this.state.custom.name && 
              <span>

                <Dropdown overlay={<ColorPicker value={this.state.custom.arg} 
                    onDrag={this.handleCustomColorChanged} />} >
                  <Upload><Button icon='upload' size='small'>Image<Icon type="down" /></Button></Upload>
                </Dropdown>
                <Button type='primary' size='small' onClick={this.handleSubmit} 
                  style={{position:'absolute', top:0,right:0}}>OK</Button>
              </span>
          }
        </div>
      </div>
    );
  }
}