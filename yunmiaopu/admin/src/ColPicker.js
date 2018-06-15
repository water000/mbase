
import React from 'react';
import {Checkbox, Button, Input, Upload, Dropdown} from 'antd';
//npm install react-color-picker
import ColorPicker from 'react-color-picker';
import 'react-color-picker/index.css';

const NAMED_COLOR = [
	'aqua', '浅绿色',
	'black', '黑色',
	'blue', '蓝色',
	'fuchsia', '紫红色',
	'gray', '灰色',
	'green', '绿色',
	'lime', '绿黄色',
	'maroon', '褐红色',
	'navy', '深蓝色',
	'olive', '橄榄色',
	'purple', '紫色',
	'red', '红色',
	'silver', '银色',
	'teal', '青色',
	'white', '白色',
	'yellow', '黄色',
];

export default class ColPicker extends React.Component{

	constructor(props){
		super(props);
    this.state = {
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
        console.log(prevStates.custom);
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

  render(){
    var box = [];
    for(var i=0; i<NAMED_COLOR.length; i+=2){
      box.push(<span className='color-box'>
        <Checkbox 
          name='color' 
          style={{width:'75px'}} 
          value={NAMED_COLOR[i]} 
          checked={this.state.color.indexOf(NAMED_COLOR[i])!=-1}>
          {NAMED_COLOR[i+1]}
        </Checkbox>
        <span className='color-bg' style={{background:NAMED_COLOR[i]}}></span></span>);
    }
    for(var i=0; i<this.state.color.length; i++){
      if(-1 == NAMED_COLOR.indexOf(this.state.color[i])){
        var arr = this.decode(this.state.color[i]);
         box.push(<span className='color-box'>
          <Checkbox 
            name='color' 
            style={{width:'75px'}} 
            value={this.state.color[i]} 
            checked='true'>
            {arr[0]}
          </Checkbox>
          <span className='color-bg' style={{background:arr[1]||''}}></span></span>); 
      }
    }
    return (
      <div>
        <div className='color-picker'>
          {box}
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
                <Dropdown 
                  overlay={<ColorPicker value={this.state.custom.arg} 
                  onDrag={this.handleCustomColorChanged} />} 
                  trigger={['click']}>
                  <Button size='small' icon='edit' style={{color:this.state.custom.arg}}>Color</Button>
                </Dropdown>
                <Upload><Button icon='upload' size='small'>Image</Button></Upload>
                <Button type='primary' size='small' onClick={this.handleSubmit} 
                  style={{position:'absolute', top:0,right:0}}>OK</Button>
              </span>
          }
        </div>
      </div>
    );
  }
}