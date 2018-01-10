import React from 'react';
import ReactDOM from 'react-dom';
import './index.css';
import App from './App'
import registerServiceWorker from './registerServiceWorker';

import { notification } from 'antd';

import About from './components/About'
import Index from './components/Index'
import Permisson from './components/Permission'
import Auth from './components/Auth'
import {RestFetch_setDomain, RestFetch_setCatcher, RestFetch} from './RestFetch'

RestFetch_setDomain("http://127.0.0.1:8080");

const _router_list = [
  {
    path : '/index',
    component : Index
  },
  {
    path : '/about',
    component : About
  },
  {
    path : '/permission',
    component : Permisson
  }
];

const _menu = [
  {title:'Home', items:[{title:'index', link:'/index'}, {title:'about', link:'/about'}]},
  {title:'Permission', link:'/permission'}
]

class AuthHandle extends React.Component{
  constructor(props){
    super(props);
    this.state = {authVisiable:false};
  }
  show=(onAuthSuccess)=>{
    this.setState({
      authVisiable: true,
    });
    this.onAuthSuccess = onAuthSuccess;
  }
  handleAuthOk=()=>{
    this.setState({
      authVisiable: false
    });
    
    if(this.onAuthSuccess){
      this.onAuthSuccess();
      this.onAuthSuccess = null;
    }
  }
  render(){
    return(
        <div style={{height:'100%'}}>
          <div id='app' style={{height:'100%'}}></div>
          <Auth visible={this.state.authVisiable} onAuthOk={this.handleAuthOk} />
        </div>
    );
  }
}
const authHandle = ReactDOM.render(
	<AuthHandle />, 
	document.getElementById('root'));

class GlobalFetchCatcher{
  handle=(rsp, restFetchInst, ctx)=>{
    const { body, headers, method, url } = ctx;
    let desc = '';
    if(rsp instanceof Response ){
      if(401 == rsp.status){
        authHandle.show(()=>restFetchInst.retry());
        return;
      }
      if(403 == rsp.status)
        desc = 'access denied';
      else if(rsp.status < 500)
        desc = 'clinet error('+rsp.status+')';
      else
        desc = 'server error('+rsp.status+')';
    }
    notification.error({
      placement:"bottomRight",
      message:'fetching error',
      description:url + '<br/>' + desc,
    });
    console.error(ctx);
  }
}

RestFetch_setCatcher(new GlobalFetchCatcher());

//pop up an auth-box while 401 status code happened to fetching network request.
//close the auth-box and retry the fetching which need to auth if auth successed.
// RestFetch --[401]--> AuthHandle --[show]--> Auth 
//          <--[retry]-            <--[success and close]
//an example to fetching 
// -- start --
//fetching request-->RestFetch[401]-->AuthHandle.onAuth[record the rest-instance]-->Auth.show
//input auth info until success --> Auth.onAuthOk --> AuthHandle.onAuthCancel --> retry rest-instance to fetch and Auth.close
// -- end --

ReactDOM.render(
  <App menu={_menu} router={_router_list} />, 
  document.getElementById('app'));

registerServiceWorker();
