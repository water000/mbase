import React from 'react';
import ReactDOM from 'react-dom';
import './index.css';
import App from './App'
import registerServiceWorker from './registerServiceWorker';

import About from './components/About'
import Index from './components/Index'
import Permisson from './components/Permission'
import Auth from './components/Auth'
import {RestFetch_setDomain, RestFetch_setAuthFilter, RestFetch} from './RestFetch'



const _menu = [
	{title:'Home', items:[{title:'index', link:'/index'}, {title:'about', link:'/about'}]},
	{title:'Permission', link:'/permission'}
]

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

class AuthHandle extends React.Component{
  constructor(props){
    super(props);
    this.state = {authVisiable:false};
  }
  onAuth=(beforeAuthRest)=>{
    this.setState({
      authVisiable: true,
    });
    this.beforeAuthRest = beforeAuthRest;
  }
  onAuthCancel=()=>{
    this.setState({
      authVisiable: false
    });
    if(this.beforeAuthRest){
      this.beforeAuthRest.onAuthOk();
      this.beforeAuthRest = null;
    }
  }
  render(){
    return(
        <div style={{height:'100%'}}>
          <div id='app' style={{height:'100%'}}></div>
          <Auth visible={this.state.authVisiable} onAuthOk={this.onAuthCancel} />
        </div>
    );
  }
}
const authHandle = ReactDOM.render(
	<AuthHandle />, 
	document.getElementById('root'));
RestFetch_setAuthFilter(authHandle);
RestFetch_setDomain("http://127.0.0.1:8080");
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
