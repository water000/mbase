
import React from 'react';
import {Checkbox, Button, Input, Upload, Dropdown, Modal} from 'antd';
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
      custom:{label:'', extra:'', dataRef:null}
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
      prevStates.custom.label = v;
      return prevStates;
    });
  }

  handleCustomColorChanged=(v)=>{
    this.setState(prevStates=>{
      prevStates.custom.extra = v;
      return prevStates;
    });
  }

  cleanCustom(){
    this.setState(prevStates=>{
      prevStates.custom.label = '';
      prevStates.custom.extra = '';
      prevStates.custom.dataRef = null;
      return prevStates;
    });
  }

  handleSubmit=()=>{
    this.props.onSubmit(this.state.custom);
    this.cleanCustom(); 
  }

  //@props, null if new color
  handleCheckboxChange=(checkbox, props)=>{
    //check, uncheck
    //custom_new, custom_edit
    if(!checkbox.checked){
      Modal.confirm({
        title:'Do you want to DELETE the item?',
        content:'data can not be revert after submitting',
        okType: 'danger',
        onOk:()=>{
          this.props.onChange(checkbox.checked, props);
          if(props.dataRef == this.state.custom.dataRef){
            this.cleanCustom(); 
          }
        }
      });
    }else{
      this.props.onChange(checkbox.checked, props);
    }

  }

  handleCustomEdit=(props)=>{
    this.setState(prevStates=>{
      prevStates.custom.label = props.label;
      prevStates.custom.extra = props.extra;
      prevStates.custom.dataRef = props;
      return prevStates;
    });
  }

  handleCustomCancel=()=>{
    this.cleanCustom();
  }

  handleBeforeUpload=(file,list)=>{
    this.setState(prevStates=>{
      prevStates.custom.extra = file;
      return prevStates;
    });
    return false;
  }

  handleRemoveUpload=(file)=>{
    this.setState(prevStates=>{
      prevStates.custom.extra = '';
      return prevStates;
    });
  }

  handleUploadChange=(file)=>{
    console.log('file:', file);
    this.setState(prevStates=>{
      prevStates.custom.extra = file;
      return prevStates;
    });
  }

  checked(value){
    for(var i=0; i<this.props.checkedColor.length; i++){
      if(value === this.props.checkedColor[i].value){
        return this.props.checkedColor[i];
      }
    }
    return null;
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
      var checked = this.checked(k);
      named_color.push(<span className='color-box'>
        <Checkbox 
          name='color' 
          style={{width:'75px'}} 
          value={k} 
          onChange={(e)=>this.handleCheckboxChange(e.target, {label:this.state.named_color[k], extra:k, dataRef:checked})}
          defaultChecked={checked != null}>
          {this.state.named_color[k]}
        </Checkbox>
        <span className='color-bg' style={{background:k}}></span></span>);
    }
    for(var i=0; i<this.props.checkedColor.length; i++){
      var extra = null, box = null, reader =  new FileReader();
      if('object' == typeof this.props.checkedColor[i].extra)
        extra = <img className='color-bg' src={window[window.webkitURL ? 'webkitURL' : 'URL']['createObjectURL'](this.props.checkedColor[i].extra)} />; 
      else if(0 == this.props.checkedColor[i].extra.indexOf('#'))
        extra = <span className='color-bg' title={this.props.checkedColor[i].extra} style={{background:this.props.checkedColor[i].extra}}></span>; 
      else if(this.props.checkedColor[i].extra.indexOf('.jpg') > 0)
        extra = <img className='color-bg' src={Global.imgUrl(this.props.checkedColor[i].value)} />; 
      else
        continue;
      box = <span className='color-box'>
          <Checkbox 
            name='color' 
            style={{width:'75px'}} 
            onChange={((i)=>(e)=>this.handleCheckboxChange(e.target, {label:null, extra:null,  dataRef:this.props.checkedColor[i]}))(i)}
            checked={true}
          >
            {((i)=><a href='#' title='cancel' onClick={(e)=>this.handleCustomEdit(this.props.checkedColor[i])}>
              {this.props.checkedColor[i].label}
            </a>)(i)}
          </Checkbox>
          {extra}
        </span>;
      custom_color.push(box);
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
            style={{width:'100px', marginRight:'5px'}} 
            size='small' 
            placeholder='custom name' 
            value={this.state.custom.label}
            onChange={(e)=>this.handleCustomNameChanged(e.target.value)}
            required />
          {
            this.state.custom.label && 
              <span>
                <Dropdown 
                  overlay={<ColorPicker value={this.state.custom.arg} 
                    onDrag={this.handleCustomColorChanged} />} 
                  trigger={['click']}>
                  <Button size='small' icon='edit' style={{color:this.state.custom.extra}}>Color</Button>
                </Dropdown>
                <Upload 
                  fileList={typeof this.state.custom.extra !== 'string' ? [this.state.custom.extra] : []}
                  beforeUpload={this.handleBeforeUpload}
                  onRemove={this.handleRemoveUpload}
                >
                  <Button icon='upload' size='small'>Image</Button>
                </Upload>
                <Button type='primary' size='small' onClick={this.handleSubmit} 
                  disabled={!this.state.custom.label || !this.state.custom.extra} 
                  style={{position:'absolute', top:0,right:'15px'}}>OK</Button>
                {this.state.custom.dataRef && 
                  <a 
                    onClick={this.handleCustomCancel}
                   style={{position:'absolute', top:0,bottom:0,right:'0',color:'red'}}>&times;</a>}
              </span>
          }
        </div>
      </div>
    );
  }
}